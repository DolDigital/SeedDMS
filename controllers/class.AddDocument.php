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

	public function run() {
		/* Call preAddDocument early, because it might need to modify some
		 * of the parameters.
		 */
		if(false === $this->callHook('preAddDocument')) {
			$this->errormsg = 'hook_preAddDocument_failed';
			return null;
		}

		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$documentsource = $this->params['documentsource'];
		$index = $this->params['index'];
		$folder = $this->params['folder'];
		$name = $this->getParam('name');
		$comment = $this->getParam('comment');
		$expires = $this->getParam('expires');
		$keywords = $this->getParam('keywords');
		$cats = $this->getParam('categories');
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
		$initialdocumentstatus = $this->getParam('initialdocumentstatus');

		$result = $this->callHook('addDocument');
		if($result === null) {
			$res = $folder->addDocument($name, $comment, $expires, $user, $keywords,
															$cats, $userfiletmp, basename($userfilename),
	                            $filetype, $userfiletype, $sequence,
	                            $reviewers, $approvers, $reqversion,
	                            $version_comment, $attributes, $attributes_version, $workflow, $initialdocumentstatus);

			if (is_bool($res) && !$res) {
				$this->errormsg = "error_occured";
				return false;
			}

			$document = $res[0];

			if($index) {
				$index->addDocument(new SeedDMS_Lucene_IndexedDocument($dms, $document, isset($settings->_converters['fulltext']) ? $settings->_converters['fulltext'] : null, true));
			}

			/* Add a default notification for the owner of the document */
			if($settings->_enableOwnerNotification) {
				$res = $document->addNotify($user->getID(), true);
			}
			/* Check if additional notification shall be added */
			foreach($notificationusers as $notuser) {
					$res = $document->addNotify($notuser->getID(), true);
			}
			foreach($notificationgroups as $notgroup) {
				$res = $document->addNotify($notgroup->getID(), false);
			}

			if(!$this->callHook('postAddDocument', $document)) {
			}
			$result = $document;
		}

		return $result;
	}
}

