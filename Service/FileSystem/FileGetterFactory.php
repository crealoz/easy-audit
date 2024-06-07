<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Magento\Framework\ObjectManagerInterface;

class FileGetterFactory
{

    public function __construct(
        protected ObjectManagerInterface $objectManager
    ) {

    }

    public function create(string $type): FileGetterInterface
    {
        return match(
            $type
        ) {
            'di' => $this->objectManager->get('\Crealoz\EasyAudit\Service\FileSystem\DiXmlGetter'),
            'layout' => $this->objectManager->get('\Crealoz\EasyAudit\Service\FileSystem\LayoutXmlGetter'),
            'helpers' => $this->objectManager->get('\Crealoz\EasyAudit\Service\FileSystem\HelpersGetter'),
            'phtml' => $this->objectManager->get('\Crealoz\EasyAudit\Service\FileSystem\PhtmlGetter'),
            default => throw new \InvalidArgumentException("Unknown file getter type: $type")
        };
    }
}