<?php

namespace Kpacha\Suricate\Config;

use Symfony\Component\Filesystem\Filesystem;
use Kpacha\Suricate\SuricateException;

class ServiceManagerTest extends \PHPUnit_Framework_TestCase
{

    private static $serverConfig = array(
        ServiceManager::SURICATE_SERVER => 'someUrl',
        ServiceManager::WATCHED_SERVICES => array('service-a', 'service-b')
    );
    private static $filesystem;
    private $configFile;
    private $globalConfigFile;
    private $globalConfigMetaFile;

    public static function setUpBeforeClass()
    {
        self::$filesystem = new Filesystem;
    }

    public function setUp()
    {
        $this->configFile = __DIR__ . '/fixtures/' . Configuration::SURICATE_SOLVED_SERVICES . '.yml';
        $this->globalConfigFile = __DIR__ . '/fixtures/' . Configuration::CACHE_FILE;
        $this->globalConfigMetaFile = $this->globalConfigFile . '.meta';
        $this->clearFilesystem();
    }

    public function tearDown()
    {
        $this->clearFilesystem();
    }

    private function clearFilesystem()
    {
        self::$filesystem->remove($this->configFile);
        self::$filesystem->remove($this->globalConfigFile);
        self::$filesystem->remove($this->globalConfigMetaFile);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The server config key is not set
     */
    public function testItThrowsExceptionIfServerIsNotDefined()
    {
        new ServiceManager($this->mockConfig(Configuration::SURICATE_SERVICE, array()));
    }

    public function testTheWatchedServicesListIsEmptyIfTheConfigIsEmpty()
    {
        $config = self::$serverConfig;
        unset($config[ServiceManager::WATCHED_SERVICES]);
        $serviceManager = new ServiceManager($this->mockConfig(Configuration::SURICATE_SERVICE, $config));
        $this->assertCount(0, $serviceManager->getWatchedServices());
    }

    public function testTheWatchedServicesListIsNotEmptyIfTheConfigIsNotEmpty()
    {
        $serviceManager = new ServiceManager($this->mockConfig(Configuration::SURICATE_SERVICE, self::$serverConfig));
        $this->assertCount(2, $serviceManager->getWatchedServices());
    }

    public function testEveryServiceInTheWatchedServicesListIsRequestedToTheSuricate()
    {
        $serviceManager = $this->getInitSubject();

        $this->assertCount(2, $serviceManager->getUpdatedServices());
    }

    public function testUpdatedDataIsDumped()
    {
        $this->assertFileNotExists($this->configFile);

        $serviceManager = $this->getInitSubject();
        $this->assertTrue($serviceManager->dumpSolved());

        $this->assertFileExists($this->configFile);
    }

    public function testDumperReturnsFalseIfItWasAProblem()
    {
        $this->assertFileNotExists($this->configFile);

        $suricate = $this->getMock('\\Kpacha\\Suricate\\Suricate', array('getAll'), array(), 'Suricate', false);
        $suricate->expects($this->once())->method('getAll')->will($this->throwException(new SuricateException('ooops')));

        $serviceManager = $this->mockSubject(
                $this->mockConfig(Configuration::SURICATE_SERVICE, self::$serverConfig), $suricate
        );

        $this->assertFalse($serviceManager->dumpSolved());
        $this->assertFileNotExists($this->configFile);
    }

    public function testGlobalConfigIsCreatedIfItDidNotExist()
    {
        $this->checkRefreshConfig();
    }

    public function testConfigIsUpdatedIfItDidNotIncludeSolvedServers()
    {
        $helper = new ConfigFileHelper($this->globalConfigFile, self::$filesystem);
        $helper->initConfigFiles($helper->getDefaultConfig());
        $oldFileTime = filemtime($this->globalConfigFile);

        sleep(1);

        $this->checkRefreshConfig();

        $this->assertFileExists($this->configFile);
        $this->assertLessThan(filemtime($this->globalConfigFile), $oldFileTime);
    }

    private function checkRefreshConfig()
    {
        $serviceManager = $this->mockSubject(
                new Configuration(dirname($this->globalConfigFile), true), $this->getMockedSuricate()
        );
        $serviceManager->refreshConfigWithSolvedServices();

        $this->assertFileExists($this->globalConfigFile);
        $this->assertFileExists($this->globalConfigMetaFile);
    }

    private function getInitSubject()
    {
        return $this->mockSubject(
                        $this->mockConfig(Configuration::SURICATE_SERVICE, self::$serverConfig),
                        $this->getMockedSuricate()
        );
    }

    private function getMockedSuricate()
    {
        $suricate = $this->getMock('\\Kpacha\\Suricate\\Suricate', array('getAll'), array(), 'Suricate', false);
        $suricate->expects($this->any())->method('getAll')->willReturn(true);
        return $suricate;
    }

    private function mockConfig($key, $returnValue)
    {
        $config = $this->getMock('\\Kpacha\\Suricate\\Config\\Configuration', array('get', 'getConfigDir'), array(),
                'Config', false);
        $config->expects($this->once())->method('get')->with($key)->willReturn($returnValue);
        $config->expects($this->any())->method('getConfigDir')->willReturn(dirname($this->configFile));
        return $config;
    }

    private function mockSubject($mockedConfig, $mockedSuricate)
    {
        $subject = $this->getMock(
                '\\Kpacha\\Suricate\\Config\\ServiceManager', array('suricateClient'), array($mockedConfig)
        );
        $subject->expects($this->once())->method('suricateClient')->willReturn($mockedSuricate);
        return $subject;
    }

}
