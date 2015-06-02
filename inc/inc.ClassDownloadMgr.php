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

		foreach($this->items as $item) {
			$document = $item->getDocument();
			$dms = $document->_dms;

			$zip->addFile($dms->contentDir.$item->getPath(), $prefixdir."/".$document->getID()."-".$item->getOriginalFileName());
		}
		$zip->close();
	} /* }}} */
}
