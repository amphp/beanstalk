<?php

use Amp\Beanstalk\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase {
    public function testParsesPartialResponseCorrectly() {
        $parser = new Parser(function ($result) use (&$parsed) {
            $parsed = $result;
        });

        $parser->send("OK 5\r\nhello\r");
        $this->assertNull($parsed);
        $parser->send("\n");
        $this->assertSame(["OK", "hello"], $parsed);
    }
}
