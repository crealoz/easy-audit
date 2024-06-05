<?php

namespace Crealoz\EasyAudit\Service\Processor;

use Crealoz\EasyAudit\Exception\Processor\AuditProcessorException;

interface ProcessorInterface
{
    /**
     * @param $input
     */
    public function run($input): array;

    /**
     * @return string
     */
    public function getProcessorName(): string;

    /**
     * @return array
     */
    public function getResults(): array;
}
