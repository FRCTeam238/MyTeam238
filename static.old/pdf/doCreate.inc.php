<?php
require PDF_DIR.'eng.php';
require PDF_DIR.'tcpdf.php';
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(SITE_SHORTNAME.' Report Builder');
$pdf->SetTitle(SITE_SHORTNAME.' Report Builder');
$pdf->SetSubject(SITE_SHORTNAME.' Report Builder');
$pdf->SetKeywords(SITE_SHORTNAME.', Tent Number');
$pdf->setPrintHeader(false);
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_RIGHT);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(false);
$pdf->SetMargins(14, 5, 14);
$pdf->setLanguageArray($l);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage($orientation, 'A4');
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->lastPage();
?>