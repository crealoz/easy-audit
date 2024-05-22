<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Exception\Processor\MagentoFrameworkPluginExtension;
use Crealoz\EasyAudit\Exception\Processor\PluginFileDoesNotExistException;
use Crealoz\EasyAudit\Exception\Processor\SameModulePluginException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Audit
{

    protected array $results = [
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
            ]
        ],
        'suggestions' => [],
    ];

    public function __construct(
        protected \Crealoz\EasyAudit\Service\FileSystem\DiXmlGetter $diXmlGetter,
        protected \Crealoz\EasyAudit\Service\Processor\Plugins      $pluginsProcessor,
        protected LoggerInterface                                   $logger
    )
    {

    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $diXmlFiles = $this->diXmlGetter->getDiXmlFiles();
        if ($output) {
            /** if we are in command line, we display a bar */
            $progressBar = new ProgressBar($output, count($diXmlFiles));
            $progressBar->start();
        }
        foreach ($diXmlFiles as $diXmlFile) {
            $xml = simplexml_load_file($diXmlFile);
            if ($output) {
                $progressBar->advance();
            }
            if ($xml === false) {
                $this->logger->error("Failed to load XML file: $diXmlFile");
                continue;
            }
            try {
                $this->checkPlugins($xml);
            } catch (MagentoFrameworkPluginExtension $e) {
                $this->results['errors']['magentoFrameworkPlugin']['files'][] = $e->getErroneousFile();
            } catch (PluginFileDoesNotExistException $e) {
                $this->results['warnings']['nonExistentPluginFile']['files'][] = $e->getErroneousFile();
            } catch (SameModulePluginException $e) {
                $this->results['errors']['sameModulePlugin']['files'][] = $e->getErroneousFile();
            }
        }
        if ($output) {
            $progressBar->finish();
        }
        dump($this->results);
    }

    /**
     * @throws MagentoFrameworkPluginExtension
     * @throws PluginFileDoesNotExistException
     * @throws SameModulePluginException
     */
    private function checkPlugins($xml)
    {
        // Get all 'type' nodes that contain a 'plugin' node
        $typeNodes = $xml->xpath('//type[plugin]');

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
                $this->pluginsProcessor->process([
                    'pluggingClassName' => $pluggingClassName,
                    'pluggedInClass' => $pluggedClassName,
                ]);
            }
        }
    }
}
