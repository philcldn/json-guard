<?php

namespace Machete\Validation\Test;

use Machete\Validation\Pointer;

class PointerTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $document = $this->getDocument();
        $pointer = new Pointer($document);

        $this->assertCorrectJson($document, $pointer->get(''));
        $this->assertCorrectJson($document->foo, $pointer->get('/foo'));
        $this->assertCorrectJson('bar', $pointer->get('/foo/0'));
        $this->assertCorrectJson('baz', $pointer->get('/foo/1'));
        $this->assertCorrectJson(0, $pointer->get('/'));
        $this->assertCorrectJson(1, $pointer->get('/a~1b'));
        $this->assertCorrectJson(2, $pointer->get('/c%d'));
        $this->assertCorrectJson(3, $pointer->get('/e^f'));
        $this->assertCorrectJson(4, $pointer->get('/g|h'));
        $this->assertCorrectJson(5, $pointer->get('/i\\j'));
        $this->assertCorrectJson(6, $pointer->get("/k\"l"));
        $this->assertCorrectJson(7, $pointer->get('/ '));
        $this->assertCorrectJson(8, $pointer->get('/m~0n'));
        // url encoded
        $this->assertCorrectJson(2, $pointer->get('/c%25d'));
        $this->assertCorrectJson(3, $pointer->get('/e^f'));
        $this->assertCorrectJson(4, $pointer->get('/g%7Ch'));
        $this->assertCorrectJson(5, $pointer->get('/i%5Cj'));
        $this->assertCorrectJson(6, $pointer->get("/k%22l"));
        $this->assertCorrectJson(7, $pointer->get('/%20'));
        $this->assertCorrectJson(8, $pointer->get('/m~0n'));
    }

    public function testSet()
    {
        $document = $this->getDocument();
        $pointer = new Pointer($document);

        $pointer->set('/foo', [1,2,3,4]);
        $this->assertSame($document->foo, [1,2,3,4]);
    }

    public function testSetInArray()
    {
        $document = $this->getDocument();
        $pointer = new Pointer($document);

        $pointer->set('/foo/0', 'oranges');
        $this->assertSame('oranges', $document->foo[0]);
    }

    public function testSetInPathInsideArray()
    {
        // /properties/type/anyOf/1/items
        $document = $this->getDocument();
        $pointer = new Pointer($document);
        $pointer->set('/nested/0/type', 'boolean');
        $this->assertInternalType('array', $document->nested);
        $this->assertCount(2, $document->nested);
        $this->assertSame('boolean', $document->nested[0]->type);
    }

    public function testInvalidPointerType()
    {
        $this->setExpectedException(Pointer\InvalidPointerException::class);
        $document = $this->getDocument();
        $pointer = new Pointer($document);
        $pointer->get(['bad' => 'type']);
    }

    public function testInvalidPointerFirstCharacter()
    {
        $this->setExpectedException(Pointer\InvalidPointerException::class);
        $document = $this->getDocument();
        $pointer = new Pointer($document);
        $pointer->get('#hello/world');
    }

    protected function assertCorrectJson($expected, $actual, $message = '')
    {
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual), $message);
    }

    protected function getDocument()
    {
        return json_decode(file_get_contents(__DIR__ . '/fixtures/pointer.json'));
    }
}
