<?php

namespace Crealoz\EasyAudit\Service\Processor\Plugins;

use Crealoz\EasyAudit\Service\Parser\Functions;
use Magento\Framework\Exception\FileSystemException;

class AroundChecker
{
    public function __construct(
        protected readonly Functions $functionsParser
    )
    {

    }

    /**
     * @throws \ReflectionException
     * @throws FileSystemException
     */
    public function execute($filePath)
    {
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            throw new FileSystemException(__('Could not read file content'));
        }
        if (str_contains($fileContent, 'around')) {
            $callable = function($functionName) {
                return str_contains($functionName, 'around');
            };
            $functions = $this->functionsParser->getFunctionsFromTokens(token_get_all($fileContent), $filePath, $callable);
            foreach ($functions as $functionName => $functionContent) {
                dd($functionName, $functionContent);
            }
        }
    }
}
