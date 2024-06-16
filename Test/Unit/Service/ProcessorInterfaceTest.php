<?php

namespace Crealoz\EasyAudit\Test\Unit\Service;

use PHPUnit\Framework\TestCase;

class ProcessorInterfaceTest extends TestCase
{
    public function testClassesImplementingProcessorInterface()
    {
        $classes = get_declared_classes();
        foreach ($classes as $class) {
            $reflector = new \ReflectionClass($class);
            if ($reflector->implementsInterface('Crealoz\EasyAudit\Service\Processor\ProcessorInterface')) {
                $instance = $reflector->newInstanceWithoutConstructor();

                // Test getProcessorName method
                $processorName = $instance->getProcessorName();
                $this->assertIsString($processorName);

                // Test getAuditSection method
                $auditSection = $instance->getAuditSection();
                $this->assertIsString($auditSection);

                // Test getResults method
                $results = $instance->getResults();
                $this->assertIsArray($results);
                $this->assertArrayHasKey('hasErrors', $results);
                $this->assertArrayHasKey('errors', $results);
                $this->assertArrayHasKey('warnings', $results);
                $this->assertArrayHasKey('suggestions', $results);
            }
        }
    }
}