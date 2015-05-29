<?php
/**
 * Implementation of ApproveDocument controller
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
class SeedDMS_Controller_ApproveDocument extends SeedDMS_Controller_Common {

	public function run() {
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$document = $this->params['document'];
		$content = $this->params['content'];
		$approvalstatus = $this->params['approvalstatus'];
		$approvaltype = $this->params['approvaltype'];
		$group = $this->params['group'];
		$comment = $this->params['comment'];

		/* Get the document id and name before removing the document */
		$docname = $document->getName();
		$documentid = $document->getID();

		if(!$this->callHook('preApproveDocument', $document)) {
		}

		$result = $this->callHook('approveDocument', $document);
		if($result === null) {
			if ($approvaltype == "ind") {
				if(0 > $content->setApprovalByInd($user, $user, $approvalstatus, $comment)) {
					$this->error = 1;
					$this->errormsg = "approval_update_failed";
					return false;
				}
			} elseif ($approvaltype == "grp") {
				if(0 > $content->setApprovalByGrp($group, $user, $approvalstatus, $comment)) {
					$this->error = 1;
					$this->errormsg = "approval_update_failed";
					return false;
				}
			}
			if(!$this->callHook('postApproveDocument', $document)) {
			}
		}

		return true;
	}
}

