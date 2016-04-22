<?php
/**
 * Implementation of a download management.
 *
 * This class handles downloading of document lists.
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  2015 Uwe Steinmann
 * @version    Release: @package_version@
 */

require_once("PHPExcel.php");

/**
 * Class to represent an download manager
 *
 * This class provides some very basic methods to download document lists.
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  2015 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Download_Mgr {
	/**
	 * @var string $tmpdir directory where download archive is temp. created
	 * @access protected
	 */
	protected $tmpdir;

	/**
	 * @var array $items list of document content items
	 * @access protected
	 */
	protected $items;

	/**
	 * @var array $extracols list of arrays with extra columns per item
	 * @access protected
	 */
	protected $extracols;

	function __construct($tmpdir = '') {
		$this->tmpdir = $tmpdir;
		$this->items = array();
		$this->header = array('Dokumenten-Nr.', 'Dokumentenname', 'Dateiname', 'Status', 'Int. Version', 'Prüfer', 'Prüfdatum', 'Prüfkommentar', 'Prüfstatus', 'Freigeber', 'Freigabedatum', 'Freigabekommentar', 'Freigabestatus');
		$this->extracols = array();
		$this->extraheader = array();
	}

	public function addHeader($extraheader) { /* {{{ */
		$this->extraheader = $extraheader;
	} /* }}} */

	public function addItem($item, $extracols) { /* {{{ */
		$this->items[$item->getID()] = $item;
		$this->extracols[$item->getID()] = $extracols;
	} /* }}} */

	public function createToc($file) { /* {{{ */
		$items = $this->items;
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("SeedDMS")->setTitle("Metadata");
		$sheet = $objPHPExcel->setActiveSheetIndex(0);

		$i = 1;
		$col = 0;
		foreach($this->header as $h)
			$sheet->setCellValueByColumnAndRow($col++, $i, $h);
		foreach($this->extraheader as $h)
			$sheet->setCellValueByColumnAndRow($col++, $i, $h);
		$i++;
		foreach($items as $item) {
			$document = $item->getDocument();
			$dms = $document->_dms;
			$status = $item->getStatus();
			$reviewStatus = $item->getReviewStatus();
			$approvalStatus = $item->getApprovalStatus();

			$col = 0;
			$sheet->setCellValueByColumnAndRow($col++, $i, $document->getID());
			$sheet->setCellValueByColumnAndRow($col++, $i, $document->getName());
			$sheet->setCellValueByColumnAndRow($col++, $i, $document->getID()."-".$item->getOriginalFileName());
			$sheet->setCellValueByColumnAndRow($col++, $i, getOverallStatusText($status['status']));
			$sheet->setCellValueByColumnAndRow($col++, $i, $item->getVersion());
			$l = $i;
			$k = $i;
			if($reviewStatus) {
				foreach ($reviewStatus as $r) {
					switch ($r["type"]) {
						case 0: // Reviewer is an individual.
							$required = $dms->getUser($r["required"]);
							if (!is_object($required)) {
								$reqName = getMLText("unknown_user")." '".$r["required"]."'";
							} else {
								$reqName = htmlspecialchars($required->getFullName()." (".$required->getLogin().")");
							}
							break;
						case 1: // Reviewer is a group.
							$required = $dms->getGroup($r["required"]);
							if (!is_object($required)) {
								$reqName = getMLText("unknown_group")." '".$r["required"]."'";
							} else {
								$reqName = htmlspecialchars($required->getName());
							}
							break;
					}
					$tcol = $col;
					$sheet->setCellValueByColumnAndRow($tcol++, $l, $reqName);
					$sheet->setCellValueByColumnAndRow($tcol, $l, ($r['status']==1 || $r['status']==-1) ? PHPExcel_Shared_Date::PHPToExcel(makeTsFromLongDate($r['date'])) : "");
					$sheet->getStyleByColumnAndRow($tcol++, $l)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX22);
					$sheet->setCellValueByColumnAndRow($tcol++, $l, $r['comment']);
					$sheet->setCellValueByColumnAndRow($tcol++, $l, getReviewStatusText($r["status"]));
					$l++;
				}
				$l--;
			}
			$col += 4;
			if($approvalStatus) {
				foreach ($approvalStatus as $r) {
					switch ($r["type"]) {
						case 0: // Reviewer is an individual.
							$required = $dms->getUser($r["required"]);
							if (!is_object($required)) {
								$reqName = getMLText("unknown_user")." '".$r["required"]."'";
							} else {
								$reqName = htmlspecialchars($required->getFullName()." (".$required->getLogin().")");
							}
							break;
						case 1: // Reviewer is a group.
							$required = $dms->getGroup($r["required"]);
							if (!is_object($required)) {
								$reqName = getMLText("unknown_group")." '".$r["required"]."'";
							} else {
								$reqName = htmlspecialchars($required->getName());
							}
							break;
					}
					$tcol = $col;
					$sheet->setCellValueByColumnAndRow($tcol++, $k, $reqName);
					$sheet->setCellValueByColumnAndRow($tcol, $k, ($r['status']==1 || $r['status']==-1) ?PHPExcel_Shared_Date::PHPToExcel(makeTsFromLongDate($r['date'])) : "");
					$sheet->getStyleByColumnAndRow($tcol++, $k)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX22);
					$sheet->setCellValueByColumnAndRow($tcol++, $k, $r['comment']);
					$sheet->setCellValueByColumnAndRow($tcol++, $k, getApprovalStatusText($r["status"]));
					$k++;
				}
				$k--;
			}
			$col += 4;
			if(isset($this->extracols[$item->getID()]) && $this->extracols[$item->getID()]) {
				foreach($this->extracols[$item->getID()] as $column)
					$sheet->setCellValueByColumnAndRow($col++, $i, $column);
			}
			$i = max($l, $k);
			$i++;
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save($file);

		return true;
	} /* }}} */

	public function createArchive($filename) { /* {{{ */
		if(!$this->items) {
			return false;
		}

		$file = tempnam("/tmp", "export-list-");
		$this->createToc($file);

		$zip = new ZipArchive();
		$prefixdir = date('Y-m-d', time());

		if($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
			return false;
		}

		foreach($this->items as $item) {
			$document = $item->getDocument();
			$dms = $document->_dms;
			$zip->addFile($dms->contentDir.$item->getPath(), utf8_decode($prefixdir."/".$document->getID()."-".$item->getOriginalFileName()));
		}

		$zip->addFile($file, $prefixdir."/metadata.xls");
		$zip->close();
		unlink($file);
	} /* }}} */
}
