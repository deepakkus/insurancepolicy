<?php

Yii::import('application.vendors.tcpdf.*'); 

class FSPdfCreator extends TCPDF {

    public $custom_footer_text = 'For inquiries regarding this report or the materials/suggestions herin, please feel free to contact:<br><b> Wildfire Defense Systems, Inc. (877) 323-4730  info@wildfire-defense.com</b>';
	// Page footer
    public function Footer() 
	{	
		if ($this->getPage() > 1) 
		{
			// Position at 15 mm from bottom
			$this->SetY(-15);
			// Set font
			$this->SetFont('helvetica', '', 10);
			// Page number
			//$this->Cell(0, 10, 'For inquiries regarding this report or the materials/suggestions herin, please feel free to contact:\n Wildfire Defense Systems, Inc. (877) 323-4730', 0, false, 'C', 0, '', 0, false, 'T', 'M');
			$html = $this->custom_footer_text;
			$this->writeHTML($html, true, false, true, false, 'C');
		}
    }
}

?>