<?php

namespace Crealoz\EasyAudit\Service;

use Psr\Log\LoggerInterface;

class Audit
{

    public function __construct(
        protected \Crealoz\EasyAudit\Service\FileSystem\DiXmlGetter $diXmlGetter,
        protected \Crealoz\EasyAudit\Service\Processor\Plugins      $pluginsProcessor,
        protected LoggerInterface $logger
    )
    {

    }

    public function run()
    {
        $diXmlFiles = $this->diXmlGetter->getDiXmlFiles();
        foreach ($diXmlFiles as $diXmlFile) {
            $xml = simplexml_load_file($diXmlFile);
            $this->checkPlugins($xml);
        }
    }

    private function checkPlugins($xml)
    {
        // Get all 'type' nodes that contain a 'plugin' node
        $typeNodes = $xml->xpath('//type[plugin]');

        foreach ($typeNodes as $typeNode) {
            // Get all 'plugin' nodes within the current 'type' node
            $pluginNodes = $typeNode->xpath('plugin');

            $typeNodeName = (string)$typeNode['name'];

            foreach ($pluginNodes as $pluginNode) {
                $pluginName = (string)$pluginNode['name'];
                $pluginType = (string)$pluginNode['type'];
                $pluginClass = (string)$pluginNode['class'];
                $pluginMethod = (string)$pluginNode['method'];
                $pluginDisabled = (string)$pluginNode['disabled'] ?? 'false';
                if ($pluginDisabled === 'true') {
                    continue;
                }
                $this->checkPlugin($pluginName, $pluginType, $pluginClass, $pluginMethod);
            }
        }
    }
}
