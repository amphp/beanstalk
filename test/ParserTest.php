<?php

namespace Amp\Beanstalk\Test;

use Amp\Beanstalk\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    protected Parser $parserToTest;

    protected $parsedElements;

    public function setUp(): void
    {
        $this->parserToTest = new Parser(function ($result) {
            $this->parsedElements = $result;
        });
    }

    public function testParsesPartialResponseCorrectly(): void
    {
        $this->parserToTest->send("OK 5\r\nhello\r");
        $this->assertNull($this->parsedElements);
        $this->parserToTest->send("\n");
        $this->assertSame(["OK", "hello"], $this->parsedElements);
    }

    public function testParsesFound(): void
    {
        $this->parserToTest->send("FOUND 5 5\r\nhello\r\n");
        $this->assertSame(["FOUND", 5, 'hello'], $this->parsedElements);
    }

    public function testParsesReserved(): void
    {
        $this->parserToTest->send("RESERVED 2 30\r\n");
        $this->assertNull($this->parsedElements);
        $this->parserToTest->reset();
        $this->parserToTest->send("RESERVED 5 5\r\nhello\r\n");
        $this->assertSame(["RESERVED", 5, 'hello'], $this->parsedElements);
    }

    public function testResetBuffer(): void
    {
        $this->parserToTest->send("OK 7\r\nmorn");
        $this->assertNull($this->parsedElements);
        $this->parserToTest->send("ing\r\n");
        $this->assertSame(["OK", "morning"], $this->parsedElements);

        $this->parserToTest->send("OK 7\r\ntest");

        $this->parserToTest->reset();

        $this->parserToTest->send("OK 7\r\ntesting\r\n");
        $this->assertSame(["OK", "testing"], $this->parsedElements);
    }


    /**
     * @dataProvider dataProviderTestExceptions
     */
    public function testParserExceptions($buffer, $exceptionExpected): void
    {
        $this->parserToTest->send($buffer);
        $this->assertInstanceOf($exceptionExpected, $this->parsedElements);
    }

    public function dataProviderTestExceptions(): array
    {
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
