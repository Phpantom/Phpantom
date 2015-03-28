<?php

namespace Phpantom\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class InfoCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('info')
            ->setDescription('Statistics information about the project')
            ->addArgument(
                'project',
                InputArgument::REQUIRED,
                'Project name'
            )

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $container = new ContainerBuilder();
//        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../..'));
        $loader = new XmlFileLoader($container, new FileLocator('.'));
        $loader->load('services.xml');
        $project = strtolower($input->getArgument('project'));
        $loader->load( $project . '.xml');

        $storage = $container->get('document_storage');
        $filter = $container->get('filter');

        $prefix = $project? $project . ':' : '';

        $output->writeln("<comment>Statistics about '{$project}' project</comment>");
        $table = new Table($output);
        $table
            ->setHeaders(array('Scheduled', 'Visited', 'Parsed', 'Failed', 'Not Parsed'))

            ->setRows(array(
                    array(
                        $filter->count($prefix .'scheduled'),
                        $filter->count($prefix .'visited'),
                        $filter->count($prefix .'parsed'),
                        '<error>' . $filter->count($prefix .'failed') .'</error>',
                        '<error>' . $filter->count($prefix .'not-parsed') .'</error>'
                    ),
                ))
        ;
        $table->render();
        $docTypes = $storage->getTypes();
        if ($docTypes) {
            foreach ($docTypes as $docType) {
                $count = $storage->count($docType);
                $output->writeln("<comment>Documents of type '{$docType}': {$count}</comment>");
            }
        }

    }
}
