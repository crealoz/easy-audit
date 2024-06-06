<?php

namespace Crealoz\EasyAudit\Service\Processor\Di;

use Crealoz\EasyAudit\Service\Processor\AbstractProcessor;
use Crealoz\EasyAudit\Service\Processor\ProcessorInterface;

class Preferences extends AbstractProcessor implements ProcessorInterface
{
    protected string $processorName = 'Preferences';

    private array $existingPreferences = [];

    protected array $results = [
        'hasErrors' => false,
        'errors' => [
            'multiplePreferences' => [
                'title' => 'Multiple Preferences',
                'explanation' => 'Multiple preferences found for the same file. This can lead to unexpected behavior.
                 Please remove the duplicate preferences or check that sequence is done correctly in module declaration.',
                'files' => []
            ],
        ],
        'warnings' => [],
    ];

    public function run($input): array
    {
        //Check if the input is an XML object
        if (!($input instanceof \SimpleXMLElement)) {
            throw new \InvalidArgumentException("Input must be an instance of SimpleXMLElement");
        }

        // Get all 'preference' nodes
        $preferences = $input->xpath('//preference');

        // Loop through all 'preference' nodes and get the 'for' and 'type' attributes
        foreach ($preferences as $preference) {
            $preferenceFor = (string)$preference['for'];
            $preferenceType = (string)$preference['type'];
            if (array_key_exists($preferenceFor, $this->existingPreferences)) {
                $this->results['hasErrors'] = true;
                $this->existingPreferences[$preferenceFor][] = $preferenceType;
                $this->results['errors']['multiplePreferences']['files'][$preferenceFor] = $this->existingPreferences[$preferenceFor];
            } else {
                $this->existingPreferences[$preferenceFor] = [$preferenceType];
            }
        }

        return $this->results;
    }
}