<?php
namespace Bread\SMS;

use Bread\Configuration\Manager as CM;

class Controller
{

    public static function factory($domain)
    {
        $vendor = CM::get(__CLASS__, 'vendor', $domain);
        return new $vendor($domain);
    }
}
