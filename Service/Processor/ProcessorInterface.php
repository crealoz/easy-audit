<?php

namespace Crealoz\EasyAudit\Service\Processor;

interface ProcessorInterface
{
    public function process(array $data): array;
}
