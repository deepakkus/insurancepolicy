<?php

Yii::import('application.vendors.tcpdf.*'); 

class ResPdfCreator extends TCPDF
{
	// Overload Footer method
    public function Footer() 
	{
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont(PDF_FONT_NAME_DATA, 'I', PDF_FONT_SIZE_DATA);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');     
    }
}

?>