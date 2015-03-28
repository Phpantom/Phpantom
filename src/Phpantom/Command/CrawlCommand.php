<?php

namespace Phpantom\Command;

use Phpantom\Engine;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('crawl')
            ->setDescription('Crawl a site')
            ->addArgument(
                'scenario',
                InputArgument::REQUIRED,
                'Scenario name'
            )
            ->addArgument(
                'mode',
                InputArgument::OPTIONAL,
                'Crawl mode: start (default), restart, refresh?',
                Engine::MODE_START
            )
            ->addOption(
                'with_failed',
                null,
                InputOption::VALUE_NONE,
                'Add failed resource to frontier'
            )
            ->addOption(
                'new_session',
                null,
                InputOption::VALUE_NONE,
                'Start new session'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = new ContainerBuilder();
//        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../..'));
        $loader = new XmlFileLoader($container, new FileLocator('.'));
        $loader->load('services.xml');
        $scenarioName = strtolower($input->getArgument('scenario'));
        $loader->load( $scenarioName . '.xml');

        $scenario = $container->get('scenario');
        $output->writeln("<comment>Running scenario {$scenarioName}...</comment>");
        if ($input->getOption('new_session')) {
            $output->writeln("Starting new session");
        } else {
            $output->writeln("Proceeding old session");
        }
        $scenario->run($input->getArgument('mode'));

    }
}
