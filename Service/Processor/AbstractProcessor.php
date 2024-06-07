<?php

namespace Crealoz\EasyAudit\Service\Processor;

use Crealoz\EasyAudit\Exception\Processor\GeneralAuditException;

abstract class AbstractProcessor
{
    protected string $processorName = '';

    protected array $results = [];

    /**
     * @throws GeneralAuditException
     */
    public function getProcessorName(): string
    {
        if ($this->processorName === '') {
            throw new GeneralAuditException(__('Processor name is not set'));
        }
        return $this->processorName;
    }

    /**
     * @throws GeneralAuditException
     */
    public function getResults(): array
    {
        if (
            !array_key_exists('hasErrors', $this->results)
            && !array_key_exists('errors', $this->results)
            && !array_key_exists('warnings', $this->results)
        ) {
            throw new GeneralAuditException(__('Results are malformed for processor ' . $this->getProcessorName() . '. Please check the processor implementation.'));
        }
        return $this->results;
    }
}