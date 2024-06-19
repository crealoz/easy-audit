<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Service\Type\TypeFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Audit
{

    protected array $results = [];

    public function __construct(
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
