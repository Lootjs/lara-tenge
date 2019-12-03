<?php
namespace Loot\Tenge;

use Illuminate\Support\Facades\Facade;

class TengeFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'tenge';
    }
}
