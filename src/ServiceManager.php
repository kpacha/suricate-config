<?php

namespace Kpacha\Suricate\Config;

use Kpacha\Config\AbstractServiceManager;
use Kpacha\Suricate\SuricateBuilder;

class ServiceManager extends AbstractServiceManager
{

    protected function buildClient($serverUrl)
    {
        return SuricateBuilder::build($serverUrl);
    }

}
