<?php

namespace Crealoz\EasyAudit\Service\Processor;

use Crealoz\EasyAudit\Exception\Processor\GeneralAuditException;

class AbstractProcessor
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

    public function getResults(): array
    {
        if (!array_key_exists('hasErrors', $this->results)) {
            throw new GeneralAuditException(__('Results are malformed. Missing "hasErrors" key.'));
        }
        return $this->results;
    }
}