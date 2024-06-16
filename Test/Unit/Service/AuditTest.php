<?php

namespace Crealoz\EasyAudit\Test\Unit\Service;

use Crealoz\EasyAudit\Service\FileSystem\FileGetterFactory;
use Crealoz\EasyAudit\Service\Type\TypeFactory;
use PHPUnit\Framework\TestCase;
use Crealoz\EasyAudit\Service\Audit;
use Psr\Log\LoggerInterface;
use Magento\MediaStorage\Model\File\Storage\FileFactory;
use Magento\Framework\Filesystem;
use Crealoz\EasyAudit\Service\PDFWriter;

/**
 * @covers Audit
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class AuditTest extends TestCase
{
    private Audit $audit;
    private $logger;
    private $fileFactory;
    private $filesystem;
    private $pdfWriter;
    private $fileGetterFactory;
    private TypeFactory $typeFactory;

    protected array $dummyResults = [
        'hasErrors' => true,
        'errors' => [
            'sameModulePlugin' => [
                'title' => 'Same Module Plugin',
                'explanation' => 'Plugin class must not be in the same module as the plugged in class',
                'files' => ['/path/to/dummy/file1.php', '/path/to/dummy/file2.php']
            ],
            'magentoFrameworkPlugin' => [
                'title' => 'Magento Framework Plugin',
                'explanation' => 'Plugin class must not be in the Magento Framework',
                'files' => ['/path/to/dummy/file3.php', '/path/to/dummy/file4.php']
            ],
        ],
        'warnings' => [
            'nonExistentPluginFile' => [
                'title' => 'Non-existent Plugin File',
                'explanation' => 'Plugin file does not exist',
                'files' => ['/path/to/dummy/file5.php', '/path/to/dummy/file6.php']
            ],
            'insufficientPermissions' => [
                'title' => 'Insufficient Permissions',
                'explanation' => 'Insufficient permissions to read file',
                'files' => ['/path/to/dummy/file7.php', '/path/to/dummy/file8.php']
            ],
            'aroundToBeforePlugin' => [
                'title' => 'Around to Before Plugin',
                'explanation' => 'Around plugin should be a before plugin',
                'files' => ['/path/to/dummy/file9.php', '/path/to/dummy/file10.php']
            ],
            'aroundToAfterPlugin' => [
                'title' => 'Around to After Plugin',
                'explanation' => 'Around plugin should be an after plugin',
                'files' => ['/path/to/dummy/file11.php', '/path/to/dummy/file12.php']
            ],
        ],
        'suggestions' => [],
    ];

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->fileFactory = $this->createMock(FileFactory::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->fileGetterFactory = $this->createMock(FileGetterFactory::class);
        $this->pdfWriter = $this->createMock(PDFWriter::class);
        $this->typeFactory = $this->createMock(TypeFactory::class);

        $processors = []; // Replace this with the actual array of processors if needed

        $this->audit = new Audit(
            $this->logger,
            $this->fileFactory,
            $this->filesystem,
            $this->fileGetterFactory,
            $this->pdfWriter,
            $this->typeFactory,
            $processors
        );
    }

    public function testRun()
    {
        $this->pdfWriter->expects($this->once())
            ->method('createdPDF')
            ->with([]);

        $this->audit->run();
    }
}