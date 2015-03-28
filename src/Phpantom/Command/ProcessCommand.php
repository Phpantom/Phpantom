<?php

namespace Phpantom\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ProcessCommand extends Command
{
    const PROCESSOR_CONSOLE = 'console';
    const PROCESSOR_CSV = 'csv';

    protected function configure()
    {
        $this
            ->setName('processor')
            ->setDescription('Process documents')
            ->addArgument(
                'project',
                InputArgument::REQUIRED,
                'Project name'
            )
            ->addArgument(
                'doc_type',
                InputArgument::REQUIRED,
                'Document type'
            )
            ->addArgument(
                'processor',
                InputArgument::OPTIONAL,
                'Processor name',
                self::PROCESSOR_CONSOLE
            )
            ->addArgument(
                'params',
                InputArgument::IS_ARRAY,
                'Processor params'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('doc_type');
        $processor = $input->getArgument('processor');
        $paramsArr = $input->getArgument('params');
        $params = [];

        $container = new ContainerBuilder();
//        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../..'));
        $loader = new XmlFileLoader($container, new FileLocator('.'));
        $loader->load('services.xml');
        $project = strtolower($input->getArgument('project'));
        $loader->load( $project . '.xml');

        $class = '\Phantom\Processor\\' . ucfirst(strtolower($processor));
        $storage = $container->get('document_storage');

        if (!class_exists($class)) {
            throw new \RuntimeException('Unknown processor ' . $processor);
        }
        $output->writeln("<comment>Processing documents with {$processor}...</comment>");
        if ($paramsArr) {
            foreach ($paramsArr as $paramStr)
                if (strpos($paramStr, ':')) {
                    list ($key, $val) = explode(':', $paramStr, 2);
                    $params[$key] = $val;
                }
        }

        (new $class($storage))->apply($type, $params);

    }
}
