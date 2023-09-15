<?php

namespace Esyede\Dana\Validation;

class Validation
{
    /**
     * Validate dana terminal type
     *
     * @param string $type
     *
     * @return bool
     */
    public static function terminalType(string $type): bool
    {
        return in_array($type, ['WEB', 'APP', 'WAP', 'SYSTEM']);
    }
}
