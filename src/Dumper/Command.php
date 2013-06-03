<?php

namespace Dumper;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

class Command extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('dumper')
            ->addArgument(
                'output_path',
                InputArgument::REQUIRED,
                'The directory that save the dumped zip file.'
            )
            ->addOption(
                'source',
                null,
                InputOption::VALUE_OPTIONAL,
                'The list of dumping stuff. <JSON format>',
                'source.json'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Loads stuff at path defined in [source] option.
        $stuff = $this->loadStuff($input->getOption('source'));

        // prepares the dispatcher, binds some
        $eventDispatcer = new EventDispatcher();
        $eventDispatcer->addListener('file_dumper.log', function (GenericEvent $event) use ($output) {
            $output->writeln($event->getArgument('message'));
        });
        $eventDispatcer->addListener('file_dumper.log_section', function (GenericEvent $event) use ($output) {
            $output->writeln(sprintf("<info>%s</info>\t%s", $event->getArgument('section'), $event->getArgument('message')));
        });

        $outputPath = $input->getArgument('output_path');

        $dumper = new CustomLoggingFileDumper($eventDispatcer, $outputPath);
        $dumper->dump($stuff);
    }

    private function loadStuff($sourcePath)
    {
        if (!is_readable($sourcePath)) {
            throw new \InvalidArgumentException(sprintf('Unable to load the source file "%s".', $sourcePath));
        }

        $stuff = json_decode(file_get_contents($sourcePath), true);
        if (!is_array($stuff)) {
            throw new \RuntimeException(sprintf('Loaded source file "%s" format is invalid.', $sourcePath));
        }

        return $stuff;
    }
}