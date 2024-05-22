<?php

namespace Crealoz\EasyAudit\Service\Processor;

use Crealoz\EasyAudit\Exception\Processor\AuditProcessorException;

interface ProcessorInterface
{
    /**
     * @param array $data
     * @throws AuditProcessorException
     */
    public function process(array $data);
}
