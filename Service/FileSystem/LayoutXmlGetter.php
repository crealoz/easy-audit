<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

class LayoutXmlGetter
{
    public function execute(): array
    {
        $layoutXmlFiles = [];

        $directory = new \RecursiveDirectoryIterator('app/');
        $iterator = new \RecursiveIteratorIterator($directory);

        // Get all files that are in layout directories and have the .xml extension
        $regex = new \RegexIterator($iterator, '/^.+\/view\/frontend\/layout\/.*\.xml$/i', \RecursiveRegexIterator::GET_MATCH);
        foreach ($regex as $file) {
            $layoutXmlFiles[] = $file[0];
        }

        return $layoutXmlFiles;
    }
}