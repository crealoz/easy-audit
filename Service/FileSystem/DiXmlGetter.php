<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

class DiXmlGetter
{
    public function getDiXmlFiles(): array
    {
        $directory = new \RecursiveDirectoryIterator('app/code/');
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/^.+di\.xml$/i', \RecursiveRegexIterator::GET_MATCH);

        $diXmlFiles = [];
        foreach($regex as $file) {
            $diXmlFiles[] = $file[0];
        }

        return $diXmlFiles;
    }
}
