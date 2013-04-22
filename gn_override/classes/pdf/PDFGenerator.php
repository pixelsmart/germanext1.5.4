<?php
class PDFGenerator extends PDFGeneratorCore
{
	public function writePage() {
		$this->SetHeaderMargin(5);
		$this->SetFooterMargin(28);
		$this->setMargins(10, 40, 10);
		$this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

		$this->AddPage();

		$this->writeHTML($this->content, true, false, true, false, '');
	}
}
