<?php

namespace Amp\Beanstalk\Test;

use Amp\Beanstalk\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase {
    protected $parserToTest;

    protected $parsedElements;

    public function setUp() {
        $this->parserToTest = new Parser(function ($result) {
            $this->parsedElements = $result;
        });
    }

    public function testParsesPartialResponseCorrectly() {
        $this->parserToTest->send("OK 5\r\nhello\r");
        $this->assertNull($this->parsedElements);
        $this->parserToTest->send("\n");
        $this->assertSame(["OK", "hello"], $this->parsedElements);
    }

    public function testParsesFound() {
        $this->parserToTest->send("FOUND 5 5\r\nhello\r");
        $this->assertSame(["FOUND", 5, 'hello'], $this->parsedElements);
    }

    public function testParsesReserved() {
        $this->parserToTest->send("RESERVED 2 30\r\n");
        $this->assertNull($this->parsedElements);
        $this->parserToTest->reset();
        $this->parserToTest->send("RESERVED 5 5\r\nhello\r");
        $this->assertSame(["RESERVED", 5, 'hello'], $this->parsedElements);
    }

    public function testResetBuffer() {
        $this->parserToTest->send("OK 5\r\nmorning\r");
        $this->assertSame(["OK", "morni"], $this->parsedElements);
        $this->parserToTest->send("fddfd\r\nbyebye\r");
        $this->assertSame(["\rfddfd"], $this->parsedElements);

        $this->parserToTest->reset();

        $this->parserToTest->send("OK 5\r\nbyebye\r");
        $this->assertSame(["OK", "byeby"], $this->parsedElements);
    }


    /**
     * @dataProvider dataProviderTestExceptions
     */
    public function testParserExceptions($buffer, $exceptionExpected) {
        $this->parserToTest->send($buffer);
        $this->assertInstanceOf($exceptionExpected, $this->parsedElements);
    }

    public function dataProviderTestExceptions() {
        return [
            [
                "OUT_OF_MEMORY\r\nhello\r",
                \Amp\Beanstalk\OutOfMemoryException::class
            ],
            [
                "INTERNAL_ERROR\r\nhello\r",
                \Amp\Beanstalk\InternalErrorException::class
            ],
            [
                "BAD_FORMAT\r\nhello\r",
                \Amp\Beanstalk\BadFormatException::class
            ],
            [
                "UNKNOWN_COMMAND\r\nhello\r",
                \Amp\Beanstalk\UnknownCommandException::class
            ]
        ];
    }
}
