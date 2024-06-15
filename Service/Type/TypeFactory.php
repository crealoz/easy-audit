<?php

namespace Crealoz\EasyAudit\Service\Type;

class TypeFactory
{
    public function __construct(
        protected readonly \Magento\Framework\ObjectManagerInterface $objectManager,
        protected readonly array $typeMapping,
    )
    {
    }

    public function create(string $type): TypeInterface
    {
        if (!isset($this->typeMapping[$type])) {
            throw new \InvalidArgumentException("Unknown type: $type");
        }
        return $this->objectManager->get($this->typeMapping[$type]);
    }
}