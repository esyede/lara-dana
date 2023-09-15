<?php

namespace Esyede\Dana\Facades;

use Illuminate\Support\Facades\Facade;

class DanaPayment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'DanaPayment';
    }
}
