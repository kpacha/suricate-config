<?php

namespace Kpacha\Suricate\Config;

use Symfony\Component\Filesystem\Filesystem;

class ServiceManagerTest extends \PHPUnit_Framework_TestCase
{

    private static $serverConfig = array(
        ServiceManager::SURICATE_SERVER => 'someUrl',
        ServiceManager::WATCHED_SERVICES => array('service-a', 'service-b')
    );
    private $filesystem;
    private $configFile;

    public function setUp()
    {
        $this->configFile = __DIR__ . '/fixtures/' . Configuration::SURICATE_SOLVED_SERVICES . '.yml';
        $this->filesystem = new Filesystem();
        $this->filesystem->remove($this->configFile);
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->configFile);
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
        $serviceManager->dumpSolved();

        $this->assertFileExists($this->configFile);
    }

    private function getInitSubject()
    {
        $suricate = $this->getMock('\\Kpacha\\Suricate\\Suricate', array('getAll'), array(), 'Suricate', false);
        $suricate->expects($this->exactly(count(self::$serverConfig[ServiceManager::WATCHED_SERVICES])))
                ->method('getAll')
                ->willReturn(true);

        return $this->mockSubject($this->mockConfig(Configuration::SURICATE_SERVICE, self::$serverConfig), $suricate);
    }

    private function mockConfig($key, $returnValue)
    {
        $config = $this->getMock('\\Kpacha\\Suricate\\Config\\Configuration', array('get', 'getConfigDir'), array(), 'Config', false);
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
