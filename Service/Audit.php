<?php

namespace Crealoz\EasyAudit\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Audit
{

    protected array $results = [];

    public function __construct(
        protected \Crealoz\EasyAudit\Service\FileSystem\DiXmlGetter $diXmlGetter,
        protected LoggerInterface                                   $logger,
        protected array $processors = []
    )
    {

    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $diProcessors = $this->processors['di'] ?? [];
        if (!empty($diProcessors)) {
            $this->processForDi($diProcessors, $output);
        }
        dump($this->results);
    }

    protected function processForDi($diProcessors, $output = null): void
    {
        $this->results['di'] = [];
        $diXmlFiles = $this->diXmlGetter->getDiXmlFiles();
        if ($output) {
            /** if we are in command line, we display a bar */
            $progressBar = new ProgressBar($output, count($diXmlFiles));
            $progressBar->start();
        }
        foreach ($diXmlFiles as $diXmlFile) {
            $xml = simplexml_load_file($diXmlFile);
            if ($output) {
                $progressBar->advance();
            }
            if ($xml === false) {
                $this->logger->error("Failed to load XML file: $diXmlFile");
                continue;
            }
            /** @var \Crealoz\EasyAudit\Service\Processor\ProcessorInterface $processor */
            foreach ($diProcessors as $processor) {
                $processor->run($xml);
            }
        }
        /** @var \Crealoz\EasyAudit\Service\Processor\ProcessorInterface $processor */
        foreach ($diProcessors as $processor) {
            $this->results['di'][$processor->getProcessorName()] = $processor->getResults();
        }
        if ($output) {
            $progressBar->finish();
        }
    }
}
