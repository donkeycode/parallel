<?php

namespace Parallel\Exception;

class MissingCommandsException extends \InvalidArgumentException
{
    const MESSAGE = "You have to define commands else using `cat commands.txt | php parallel.phar` or `php parallel.php 'commands' 'splitted by space'`";

    public function __construct($message="", $code=0, \Throwable $previous=null)
    {
        parent::__construct(self::MESSAGE, $code, $previous);
    }
}