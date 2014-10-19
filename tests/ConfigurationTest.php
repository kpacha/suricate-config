<?php

namespace Kpacha\Suricate\Config;

use Symfony\Component\Filesystem\Filesystem;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    private $filesystem;
    private $configFile;

    public function setUp()
    {
        $this->configFile = __DIR__ . '/fixtures/' . Configuration::CACHE_FILE;
        $this->filesystem = new Filesystem();
    }

    public function tearDown()
    {
        $this->cleanConfigFiles();
    }

    public function testCacheConfigFileIsCreatedWhenItDoesNotExist()
    {
        $this->cleanConfigFiles();

        $configuration = new Configuration(dirname($this->configFile), true);

        $this->assertArrayHasKey(Configuration::DEFAULT_SERVICE_KEY,
                $configuration->get(Configuration::SURICATE_SERVICE));
    }

    public function testCacheConfigFileIsNotCreatedWhenItIsFresh()
    {
        $this->initConfigFiles($this->getDefaultConfig());

        $configuration = new Configuration(dirname($this->configFile), true);

        $this->assertArrayHasKey(Configuration::DEFAULT_SERVICE_KEY,
                $configuration->get(Configuration::SURICATE_SERVICE));
    }

    public function testDoNotOverrideSuricateSolvedServers()
    {
        $config = array_merge(
                $this->getDefaultConfig(),
                array(
            Configuration::SURICATE_SOLVED_SERVICES => array('solved' => array(true))
                )
        );
        $this->initConfigFiles($config);

        $configuration = new Configuration(dirname($this->configFile), true);

        $this->assertArrayHasKey('solved', $configuration->get(Configuration::SURICATE_SOLVED_SERVICES));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The key LOREM does not exist
     */
    public function testItTrhowsAnExceptionIfUnknownKey()
    {
        $this->initConfigFiles($this->getDefaultConfig());

        $configuration = new Configuration(dirname($this->configFile), true);

        $configuration->get('LOREM');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage suricate_services configuration not found inf config dir
     */
    public function testItTrhowsAnExceptionIfServicesAreNotDefined()
    {
        $this->initConfigFiles(array());

        new Configuration(dirname($this->configFile), true);
    }

    private function getDefaultConfig()
    {
        return array(Configuration::SURICATE_SERVICE => array(Configuration::DEFAULT_SERVICE_KEY => array('some')));
    }

    private function initConfigFiles($config)
    {

        $fakeConfig = var_export($config, true);
        $content = <<< PHP
<?php
\$config = $fakeConfig;
PHP;

        $this->filesystem->dumpFile($this->configFile, $content);
        $this->filesystem->dumpFile($this->configFile . '.meta', 'a:0:{}');
    }

    private function cleanConfigFiles()
    {
        $this->filesystem->remove($this->configFile);
        $this->filesystem->remove($this->configFile . '.meta');
    }

}
