<?php
$phpExcelPath = 'e:\eventregis\protected\extensions\phpexcel\Classes';
require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');

$objPHPExcel = new PHPExcel();

$objPHPExcel->getProperties()->setCreator("System")
					 ->setLastModifiedBy("System")
					 ->setTitle("Mau import nguoi tham du")
					 ->setSubject("Mau import nguoi tham du");
					 
$sheet = $objPHPExcel->setActiveSheetIndex(0);

// Header
$headers = array('Họ và tên (*)', 'Phòng ban', 'Chức danh (*)', 'Vai trò (cách nhau bởi dấu phẩy)', 'Ngày vào làm (dd/mm/yyyy) (*)', 'Ghi chú');
$col = 'A';
foreach ($headers as $header) {
	$sheet->setCellValue($col . '1', $header);
	$sheet->getStyle($col . '1')->getFont()->setBold(true);
	$col++;
}

// Sample data
$sheet->setCellValue('A2', 'Nguyễn Văn A');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
