<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Service\FileSystem\DiXmlGetter;
use Crealoz\EasyAudit\Service\FileSystem\LayoutXmlGetter;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\Storage\FileFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Audit
{

    protected array $results = [];

    public function __construct(
        protected DiXmlGetter            $diXmlGetter,
        protected LoggerInterface        $logger,
        protected readonly FileFactory   $fileFactory,
        protected readonly Filesystem    $filesystem,
        private readonly LayoutXmlGetter $layoutXmlGetter,
        private readonly PDFWriter       $pdfWriter,
        protected array                  $processors = []
    )
    {

    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $diProcessors = $this->processors['di'] ?? [];
        if (!empty($diProcessors)) {
            $this->processForDi($diProcessors, $output);
        }
        $viewProcessors = $this->processors['view'] ?? [];
        if (!empty($viewProcessors)) {
            if (!empty($viewProcessors['layout'])) {
                $this->processForLayout($viewProcessors['layout'], $output);
            }
        }
        $this->pdfWriter->createdPDF($this->results);
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

    protected function processForLayout($viewProcessors, $output = null): void
    {
        $this->results['view'] = [];
        $layoutXmlFiles = $this->layoutXmlGetter->execute();

        if ($output) {
            /** if we are in command line, we display a bar */
            $progressBar = new ProgressBar($output, count($layoutXmlFiles));
            $progressBar->start();
        }
        foreach ($layoutXmlFiles as $layoutXmlFile) {
            $xml = simplexml_load_file($layoutXmlFile);
            if ($output) {
                $progressBar->advance();
            }
            if ($xml === false) {
                $this->logger->error("Failed to load XML file: $layoutXmlFile");
                continue;
            }
            /** @var \Crealoz\EasyAudit\Service\Processor\ProcessorInterface $processor */
            foreach ($viewProcessors as $processor) {
                $processor->run($xml);
            }
        }
        foreach ($viewProcessors as $processor) {
            $this->results['view'][$processor->getProcessorName()] = $processor->getResults();
        }
    }
}
