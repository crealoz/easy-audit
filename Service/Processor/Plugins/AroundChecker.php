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
    public function execute($class, $filePath)
    {
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            throw new FileSystemException(__('Could not read file content'));
        }
        if (str_contains($fileContent, 'around')) {
            $callable = function($functionName) {
                return str_contains($functionName, 'around');
            };
            $aroundMethods = [];
            foreach (get_class_methods($class) as $methodName) {
                if ($callable($methodName)) {
                    $aroundMethods[] = $methodName;
                }
            }
            foreach ($aroundMethods as $aroundMethod) {
                $content = $this->functionsParser->getFunctionContent($class, $filePath, $aroundMethod);
                /**
                 * If $proceed is the first instruction of the method, throw an exception
                 */

            }
        }
    }
}
