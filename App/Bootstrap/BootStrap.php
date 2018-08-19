<?php
namespace App\Bootstrap;

/**
 * Class BootStrap
 * @package App\Bootstrap
 */
class BootStrap
{
    protected $_executables = null;

    public function __construct(BootstrapInterface ...$executables)
    {
        $this->_executables = $executables;
    }

    public function run(...$args)
    {
        foreach ($this->_executables as $executable) {
            call_user_func_array([$executable, 'run'], $args);
        }
    }
}