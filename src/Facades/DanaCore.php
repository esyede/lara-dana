<?php

namespace Esyede\Dana\Facades;

use Illuminate\Support\Facades\Facade;

class DanaCore extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'DanaCore';
    }
}
