<?php
/**
 * Implementation of ReviseDocument controller
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
class SeedDMS_Controller_ReviseDocument extends SeedDMS_Controller_Common {

	public function run() {
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$document = $this->params['document'];
		$content = $this->params['content'];
		$revisionstatus = $this->params['revisionstatus'];
		$revisiontype = $this->params['revisiontype'];
		$group = $this->params['group'];
		$comment = $this->params['comment'];

		/* Get the document id and name before removing the document */
		$docname = $document->getName();
		$documentid = $document->getID();

		if(!$this->callHook('preReviseDocument', $content)) {
		}

		$result = $this->callHook('reviseDocument', $content);
		if($result === null) {

			if ($revisiontype == "ind") {
				if(0 > $content->setRevision($user, $user, $revisionstatus, $comment)) {
					$this->error = 1;
					$this->errormsg = "revision_update_failed";
					return false;
				}
			} elseif ($revisiontype == "grp") {
				if(0 > $content->setRevision($group, $user, $revisionstatus, $comment)) {
					$this->error = 1;
					$this->errormsg = "revision_update_failed";
					return false;
				}
			}
		}

		/* Check to see if the overall status for the document version needs to be
		 * updated.
		 */
		$result = $this->callHook('reviseUpdateDocumentStatus', $content);
		if($result === null) {
			if ($revisionstatus == -1){
				if($content->setStatus(S_REJECTED,$comment,$user)) {
					$this->error = 1;
					$this->errormsg = "revision_update_failed";
					return false;
				}
			} else {
				$docRevisionStatus = $content->getRevisionStatus();
				if (is_bool($docRevisionStatus) && !$docRevisionStatus) {
					$this->error = 1;
					$this->errormsg = "cannot_retrieve_revision_snapshot";
					return false;
				}
				$revisionCT = 0;
				$revisionTotal = 0;
				foreach ($docRevisionStatus as $drstat) {
					if ($drstat["status"] == 1) {
						$revisionCT++;
					}
					if ($drstat["status"] != -2) {
						$revisionTotal++;
					}
				}
				// If all revisions have been received and there are no rejections,
				// then release the document otherwise put it back into revision workflow
				if ($revisionCT == $revisionTotal) {
					$newStatus=S_RELEASED;
					if ($content->finishRevision($user, $newStatus, '', getMLText("automatic_status_update"))) {
					}
				} else {
					$newStatus=S_IN_REVISION;
					if($content->setStatus($newStatus,$comment,$user)) {
						$this->error = 1;
						$this->errormsg = "revision_update_failed";
						return false;
					}
				}
			}
		}

		if(!$this->callHook('postReviseDocument', $content)) {
		}

		return true;
	}
}

