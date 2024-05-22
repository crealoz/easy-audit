<?php

namespace Crealoz\EasyAudit\Service\Processor;

use Crealoz\EasyAudit\Exception\Processor\MagentoFrameworkPluginExtension;
use Crealoz\EasyAudit\Exception\Processor\PluginFileDoesNotExistException;
use Crealoz\EasyAudit\Exception\Processor\SameModulePluginException;
use Crealoz\EasyAudit\Service\Processor\Plugins\AroundChecker;
use Magento\Framework\Data\Collection\FilesystemFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;

class Plugins implements ProcessorInterface
{

    public function __construct(
        protected AroundChecker $aroundChecker,
        private readonly DirectoryList $directoryList,
        private readonly LoggerInterface $logger
    )
    {

    }

    /**
     * @param array $data
     * @return array
     * @throws MagentoFrameworkPluginExtension
     * @throws SameModulePluginException
     * @throws PluginFileDoesNotExistException
     */
    public function process(array $data)
    {
        $pluggingClass = $data['pluggingClassName'];
        $pluggedInClass = $data['pluggedInClass'];
        $this->isSameModulePlugin($pluggingClass, $pluggedInClass);
        $this->isMagentoFrameworkClass($pluggedInClass);
        $this->checkPluginFile($pluggingClass);
    }

    /**
     * @throws SameModulePluginException
     */
    private function isSameModulePlugin(string $pluggingClass, string $pluggedInClass)
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
    private function isMagentoFrameworkClass(string $pluggedInClass)
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
     */
    private function checkPluginFile(string $pluggingClass)
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
            $this->aroundChecker->execute($pluggingClassPath);
        } catch (FileSystemException|\ReflectionException $e) {
            $this->logger->error($e->getMessage());
        }
    }


}
