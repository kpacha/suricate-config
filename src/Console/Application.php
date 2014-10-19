<?php

namespace Kpacha\Suricate\Config\Console;

use Kpacha\Suricate\Console\Application as BaseApplication;
use Kpacha\Suricate\Config\Command\UpdateServices;

class Application extends BaseApplication
{

    protected function getDefaultCommands()
    {
        return array_merge(
                        parent::getDefaultCommands(),
                        array(new UpdateServices)
        );
    }
}
