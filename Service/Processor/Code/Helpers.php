<?php

namespace Crealoz\EasyAudit\Service\Processor\Code;

use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Crealoz\EasyAudit\Service\Processor\AbstractProcessor;
use Crealoz\EasyAudit\Service\Processor\ProcessorInterface;
use Magento\Framework\Exception\FileSystemException;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class Helpers extends AbstractProcessor implements ProcessorInterface
{
    protected string $processorName = 'Helpers';

    protected array $results = [
        'hasErrors' => false,
        'errors' => [
            'extensionOfAbstractHelper' => [
                'title' => 'Extension of Abstract Helper',
                'explanation' => 'Helper class must not extend Magento\Framework\App\Helper\AbstractHelper',
                'files' => []
            ],
            'helpersInsteadOfViewModels' => [
                'title' => 'Helpers Instead of View Models',
                'explanation' => 'Helpers should not be used as View Models',
                'files' => []
            ],
        ],
        'warnings' => [
            'couldNotReadFile' => [
                'title' => 'Could not read file',
                'explanation' => 'Could not read file',
                'files' => []
            ],
            'couldNotParseCorrectlyContent' => [
                'title' => 'Could not parse correctly content',
                'explanation' => 'Could not parse correctly content',
                'files' => []
            ],
        ],
    ];

    private array $helpersInPhtmlFiles = [];

    private array $ignoredHelpers = [
        'Magento\Customer\Helper\Address',
        'Magento\Tax\Helper\Data',
        'Magento\Msrp\Helper\Data',
        'Magento\Catalog\Helper\Output',
        '\Magento\Directory\Helper\Data'
    ];


    public function __construct(
        private readonly FileGetterFactory $fileGetterFactory,
        protected readonly \Magento\Framework\Filesystem\DriverInterface $driver,
        protected readonly \Magento\Framework\Filesystem\Io\File $io
    )
    {
    }

    public function run($input): array
    {
        if (empty($this->helpersInPhtmlFiles)){
            $this->retrieveHelpersInPhtml();
        }
        // First we get class name from the input that represents the file's path
        $className = $this->getClassName($input);

        $reflection = new \ReflectionClass($className);
        if ($reflection->isSubclassOf('Magento\Framework\App\Helper\AbstractHelper')) {
            $this->results['hasErrors'] = true;
            if (isset($this->helpersInPhtmlFiles[$className])) {
                $this->results['errors']['helpersInsteadOfViewModels']['files'][$className] = $this->helpersInPhtmlFiles[$className];
            } else {
                $this->results['errors']['extensionOfAbstractHelper']['files'][] = $className;
            }
        }
        return $this->results;
    }

    public function getResults(): array
    {
        $results = parent::getResults();
        foreach ($results['errors']['helpersInsteadOfViewModels']['files'] as $className => $templates) {
            $results['errors']['helpersInsteadOfViewModels']['files'][$className]['usageCount'] = 1;
            foreach ($templates as $key => $template) {
                if (!isset($results['errors']['helpersInsteadOfViewModels']['files'][$className][$template])) {
                    $results['errors']['helpersInsteadOfViewModels']['files'][$className][$template] = 1;
                } else {
                    $results['errors']['helpersInsteadOfViewModels']['files'][$className][$template]++;
                }
                $results['errors']['helpersInsteadOfViewModels']['files'][$className]['usageCount']++;
                unset($results['errors']['helpersInsteadOfViewModels']['files'][$className][$key]);
            }
        }
        return $results;
    }

    protected function retrieveHelpersInPhtml(): void
    {
        $phtmlFilesGetter = $this->fileGetterFactory->create('phtml');
        $phtmlFiles = $phtmlFilesGetter->execute();
        foreach ($phtmlFiles as $phtmlFile) {
            try {
                $this->getHelpersFromPhtml($phtmlFile);
            } catch (FileSystemException $e) {
                $this->results['hasErrors'] = true;
                $this->results['warnings']['couldNotReadFile']['files'][] = $phtmlFile;
            }
        }
    }

    /**
     * @throws FileSystemException
     */
    protected function getHelpersFromPhtml(string $phtmlFile): void
    {
        $content = $this->driver->fileGetContents($phtmlFile);
        $matches = [];
        preg_match_all('/\$this->helper\((.*?)\)/s', $content, $matches);
        foreach ($matches[1] as $match) {
            $className = trim($match, '\'"');
            $className = str_replace('::class', '', $className);
            if (in_array($className, $this->ignoredHelpers)) {
                continue;
            }
            // Check if the class is an alias or an import
            if (!str_contains($className, '\\')) {
                preg_match("/use (.*\\\\$className)/", $content, $importMatches);
                if (!empty($importMatches)) {
                    $className = $importMatches[1];
                } else {
                    $this->results['hasErrors'] = true;
                    $this->results['warnings']['couldNotParseCorrectlyContent']['files'][$phtmlFile] = __('Looking for the class name %1', $className);
                }
            }
            if (!isset($this->helpersInPhtmlFiles[$className])) {
                $this->helpersInPhtmlFiles[$className] = [];
            }
            $this->helpersInPhtmlFiles[$className][] = $phtmlFile;
        }
    }

    protected function getClassName(string $filePath): string
    {
        // Remove the 'app/code/' part from the file path
        $relativePath = str_replace('app/code/', '', $filePath);

        // Get the file name without extension
        $fileName = $this->io->getPathInfo($relativePath)['filename'];

        // Get the directory name
        $dirName = $this->driver->getParentDirectory($relativePath);

        // Replace directory separators with namespace separators
        return str_replace('/', '\\', $dirName) . '\\' . $fileName;
    }
}