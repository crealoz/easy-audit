<?php

namespace Crealoz\EasyAudit\Service\Processor;

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
                'explanation' => 'Plugin class must not be in the same module as the plugged in class',
                'files' => []
            ],
            'magentoFrameworkPlugin' => [
                'explanation' => 'Plugin class must not be in the Magento Framework',
                'files' => []
            ],
        ],
        'warnings' => [
            'nonExistentPluginFile' => [
                'explanation' => 'Plugin file does not exist',
                'files' => []
            ],
            'insufficientPermissions' => [
                'explanation' => 'Insufficient permissions to read file',
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
                    try {
                        $this->process($pluggingClassName, $pluggedClassName);
                    } catch (MagentoFrameworkPluginExtension $e) {
                        $this->results['errors']['magentoFrameworkPlugin']['files'][] = $e->getErroneousFile();
                    } catch (PluginFileDoesNotExistException $e) {
                        $this->results['warnings']['nonExistentPluginFile']['files'][] = $e->getErroneousFile();
                    } catch (SameModulePluginException $e) {
                        $this->results['errors']['sameModulePlugin']['files'][] = $e->getErroneousFile();
                    }
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
     * @throws MagentoFrameworkPluginExtension
     * @throws PluginFileDoesNotExistException
     * @throws SameModulePluginException
     * @throws FileSystemException
     */
    protected function process($pluggingClass, $pluggedInClass): void
    {
        $this->isSameModulePlugin($pluggingClass, $pluggedInClass);
        $this->isMagentoFrameworkClass($pluggedInClass);
        $this->checkPluginFile($pluggingClass);
    }

    /**
     * @throws SameModulePluginException
     */
    private function isSameModulePlugin(string $pluggingClass, string $pluggedInClass): array
    {
        $pluggingClassParts = explode('\\', $pluggingClass);
        $pluggedInClassParts = explode('\\', $pluggedInClass);
        if ($pluggingClassParts[0].'\\'.$pluggingClassParts[1] === $pluggedInClassParts[0].'\\'.$pluggedInClassParts[1]) {
            throw new SameModulePluginException(
                __("Plugin class must not be in the same module as the plugged in class"),
                $pluggingClass
            );
        }
        return ['vendor' => $pluggingClassParts[0], 'module' => $pluggingClassParts[1]];
    }

    /**
     * @throws MagentoFrameworkPluginExtension
     */
    private function isMagentoFrameworkClass(string $pluggedInClass): void
    {
        if (str_starts_with($pluggedInClass, 'Magento\\Framework\\')) {
            throw new MagentoFrameworkPluginExtension(
                __('Plugin class must not be in the Magento Framework'),
                $pluggedInClass
            );
        }
    }

    /**
     * @throws PluginFileDoesNotExistException
     * @throws FileSystemException
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
