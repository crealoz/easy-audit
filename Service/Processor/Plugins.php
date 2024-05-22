<?php

namespace Crealoz\EasyAudit\Service\Processor;

class Plugins implements ProcessorInterface
{

    public function process(array $data): array
    {
        $response = [];
        $fileName = $data['fileName'];
        $className = $data['className'];

    }
}
