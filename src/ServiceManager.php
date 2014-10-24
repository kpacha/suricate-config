<?php

namespace Kpacha\Suricate\Config;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Kpacha\Suricate\SuricateBuilder;
use Kpacha\Suricate\Config\Configuration;

class ServiceManager
{

    const SURICATE_SERVER = 'server';
    const WATCHED_SERVICES = 'service-names';

    private $config;
    private $watchedServices = array();
    private $suricateServer;
    private $suricateClient;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $suricateService = $config->get(Configuration::SURICATE_SERVICE);
        if (!isset($suricateService[self::SURICATE_SERVER])) {
            throw new \Exception('The ' . self::SURICATE_SERVER . ' config key is not set');
        }
        $this->suricateServer = $suricateService[self::SURICATE_SERVER];
        if (isset($suricateService[self::WATCHED_SERVICES])) {
            $this->watchedServices = $suricateService[self::WATCHED_SERVICES];
        }
    }

    public function getWatchedServices()
    {
        return $this->watchedServices;
    }

    public function getUpdatedServices()
    {
        $services = array();
        $client = $this->suricateClient();
        foreach ($this->watchedServices as $service) {
            $services[$service] = $client->getAll($service);
        }
        return $services;
    }

    public function dumpSolved()
    {
        try {
            $this->dump($this->getUpdatedServices());
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function refreshConfigWithSolvedServices()
    {
        if ($this->dumpSolved()) {
            $this->refreshConfig();
        }
    }

    protected function dump($data)
    {
        $configCache = new ConfigCache($this->getSolvedServicesFileName(), true);
        $configCache->write(Yaml::dump($data));
    }

    protected function refreshConfig()
    {
        if ($this->hasToCleanCache()) {
            $this->cleanCache();
        }
        new Configuration($this->getConfigDir(), true);
    }

    protected function hasToCleanCache()
    {
        return $this->isCacheConfigFilePresent() &&
                (!$this->isMetaCacheConfigFilePresent() || !$this->isSolvedServicesFileTracked());
    }

    private function isCacheConfigFilePresent()
    {
        return is_file($this->getCacheConfigFileName());
    }

    private function isMetaCacheConfigFilePresent()
    {
        return is_file($this->getMetaCacheConfigFileName());
    }

    protected function cleanCache()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->getCacheConfigFileName());
    }

    protected function suricateClient()
    {
        if (!$this->suricateClient) {
            $this->suricateClient = SuricateBuilder::build($this->suricateServer);
        }
        return $this->suricateClient;
    }

    protected function isSolvedServicesFileTracked()
    {
        $solvedServicesFileName = $this->getSolvedServicesFileName();
        $meta = $this->getStoredMetadata();
        foreach ($meta as $resource) {
            if ($resource === $solvedServicesFileName) {
                return true;
            }
        }

        return false;
    }

    protected function getStoredMetadata()
    {
        return unserialize(file_get_contents($this->getMetaCacheConfigFileName()));
    }

    protected function getConfigDir()
    {
        return $this->config->getConfigDir();
    }

    protected function getCacheConfigFileName()
    {
        return $this->getConfigDir() . '/' . Configuration::CACHE_FILE;
    }

    protected function getMetaCacheConfigFileName()
    {
        return $this->getCacheConfigFileName() . '.meta';
    }

    protected function getSolvedServicesFileName()
    {
        return $this->getConfigDir() . '/' . Configuration::SURICATE_SOLVED_SERVICES . '.yml';
    }

}
