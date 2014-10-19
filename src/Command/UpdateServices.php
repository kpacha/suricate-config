<?php
namespace Kpacha\Suricate\Config\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Kpacha\Suricate\Config\Configuration;
use Kpacha\Suricate\Config\ServiceManager;

class UpdateServices extends Command
{
    protected function configure()
    {
        $this->setName('suricate:update-services')
                ->setDescription('Update the service config file')
                ->addArgument('dir', InputArgument::REQUIRED, 'config dir');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!($configDir = $input->getArgument('dir'))) {
            throw new \InvalidArgumentException("Specify a config dir");
        }
        
        $start = microtime(true);
        
        $output->writeln("Loading the config files");
        $serviceManager = new ServiceManager(new Configuration($configDir, true));
        
        $output->writeln("Regenerating the solved services config file");
        $serviceManager->refreshConfigWithSolvedServices();
        $totalTime = microtime(true) - $start;
        $output->writeln("Solved services config file generated in {$totalTime} µs");
    }
}
