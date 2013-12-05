<?php
namespace BEST\SMS;

use Bread\Configuration\Manager as CM;

class Controller
{

    public static function factory()
    {
        $vendor = CM::get(get_called_class(), 'vendor');
        return new $vendor();
    }
}
