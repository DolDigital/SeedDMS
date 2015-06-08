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

	function __construct($tmpdir = '') {
		$this->tmpdir = $tmpdir;
		$this->items = array();
	}

	public function addItem($item) { /* {{{ */
		$this->items[$item->getID()] = $item;
	} /* }}} */

	public function createArchive($filename) { /* {{{ */
		if(!$this->items) {
			return false;
		}

		$zip = new ZipArchive();
		$prefixdir = date('Y-m-d', time());

		if($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
			return false;
		}

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("MMK GmbH, Hagen, Germany")->setTitle("Metadata");
		$sheet = $objPHPExcel->setActiveSheetIndex(0);

		$i = 1;
		$sheet->setCellValueByColumnAndRow(0, $i, 'Dokumenten-Nr.');
		$sheet->setCellValueByColumnAndRow(1, $i, 'Dateiname');
		$sheet->setCellValueByColumnAndRow(2, $i, 'Status');
		$sheet->setCellValueByColumnAndRow(3, $i, 'Int. Version');
		$sheet->setCellValueByColumnAndRow(4, $i, 'Prüfer');
		$sheet->setCellValueByColumnAndRow(5, $i, 'Prüfdatum');
		$sheet->setCellValueByColumnAndRow(6, $i, 'Prüfkommentar');
		$sheet->setCellValueByColumnAndRow(7, $i, 'Prüfstatus');
		$sheet->setCellValueByColumnAndRow(8, $i, 'Freigeber');
		$sheet->setCellValueByColumnAndRow(9, $i, 'Freigabedatum');
		$sheet->setCellValueByColumnAndRow(10, $i, 'Freigabekommentar');
		$sheet->setCellValueByColumnAndRow(11, $i, 'Freigabestatus');
		$i++;
		foreach($this->items as $item) {
			$document = $item->getDocument();
			$dms = $document->_dms;
			$status = $item->getStatus();
			$reviewStatus = $item->getReviewStatus();
			$approvalStatus = $item->getApprovalStatus();

			$zip->addFile($dms->contentDir.$item->getPath(), utf8_decode($prefixdir."/".$document->getID()."-".$item->getOriginalFileName()));
			$sheet->setCellValueByColumnAndRow(0, $i, $document->getID());
			$sheet->setCellValueByColumnAndRow(1, $i, $document->getID()."-".$item->getOriginalFileName());
			$sheet->setCellValueByColumnAndRow(2, $i, getOverallStatusText($status['status']));
			$sheet->setCellValueByColumnAndRow(3, $i, $item->getVersion());
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
					$sheet->setCellValueByColumnAndRow(4, $l, $reqName);
					$sheet->setCellValueByColumnAndRow(5, $l, $r['date']);
					$sheet->setCellValueByColumnAndRow(6, $l, $r['comment']);
					$sheet->setCellValueByColumnAndRow(7, $l, getReviewStatusText($r["status"]));
					$l++;
				}
				$l--;
			}
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
					$sheet->setCellValueByColumnAndRow(8, $k, $reqName);
					$sheet->setCellValueByColumnAndRow(9, $k, $r['date']);
					$sheet->setCellValueByColumnAndRow(10, $k, $r['comment']);
					$sheet->setCellValueByColumnAndRow(11, $k, getReviewStatusText($r["status"]));
					$k++;
				}
				$k--;
			}
			$i = max($l, $k);
			$i++;
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$file = tempnam("/tmp", "export-list-");
		$objWriter->save($file);
		$zip->addFile($file, $prefixdir."/metadata.xls");
		$zip->close();
		unlink($file);
	} /* }}} */
}
