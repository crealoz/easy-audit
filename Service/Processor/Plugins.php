<?php

namespace Crealoz\EasyAudit\Service\Processor;

use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToAfterPluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\AroundToBeforePluginException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\MagentoFrameworkPluginExtension;
use Crealoz\EasyAudit\Exception\Processor\Plugins\PluginFileDoesNotExistException;
use Crealoz\EasyAudit\Exception\Processor\Plugins\SameModulePluginException;
use Crealoz\EasyAudit\Service\Processor\Plugins\AroundChecker;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;

class Plugins extends AbstractProcessor implements ProcessorInterface
{
    protected string $processorName = 'plugins';

    protected array $results = [
        'hasErrors' => false,
        'errors' => [
            'sameModulePlugin' => [
                'title' => 'Same Module Plugin',
                'explanation' => 'Plugin class must not be in the same module as the plugged in class',
                'files' => []
            ],
            'magentoFrameworkPlugin' => [
                'title' => 'Magento Framework Plugin',
                'explanation' => 'Plugin class must not be in the Magento Framework',
                'files' => []
            ],
        ],
        'warnings' => [
            'nonExistentPluginFile' => [
                'title' => 'Non-existent Plugin File',
                'explanation' => 'Plugin file does not exist',
                'files' => []
            ],
            'insufficientPermissions' => [
                'title' => 'Insufficient Permissions',
                'explanation' => 'Insufficient permissions to read file',
                'files' => []
            ],
            'aroundToBeforePlugin' => [
                'title' => 'Around to Before Plugin',
                'explanation' => 'Around plugin should be a before plugin',
                'files' => []
            ],
            'aroundToAfterPlugin' => [
                'title' => 'Around to After Plugin',
                'explanation' => 'Around plugin should be an after plugin',
                'files' => []
            ],
        ],
        'suggestions' => [],
    ];

    public function __construct(
        protected AroundChecker $aroundChecker,
        private readonly DirectoryList $directoryList,
        private readonly LoggerInterface $logger
    )
    {

    }

    /**
     * @param $input
     * @return array
     */
    public function run($input): array
    {

        //Check if the input is an XML object
        if (!($input instanceof \SimpleXMLElement)) {
            throw new \InvalidArgumentException("Input must be an instance of SimpleXMLElement");
        }

        // Get all 'type' nodes that contain a 'plugin' node
        $typeNodes = $input->xpath('//type[plugin]');

        try {
            foreach ($typeNodes as $typeNode) {
                // Get all 'plugin' nodes within the current 'type' node
                $pluginNodes = $typeNode->xpath('plugin');

                $pluggedClassName = (string)$typeNode['name'];

                foreach ($pluginNodes as $pluginNode) {
                    $pluggingClassName = (string)$pluginNode['type'];
                    $pluginDisabled = (string)$pluginNode['disabled'] ?? 'false';
                    if ($pluginDisabled === 'true') {
                        continue;
                    }
                    $this->process($pluggingClassName, $pluggedClassName);
                }
            }
        } catch (FileSystemException $e) {
            $this->results['warnings']['insufficientPermissions']['files'][] = $e->getMessage();
        }
        return $this->results;
    }

    /**
     * @param $pluggingClass
     * @param $pluggedInClass
     * @throws FileSystemException
     */
    protected function process($pluggingClass, $pluggedInClass): void
    {
        try {
            $this->isSameModulePlugin($pluggingClass, $pluggedInClass);
            $this->isMagentoFrameworkClass($pluggingClass, $pluggedInClass);
            $this->checkPluginFile($pluggingClass);
        } catch (MagentoFrameworkPluginExtension $e) {
            $this->results['hasErrors'] = true;
            $this->results['errors']['magentoFrameworkPlugin']['files'][] = $e->getErroneousFile();
        } catch (PluginFileDoesNotExistException $e) {
            $this->results['hasErrors'] = true;
            $this->results['warnings']['nonExistentPluginFile']['files'][] = $e->getErroneousFile();
        } catch (SameModulePluginException $e) {
            $this->results['hasErrors'] = true;
            $this->results['errors']['sameModulePlugin']['files'][] = $e->getErroneousFile();
        } catch (AroundToBeforePluginException $e) {
            $this->results['hasErrors'] = true;
            $this->results['warnings']['aroundToBeforePlugin']['files'][] = $e->getErroneousFile();
        } catch (AroundToAfterPluginException $e) {
            $this->results['hasErrors'] = true;
            $this->results['warnings']['aroundToAfterPlugin']['files'][] = $e->getErroneousFile();
        }
    }

    /**
     * @throws SameModulePluginException
     */
    private function isSameModulePlugin(string $pluggingClass, string $pluggedInClass): void
    {
        $pluggingClassParts = explode('\\', $pluggingClass);
        $pluggedInClassParts = explode('\\', $pluggedInClass);
        if ($pluggingClassParts[0].'\\'.$pluggingClassParts[1] === $pluggedInClassParts[0].'\\'.$pluggedInClassParts[1]) {
            throw new SameModulePluginException(
                __("Plugin class must not be in the same module as the plugged in class"),
                $pluggingClass
            );
        }
    }

    /**
     * @throws MagentoFrameworkPluginExtension
     */
    private function isMagentoFrameworkClass(string $pluggingClass, string $pluggedInClass): void
    {
        if (str_starts_with($pluggedInClass, 'Magento\\Framework\\')) {
            throw new MagentoFrameworkPluginExtension(
                __('Plugin class must not be in the Magento Framework'),
                $pluggingClass . " is plugged on " . $pluggedInClass
            );
        }
    }

    /**
     * @throws PluginFileDoesNotExistException
     * @throws FileSystemException
     * @throws AroundToBeforePluginException
     * @throws AroundToAfterPluginException
     */
    private function checkPluginFile(string $pluggingClass): void
    {
        $pluggingClassParts = explode('\\', $pluggingClass);

        /**
         * get file path in magento environment
         */
        $directoryPath = $this->directoryList->getPath('app');
        $pluggingClassPath = $directoryPath.'/code/'.implode('/', $pluggingClassParts).'.php';
        if (!file_exists($pluggingClassPath)) {
            throw new PluginFileDoesNotExistException(
                __("Plugin file does not exist: $pluggingClassPath"),
                $pluggingClassPath
            );
        }
        /**
         * Parse code for around plugins
         */
        try {
            $this->aroundChecker->execute($pluggingClass, $pluggingClassPath);
        } catch (FileSystemException|\ReflectionException $e) {
            $this->logger->error($e->getMessage());
        }
    }


}
