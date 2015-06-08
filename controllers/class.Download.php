<?php
/**
 * Implementation of Download controller
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2013 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Class which does the busines logic for downloading a document
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2013 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Controller_Download extends SeedDMS_Controller_Common {

	public function run() {
		$dms = $this->params['dms'];
		$type = $this->params['type'];

		switch($type) {
			case "version":
				$content = $this->params['content'];

				if(!$this->callHook('version')) {
					header("Content-Transfer-Encoding: binary");
					header("Content-Length: " . filesize($dms->contentDir . $content->getPath() ));
					header("Content-Disposition: attachment; filename=\"" . $content->getOriginalFileName() . "\"");
					header("Content-Type: " . $content->getMimeType());
					header("Cache-Control: must-revalidate");

					readfile($dms->contentDir . $content->getPath());
				}
				break;
			case "file":
				$file = $this->params['file'];

				if(!$this->callHook('file')) {
					header("Content-Type: application/force-download; name=\"" . $file->getOriginalFileName() . "\"");
					header("Content-Transfer-Encoding: binary");
					header("Content-Length: " . filesize($dms->contentDir . $file->getPath() ));
					header("Content-Disposition: attachment; filename=\"" . $file->getOriginalFileName() . "\"");
					//header("Expires: 0");
					header("Content-Type: " . $file->getMimeType());
					//header("Cache-Control: no-cache, must-revalidate");
					header("Cache-Control: must-revalidate");
					//header("Pragma: no-cache");

					readfile($dms->contentDir . $file->getPath());
				}
				break;
		}
	}
}
