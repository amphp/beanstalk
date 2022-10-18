<?php

namespace Amp\Beanstalk;

class Parser
{
    const CRLF = "\r\n";

    const ERROR_OUT_OF_MEMORY = "OUT_OF_MEMORY";
    const ERROR_INTERNAL_ERROR = "INTERNAL_ERROR";
    const ERROR_BAD_FORMAT = "BAD_FORMAT";
    const ERROR_UNKNOWN_COMMAND = "UNKNOWN_COMMAND";

    private $responseCallback;
    private $buffer = "";

    public function __construct(callable $responseCallback)
    {
        $this->responseCallback = $responseCallback;
    }

    public function send(string $bytes): void
    {
        $this->buffer .= $bytes;

        do {
            $pos = strpos($this->buffer, self::CRLF);

            if ($pos === false) {
                return;
            }

            $line = substr($this->buffer, 0, $pos);
            $args = explode(" ", $line);

            $callback = $this->responseCallback;

            switch ($args[0]) {
                case self::ERROR_OUT_OF_MEMORY:
                    $this->buffer = substr($this->buffer, strlen($line) + 2);
                    $callback(new OutOfMemoryException);
                    break;

                case self::ERROR_INTERNAL_ERROR:
                    $this->buffer = substr($this->buffer, strlen($line) + 2);
                    $callback(new InternalErrorException);
                    break;

                case self::ERROR_BAD_FORMAT:
                    $this->buffer = substr($this->buffer, strlen($line) + 2);
                    $callback(new BadFormatException);
                    break;

                case self::ERROR_UNKNOWN_COMMAND:
                    $this->buffer = substr($this->buffer, strlen($line) + 2);
                    $callback(new UnknownCommandException);
                    break;

                case "OK":
                    $size = (int) $args[1];

                    if (strlen($line) + $size + 4 > strlen($this->buffer)) {
                        return;
                    }

                    $data = substr($this->buffer, strlen($line) + 2, $size);
                    $this->buffer = substr($this->buffer, strlen($line) + $size + 4);

                    $callback(["OK", $data]);
                    break;

                case "FOUND":
                case "RESERVED":
                    $size = (int) $args[2];

                    if (strlen($line) + $size + 4 > strlen($this->buffer)) {
                        return;
                    }

                    $data = substr($this->buffer, strlen($line) + 2, $size);
                    $this->buffer = substr($this->buffer, strlen($line) + $size + 4);

                    $callback([$args[0], (int) $args[1], $data]);
                    break;

                default:
                    $this->buffer = substr($this->buffer, strlen($line) + 2);
                    $callback($args);
                    break;
            }
        } while (isset($this->buffer[0]));
    }

    public function reset(): void
    {
        $this->buffer = "";
    }
}
