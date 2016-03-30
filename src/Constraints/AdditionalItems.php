<?php

namespace Yuloh\JsonGuard\Constraints;

use Yuloh\JsonGuard\ErrorCode;
use Yuloh\JsonGuard\SubSchemaValidatorFactory;
use Yuloh\JsonGuard\ValidationError;

class AdditionalItems implements ParentSchemaAwareContainerInstanceConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate(
        $data,
        $schema,
        $parameter,
        SubSchemaValidatorFactory $validatorFactory,
        $pointer = null
    ) {
        if (!is_array($data) || $parameter === true) {
            return null;
        }

        if (!is_array($items = self::getItems($schema))) {
            return null;
        }

        if ($parameter === false) {
            return self::validateAdditionalItemsWhenNotAllowed($data, $items, $pointer);
        } elseif (is_object($parameter)) {
            $additionalItems = array_slice($data, count($items));

            return self::validateAdditionalItemsAgainstSchema(
                $additionalItems,
                $parameter,
                $validatorFactory,
                $pointer
            );
        }

        return null;
    }

    /**
     * @param object $schema
     *
     * @return mixed
     */
    private static function getItems($schema)
    {
        return property_exists($schema, 'items') ? $schema->items : null;
    }

    /**
     * @param array                                      $items
     * @param object                                     $schema
     * @param \Yuloh\JsonGuard\SubSchemaValidatorFactory $validatorFactory
     * @param string                                     $pointer
     *
     * @return array
     */
    private static function validateAdditionalItemsAgainstSchema(
        $items,
        $schema,
        SubSchemaValidatorFactory $validatorFactory,
        $pointer
    ) {
        $errors = [];
        foreach ($items as $key => $item) {
            $currentPointer = $pointer . '/' . $key;
            $validator      = $validatorFactory->makeSubSchemaValidator($item, $schema, $currentPointer);
            $errors         = array_merge($errors, $validator->errors());
        }

        return $errors;
    }

    /**
     * @param array $data
     * @param array $items
     * @param $pointer
     *
     * @return \Yuloh\JsonGuard\ValidationError
     */
    private static function validateAdditionalItemsWhenNotAllowed($data, $items, $pointer)
    {
        if (count($data) > count($items)) {
            return new ValidationError(
                'Additional items are not allowed.',
                ErrorCode::NOT_ALLOWED_ITEM,
                $data,
                $pointer
            );
        }
    }
}