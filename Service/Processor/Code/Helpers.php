<?php

namespace Crealoz\EasyAudit\Service\Processor\Code;

use Crealoz\EasyAudit\Service\Processor\AbstractProcessor;
use Crealoz\EasyAudit\Service\Processor\ProcessorInterface;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class Helpers extends AbstractProcessor implements ProcessorInterface
{
    protected string $processorName = 'Helpers';

    protected array $results = [
        'hasErrors' => false,
        'errors' => [
            'extensionOfAbstractHelper' => [
                'title' => 'Extension of Abstract Helper',
                'explanation' => 'Helper class must not extend Magento\Framework\App\Helper\AbstractHelper',
                'files' => []
            ],
        ],
        'warnings' => [],
    ];

    public function run($input): array
    {

    }
}