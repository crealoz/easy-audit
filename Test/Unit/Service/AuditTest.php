<?php

use PHPUnit\Framework\TestCase;
use Crealoz\EasyAudit\Service\Audit;
use Psr\Log\LoggerInterface;
use Magento\MediaStorage\Model\File\Storage\FileFactory;
use Magento\Framework\Filesystem;
use Crealoz\EasyAudit\Service\FileSystem\DiXmlGetter;
use Crealoz\EasyAudit\Service\FileSystem\LayoutXmlGetter;
use Crealoz\EasyAudit\Service\FileSystem\HelpersGetter;
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
    private $diXmlGetter;
    private $layoutXmlGetter;
    private $helpersGetter;
    private $pdfWriter;

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
        $this->diXmlGetter = $this->createMock(DiXmlGetter::class);
        $this->layoutXmlGetter = $this->createMock(LayoutXmlGetter::class);
        $this->helpersGetter = $this->createMock(HelpersGetter::class);
        $this->pdfWriter = $this->createMock(PDFWriter::class);

        $this->audit = new Audit(
            $this->logger,
            $this->fileFactory,
            $this->filesystem,
            $this->diXmlGetter,
            $this->layoutXmlGetter,
            $this->helpersGetter,
            $this->pdfWriter,
            []
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