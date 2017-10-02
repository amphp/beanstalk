<?php

use Amp\Beanstalk\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @param mixed &$variable
     * @return Parser
     */
    protected function getParserToTest(&$variable)
    {
        return new Parser(function ($result) use (&$variable) {
            $variable = $result;
        });
    }

    public function testParsesPartialResponseCorrectly()
    {
        $parser = $this->getParserToTest($parsed);

        $parser->send("OK 5\r\nhello\r");
        $this->assertNull($parsed);
        $parser->send("\n");
        $this->assertSame(["OK", "hello"], $parsed);
    }

    public function testParsesFound()
    {
        $parser = $this->getParserToTest($parsed);
        $parser->send("FOUND 5 5\r\nhello\r");
        $this->assertSame(["FOUND", 5, 'hello'], $parsed);
    }

    public function testParsesReserved()
    {
        $parser = $this->getParserToTest($parsed);

        $parser->send("FOUND 2 30\r\n");
        $this->assertNull($parsed);
        $parser->reset();
        $parser->send("RESERVED 5 5\r\nhello\r");
        $this->assertSame(["RESERVED", 5, 'hello'], $parsed);
    }

    public function testResetBuffer()
    {
        $parser = $this->getParserToTest($parsed);

        $parser->send("OK 5\r\nmorning\r");
        $this->assertSame(["OK", "morni"], $parsed);
        $parser->send("OK 5\r\nbyebye\r");
        $this->assertSame(["\rOK", "5"], $parsed);

        $parser->reset();

        $parser->send("OK 5\r\nbyebye\r");
        $this->assertSame(["OK", "byeby"], $parsed);
    }


    /**
     * @dataProvider dataProviderTestExceptions
     */
    public function testParserExceptions($buffer, $exceptionExpected)
    {
        $parser = $this->getParserToTest($parsed);
        $parser->send($buffer);
        $this->assertInstanceOf($exceptionExpected, $parsed);
    }

    public function dataProviderTestExceptions()
    {
        return [
            [
                "OUT_OF_MEMORY 5\r\nhello\r",
                \Amp\Beanstalk\OutOfMemoryException::class
            ],
            [
                "INTERNAL_ERROR 5\r\nhello\r",
                \Amp\Beanstalk\InternalErrorException::class
            ],
            [
                "BAD_FORMAT 5\r\nhello\r",
                \Amp\Beanstalk\BadFormatException::class
            ],
            [
                "UNKNOWN_COMMAND 5\r\nhello\r",
                \Amp\Beanstalk\UnknownCommandException::class
            ]
        ];
    }
}
