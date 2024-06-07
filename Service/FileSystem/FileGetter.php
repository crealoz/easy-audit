<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use RegexIterator;

/**
 * This class is responsible for getting files from a directory but is not intended to be used directly. Usage is to
 * create a virtual class using di.xml with the path and pattern as arguments.
 *
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class FileGetter implements FileGetterInterface
{
    public function __construct(
        protected string $path,
        protected string $pattern
    )
    {

    }

    public function execute(): array
    {
        $directory = new \RecursiveDirectoryIterator($this->path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, $this->pattern, RegexIterator::GET_MATCH);
        $files = [];
        foreach ($regex as $file) {
            $files[] = $file[0];
        }
        return $files;
    }
}