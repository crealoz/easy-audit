<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Crealoz\EasyAudit\Service\Type\TypeFactory;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\Storage\FileFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Audit
{

    protected array $results = [];

    public function __construct(
        protected LoggerInterface            $logger,
        protected readonly FileFactory       $fileFactory,
        protected readonly Filesystem        $filesystem,
        protected readonly FileGetterFactory $fileGetterFactory,
        protected readonly PDFWriter         $pdfWriter,
        protected readonly TypeFactory       $typeFactory,
        protected array                      $processors = []
    )
    {

    }

    public function run(InputInterface $input = null, OutputInterface $output = null): void
    {
        foreach ($this->processors as $typeName => $subTypes) {
            $type = $this->typeFactory->create($typeName);
            $this->results[$typeName] = $type->process($subTypes, $typeName, $output);
        }
        $this->pdfWriter->createdPDF($this->results);
    }
}
