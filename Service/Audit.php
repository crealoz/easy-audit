<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Service\FileSystem\DiXmlGetter;
use Crealoz\EasyAudit\Service\FileSystem\HelpersGetter;
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
        protected LoggerInterface        $logger,
        protected readonly FileFactory   $fileFactory,
        protected readonly Filesystem    $filesystem,
        protected readonly DiXmlGetter     $diXmlGetter,
        protected readonly LayoutXmlGetter $layoutXmlGetter,
        protected readonly HelpersGetter   $helpersGetter,
        protected readonly PDFWriter       $pdfWriter,
        protected array                  $processors = []
    )
    {

    }

    public function run(InputInterface $input = null, OutputInterface $output = null): void
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
        $codeProcessors = $this->processors['code'] ?? [];
        if (!empty($codeProcessors)) {
            $this->processForCode($codeProcessors, $output);
        }
        $this->pdfWriter->createdPDF($this->results);
    }

    protected function processForDi(array $diProcessors, OutputInterface $output = null): void
    {
        $this->results['di'] = [];
        $diXmlFiles = $this->diXmlGetter->execute();

        if (!empty($diXmlFiles)) {
            $this->processXml($diProcessors, $diXmlFiles, $output);
        }
    }

    protected function processForLayout(array $viewProcessors, OutputInterface $output = null): void
    {
        $this->results['view'] = [];
        $layoutXmlFiles = $this->layoutXmlGetter->execute();

        if (!empty($layoutXmlFiles)) {
            $this->processXml($viewProcessors, $layoutXmlFiles, $output);
        }
    }

    protected function processForCode(array $codeProcessors, OutputInterface $output = null): void
    {
        $this->results['code'] = [];
        $codeFiles = $this->helpersGetter->execute();

        if (!empty($codeFiles)) {
            $this->processCode($codeProcessors, $codeFiles, $output);
        }
    }

    protected function processXml(array $viewProcessors, array $xmlFiles, OutputInterface $output =null): void
    {
        if ($output) {
            /** if we are in command line, we display a bar */
            $progressBar = new ProgressBar($output, count($xmlFiles));
            $progressBar->start();
        }
        foreach ($xmlFiles as $layoutXmlFile) {
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
        if ($output) {
            $progressBar->finish();
        }
    }
}
