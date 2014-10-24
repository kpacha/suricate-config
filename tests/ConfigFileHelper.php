<?php

namespace Kpacha\Suricate\Config;

class ConfigFileHelper
{

    const EMPTY_META = 'a:0:{}';

    private $filesystem;
    private $configFile;

    public function __construct($configFile, $filesystem)
    {
        $this->configFile = $configFile;
        $this->filesystem = $filesystem;
    }

    public function getDefaultConfig()
    {
        return array(
            Configuration::SURICATE_SERVICE => array(
                Configuration::DEFAULT_SERVICE_KEY => array('some'),
                ServiceManager::SURICATE_SERVER => 'someUrl',
                ServiceManager::WATCHED_SERVICES => array('one', 'two'),
            ),
        );
    }

    public function initConfigFiles($config, $meta = self::EMPTY_META)
    {

        $fakeConfig = var_export($config, true);
        $content = <<< PHP
<?php
\$config = $fakeConfig;
PHP;

        $this->filesystem->dumpFile($this->configFile, $content);
        $this->filesystem->dumpFile($this->configFile . '.meta', $meta);
    }

    public function cleanConfigFiles()
    {
        $this->filesystem->remove($this->configFile);
        $this->filesystem->remove($this->configFile . '.meta');
    }

}
