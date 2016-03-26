<?php

namespace Phpantom\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EngineCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('engine')
            ->setDescription('Performs action on engine')
            ->addArgument(
                'project',
                InputArgument::REQUIRED,
                'Project name'
            )
            ->addArgument(
                'action',
                InputArgument::REQUIRED,
                "Action name. Available actions: \n
                init, clearVisited, clearScheduled, clearFrontier, clearFailed, clearSuccessful"
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');
        $project = strtolower($input->getArgument('project'));
        if ('init' !== $action) {
            $container = new ContainerBuilder();
            $loader = new XmlFileLoader($container, new FileLocator('.'));
            $loader->load('services.xml');
            $loader->load($project . '.xml');
            $engine = $container->get('engine');
            $output->writeln("<comment>Running action {$action}...</comment>");
            $engine->$action();
        } else {
            if (!file_exists('services.xml')) {
                $services = file_get_contents(__DIR__ . '/services.xml.dist');
                $blobsRoot = sys_get_temp_dir() . "/phpantom_{$project}";
                if (file_exists($blobsRoot)) {
                    mkdir($blobsRoot, 0777);
                }
                file_put_contents('services.xml', str_replace('{{blobs_root}}', $blobsRoot, $services ));
            }
            $projectConfig = $project . '.xml';
            if (!file_exists($projectConfig)) {
                copy(__DIR__ . '/project.xml.dist', $projectConfig);
            }
        }
    }
}
