<?php

namespace Crealoz\EasyAudit\Service;

use Magento\Framework\App\Filesystem\DirectoryList;

class PDFWriter
{
    private $pdf;
    
    private $currentPage;
    
    private $y;

    public function __construct(
        private readonly \Magento\Framework\Filesystem $filesystem
    )
    {

    }

    public function createdPDF($results)
    {
        $this->pdf = new \Zend_Pdf();
        $this->addPage();
        $x = 30;
        foreach ($results as $section => $result) {
            $this->writeTitle($section, $x);
            foreach ($result as $subsection => $subresult) {
                if ($subresult['hasErrors']) {
                    $this->writeLine('Errors', $x);
                    foreach ($subresult['errors'] as $errorType => $errors) {
                        $this->manageSubsection($errors);
                    }
                    $this->writeLine('Warnings', $x);
                    foreach ($subresult['warnings'] as $warningType => $warnings) {
                        $this->manageSubsection($warnings);
                    }
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

    private function manageSubsection($subresults) {
        if ($subresults['files'] === []) {
            return;
        }
        $this->writeSubSectionIntro($subresults);
        $this->writeLine('Files:', 30);
        foreach ($subresults['files'] as $file) {
            if (is_array($file)) {
                $file = implode(', ', $file);
            }
            $this->writeLine($file, 30);
        }
    }

    private function writeLine($text, $x)
    {
        $this->setGeneralStyle();
        // If line is too long, we split it
        if (strlen($text) > 100) {
            $wrappedText = wordwrap($text, 100, "--SPLIT--");
            $lines = explode("--SPLIT--", $wrappedText);
            foreach ($lines as $line) {
                $this->writeLine($line, $x);
            }
            return;
        }

        $this->currentPage->drawText($text, $x, $this->y);
        $this->y -= 20;
        if ($this->y < 50) {
            $this->addPage();
        }
    }

    private function writeTitle($text, $x)
    {
        $this->setTitleStyle();
        $this->currentPage->drawText($text, $x, $this->y);
        $this->y -= 40;
        if ($this->y < 50) {
            $this->addPage();
        }
        $this->setGeneralStyle();
    }

    private function writeSubSectionIntro($subsection)
    {
        if (isset($subsection['title'])) {
            $this->setSubTitleStyle();
            $this->currentPage->drawText($subsection['title'], 30, $this->y);
        }
        if (isset($subsection['explanation'])) {
            $this->y -= 20;
            $this->writeLine($subsection['explanation'], 30);
        }

    }

    private function addPage()
    {
        $this->currentPage = $this->pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->pdf->pages[] = $this->currentPage;
        $this->setGeneralStyle();
        $this->y = 850 - 100;
    }

    private function setGeneralStyle()
    {
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0,0));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0,0,0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font,12);
        $this->currentPage->setStyle($style);
    }

    private function setTitleStyle()
    {
        $style = new \Zend_Pdf_Style();
        // Blue color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0,0.85));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0,0,0.85));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font,20);
        $this->currentPage->setStyle($style);
    }

    private function setSubTitleStyle()
    {
        $style = new \Zend_Pdf_Style();
        // Blue color
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0.15,0.85));
        $style->setFillColor(new \Zend_Pdf_Color_Rgb(0,0.15,0.85));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font,16);
        $this->currentPage->setStyle($style);
    }
}