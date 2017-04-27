<?php
/**
 * Implementation of AddDocument controller
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
class SeedDMS_Controller_AddDocument extends SeedDMS_Controller_Common {

	protected function addAttachments($document) { /* {{{ */
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		/* Check for attachments */
		for ($attach_num=0;$attach_num<count($_FILES["attachment"]["tmp_name"]);$attach_num++){
			if ($_FILES["attachment"]["size"][$attach_num]==0) {
//				UI::exitError(getMLText("folder_title", array("foldername" => $folder->getName())),getMLText("uploading_zerosize"));
			}
			if ($_FILES['attachment']['error'][$attach_num]!=0){
//				UI::exitError(getMLText("folder_title", array("foldername" => $folder->getName())),getMLText("uploading_failed"));
			}

			$attachfiletmp = $_FILES["attachment"]["tmp_name"][$attach_num];
			$attachfiletype = $_FILES["attachment"]["type"][$attach_num];
			$attachfilename = $_FILES["attachment"]["name"][$attach_num];

			$fileType = ".".pathinfo($attachfilename, PATHINFO_EXTENSION);

			if($settings->_overrideMimeType) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$attachfiletype = finfo_file($finfo, $attachfiletmp);
			}

			$res = $document->addDocumentFile(utf8_basename($attachfilename), '', $user, $attachfiletmp, utf8_basename($attachfilename),$fileType, $attachfiletype);
		}
	} /* }}} */

	public function run() { /* {{{ */
		$name = $this->getParam('name');
		$comment = $this->getParam('comment');

		/* Call preAddDocument early, because it might need to modify some
		 * of the parameters.
		 */
		if(false === $this->callHook('preAddDocument', array('name'=>&$name, 'comment'=>&$comment))) {
			$this->errormsg = 'hook_preAddDocument_failed';
			return null;
		}

		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$documentsource = $this->params['documentsource'];
		$index = $this->params['index'];
		$indexconf = $this->params['indexconf'];
		$folder = $this->params['folder'];
		$expires = $this->getParam('expires');
		$keywords = $this->getParam('keywords');
		$cats = $this->getParam('categories');
		$owner = $this->getParam('owner');
		$userfiletmp = $this->getParam('userfiletmp');
		$userfilename = $this->getParam('userfilename');
		$filetype = $this->getParam('filetype');
		$userfiletype = $this->getParam('userfiletype');
		$sequence = $this->getParam('sequence');
		$reviewers = $this->getParam('reviewers');
		$approvers = $this->getParam('approvers');
		$reqversion = $this->getParam('reqversion');
		$version_comment = $this->getParam('versioncomment');
		$attributes = $this->getParam('attributes');
		$attributes_version = $this->getParam('attributesversion');
		$workflow = $this->getParam('workflow');
		$notificationgroups = $this->getParam('notificationgroups');
		$notificationusers = $this->getParam('notificationusers');
		$maxsizeforfulltext = $this->getParam('maxsizeforfulltext');
		$defaultaccessdocs = $this->getParam('defaultaccessdocs');

		$result = $this->callHook('addDocument');
		if($result === null) {
			$filesize = SeedDMS_Core_File::fileSize($userfiletmp);
			$res = $folder->addDocument($name, $comment, $expires, $owner, $keywords,
															$cats, $userfiletmp, utf8_basename($userfilename),
	                            $filetype, $userfiletype, $sequence,
	                            $reviewers, $approvers, $reqversion,
	                            $version_comment, $attributes, $attributes_version, $workflow);

			if (is_bool($res) && !$res) {
				$this->errormsg = "error_occured";
				return false;
			}

			$document = $res[0];

			/* Set access as specified in settings. */
			if($defaultaccessdocs) {
				if($defaultaccessdocs > 0 && $defaultaccessdocs < 4) {
					$document->setInheritAccess(0, true);
					$document->setDefaultAccess($defaultaccessdocs, true);
				}
			}

			if($index) {
				$idoc = new $indexconf['IndexedDocument']($dms, $document, isset($settings->_converters['fulltext']) ? $settings->_converters['fulltext'] : null, !($filesize < $settings->_maxSizeForFullText));
				if(!$this->callHook('preIndexDocument', $document, $idoc)) {
				}
				$index->addDocument($idoc);
			}

			/* Add a default notification for the owner of the document */
			if($settings->_enableOwnerNotification) {
				$res = $document->addNotify($user->getID(), true);
			}
			/* Check if additional notification shall be added */
			foreach($notificationusers as $notuser) {
				if($document->getAccessMode($user) >= M_READ)
					$res = $document->addNotify($notuser->getID(), true);
			}
			foreach($notificationgroups as $notgroup) {
				if($document->getGroupAccessMode($notgroup) >= M_READ)
					$res = $document->addNotify($notgroup->getID(), false);
			}

			if(!$this->callHook('postAddDocument', $document)) {
			}
			$result = $document;
		}

		return $result;
	} /* }}} */
}

