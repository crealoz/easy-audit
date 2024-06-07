<?php

namespace Crealoz\EasyAudit\Service;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class PDFWriter
{
    private $pdf;
    
    private $currentPage;
    
    private int $y;

    public function __construct(
        private readonly \Magento\Framework\Filesystem $filesystem,
        private int $x = 50
    )
    {

    }

    /**
     * @throws \Zend_Pdf_Exception
     * @throws FileSystemException
     */
    public function createdPDF($results): void
    {
        $this->pdf = new \Zend_Pdf();
        $this->addPage();
        foreach ($results as $section => $result) {
            $this->writeTitle($section, 40);
            foreach ($result as $subsection => $subresult) {
                $this->writeSectionTitle($subsection);
                if ($subresult['hasErrors']) {
                    $this->manageSubResult($subresult);
                }
            }
        }
        //Get media directory in filesystem
        if (!$this->filesystem->getDirectoryRead(DirectoryList::MEDIA )->isExist('/crealoz')) {
            $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA )->create('/crealoz');
        }
        $fileName = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA )->getAbsolutePath('/crealoz/audit.pdf');
        $this->pdf->save($fileName);
    }

    private function manageSubResult($subresult): void
    {
        if (isset($subresult['errors'])){
            $this->setErrorStyle(14);
            $this->currentPage->drawText('Errors', 44, $this->y);
            foreach ($subresult['errors'] as $errorType => $errors) {
                if ($errorType === 'helpersInsteadOfViewModels') {
                    $this->manageHelperInsteadOfViewModel($errors);
                } else {
                    $this->manageSubsection($errors);
                }
            }
        }
        if (!empty($subresult['warnings'])) {
            $this->y -= 15;
            $this->setWarningStyle(14);
            $this->currentPage->drawText('Warning', 44, $this->y);
            foreach ($subresult['warnings'] as $warningType => $warnings) {
                $this->manageSubsection($warnings);
            }
        }
    }

    private function manageHelperInsteadOfViewModel($subresults): void
    {
        if ($subresults['files'] === []) {
            return;
        }
        $this->writeSubSectionIntro($subresults);
        $this->writeLine('Files:');
        foreach ($subresults['files'] as $file => $usages) {
            $this->writeLine('-' . $file . '(usages : ' . $usages['usageCount'] . ')');
            $this->x += 5;
            unset($usages['usageCount']);
            foreach ($usages as $template => $usage) {
                $this->setGeneralStyle(8);
                $this->currentPage->drawText('-' . $template . '(' . $usage . ')', $this->x, $this->y);
                $this->y -= 15;
                if ($this->y < 50) {
                    $this->addPage();
                }
            }
            $this->x -= 5;
        }
    }

    private function manageSubsection($subresults): void
    {
        if ($subresults['files'] === []) {
            return;
        }
        $this->writeSubSectionIntro($subresults);
        $this->writeLine('Files:');
        foreach ($subresults['files'] as $file) {
            if (is_array($file)) {
                $file = implode(', ', $file);
            }
            $this->writeLine('-' . $file);
        }
    }

    private function writeLine($text): void
    {
        $this->setGeneralStyle();
        // If line is too long, we split it
        if (strlen($text) > 100) {
            $wrappedText = wordwrap($text, 100, "--SPLIT--");
            $lines = explode("--SPLIT--", $wrappedText);
            foreach ($lines as $line) {
                $this->writeLine($line);
            }
            return;
        }

        $this->currentPage->drawText($text, $this->x, $this->y);
        $this->y -= 15;
        if ($this->y < 50) {
            $this->addPage();
        }
    }

    private function writeTitle($text, $x = null): void
    {
        $this->y -= 10;
        if ($this->y < 130) {
            $this->addPage();
        }
        $x = $x ?? $this->x;
        $this->setTitleStyle();
        $this->y -= 15;
        $this->currentPage->drawText(strtoupper($text), $x, $this->y);
        $this->y -= 30;
        $this->setGeneralStyle();
    }

    private function writeSubSectionIntro($subsection): void
    {
        if (isset($subsection['title'])) {
            $this->y -= 20;
            $this->setSubTitleStyle();
            $this->currentPage->drawText($subsection['title'], 48, $this->y);
        }
        if (isset($subsection['explanation'])) {
            $this->y -= 10;
            $this->writeLine($subsection['explanation']);
        }
    }

    private function writeSectionTitle($text): void
    {
        $this->setTitleStyle(15);
        $this->y -= 15;
        $this->currentPage->drawText(strtoupper($text), 43, $this->y);
        $this->y -= 20;
        if ($this->y < 50) {
            $this->addPage();
        }
        $this->setGeneralStyle();
    }

    private function addPage()
    {
        $this->currentPage = $this->pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->pdf->pages[] = $this->currentPage;
        $this->setGeneralStyle();
        $this->y = 850 - 50;
    }

    private function setGeneralStyle($size = 9)
    {
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0,0));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0,0,0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font,$size);
        $this->currentPage->setStyle($style);
    }

    private function setTitleStyle($size = 20)
    {
        $style = new \Zend_Pdf_Style();
        // Blue color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0,0.85));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0,0,0.85));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font,$size);
        $this->currentPage->setStyle($style);
    }

    private function setSubTitleStyle($size = 12)
    {
        $style = new \Zend_Pdf_Style();
        // Blue color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0.45,0.85));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0,0.45,0.85));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font,$size);
        $this->currentPage->setStyle($style);
    }

    private function setErrorStyle($size = 11)
    {
        $style = new \Zend_Pdf_Style();
        // Red color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0.85,0,0));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0.85,0,0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font,$size);
        $this->currentPage->setStyle($style);
    }

    private function setWarningStyle($size = 11)
    {
        $style = new \Zend_Pdf_Style();
        // Orange color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0.85,0.45,0));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0.85,0.45,0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font,$size);
        $this->currentPage->setStyle($style);
    }
}