<?php
/**
 * Implementation of access restricitions
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */

require_once "inc.ClassAcl.php";

/**
 * Class to check certain access restrictions
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_AccessOperation {
	/**
	 * @var object $dms reference to dms
	 * @access protected
	 */
	private $dms;

	/**
	 * @var object $user user requesting the access
	 * @access protected
	 */
	private $user;

	/**
	 * @var object $settings SeedDMS Settings
	 * @access protected
	 */
	private $settings;

	function __construct($dms, $user, $settings) { /* {{{ */
		$this->dms = $dms;
		$this->user = $user;
		$this->settings = $settings;
	} /* }}} */

	/**
	 * Check if removal of version is allowed
	 *
	 * This check can only be done for documents. Removal of versions is
	 * only allowed if this is turned on in the settings and there are
	 * at least 2 versions avaiable. Everybody with write access on the
	 * document may delete versions. The admin may even delete a version
	 * even if is disallowed in the settings.
	 */
	function mayRemoveVersion($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$versions = $document->getContent();
			if ((($this->settings->_enableVersionDeletion && ($document->getAccessMode($this->user) == M_ALL)) || $this->user->isAdmin() ) && (count($versions) > 1)) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if document status may be overwritten
	 *
	 * This check can only be done for documents. Overwriting the document
	 * status is
	 * only allowed if this is turned on in the settings and the current
	 * status is either 'releaѕed' or 'obsoleted'.
	 * The admin may even modify the status
	 * even if is disallowed in the settings.
	 */
	function mayOverrideStatus($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$latestContent = $document->getLatestContent();
			$status = $latestContent->getStatus();
			if ((($this->settings->_enableVersionModification && ($document->getAccessMode($this->user) == M_ALL)) || $this->user->isAdmin()) && ($status["status"]==S_DRAFT || $status["status"]==S_RELEASED || $status["status"]==S_OBSOLETE)) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if reviewers/approvers may be edited
	 *
	 * This check can only be done for documents. Overwriting the document
	 * reviewers/approvers is only allowed if version modification is turned on
	 * in the settings and the document is in 'draft review' status.  The
	 * admin may even set reviewers/approvers if is disallowed in the
	 * settings.
	 */
	function maySetReviewersApprovers($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$latestContent = $document->getLatestContent();
			$status = $latestContent->getStatus();
			if ((($this->settings->_enableVersionModification && ($document->getAccessMode($this->user) == M_ALL)) || $this->user->isAdmin()) && ($status['status']==S_DRAFT || $status["status"]==S_DRAFT_REV || $status["status"]==S_DRAFT_APP && $this->settings->_workflowMode == 'traditional_only_approval')) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if recipients may be edited
	 *
	 * This check can only be done for documents. Setting the document
	 * recipients is only allowed if version modification is turned on
	 * in the settings.  The
	 * admin may even set recipients if is disallowed in the
	 * settings.
	 */
	function maySetRecipients($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$latestContent = $document->getLatestContent();
			$status = $latestContent->getStatus();
			if ((($this->settings->_enableVersionModification && ($document->getAccessMode($this->user) == M_ALL)) || $this->user->isAdmin()) && ($status["status"]==S_RELEASED)) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if revisors may be edited
	 *
	 * This check can only be done for documents. Setting the document
	 * revisors is only allowed if version modification is turned on
	 * in the settings.  The
	 * admin may even set revisors if is disallowed in the
	 * settings.
	 */
	function maySetRevisors($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$latestContent = $document->getLatestContent();
			$status = $latestContent->getStatus();
			if (($this->settings->_enableVersionModification && ($document->getAccessMode($this->user) == M_ALL)) || $this->user->isAdmin() /* && ($status["status"]==S_RELEASED || $status["status"]==S_IN_REVISION)*/) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if workflow may be edited
	 *
	 * This check can only be done for documents. Overwriting the document
	 * workflow is only allowed if version modification is turned on
	 * in the settings and the document is in it's initial status.  The
	 * admin may even set the workflow if is disallowed in the
	 * settings.
	 */
	function maySetWorkflow($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$latestContent = $document->getLatestContent();
			$workflow = $latestContent->getWorkflow();
			if ((($this->settings->_enableVersionModification && ($document->getAccessMode($this->user) == M_ALL)) || $this->user->isAdmin()) && (!$workflow || ($workflow->getInitState()->getID() == $latestContent->getWorkflowState()->getID()))) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if expiration date may be set
	 *
	 * This check can only be done for documents. Setting the documents
	 * expiration date is only allowed if the document has not been obsoleted.
	 */
	function maySetExpires($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$latestContent = $document->getLatestContent();
			$status = $latestContent->getStatus();
			if ((($document->getAccessMode($this->user) == M_ALL) || $this->user->isAdmin()) && ($status["status"]!=S_OBSOLETE)) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if comment may be edited
	 *
	 * This check can only be done for documents. Setting the documents
	 * comment date is only allowed if version modification is turned on in
	 * the settings and the document has not been obsoleted.
	 * The admin may set the comment even if is
	 * disallowed in the settings.
	 */
	function mayEditComment($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			if($document->isLocked()) {
				$lockingUser = $document->getLockingUser();
				if (($lockingUser->getID() != $this->user->getID()) && ($document->getAccessMode($this->user) != M_ALL)) {
					return false;
				}
			}
			$latestContent = $document->getLatestContent();
			$status = $latestContent->getStatus();
			if ((($this->settings->_enableVersionModification && ($document->getAccessMode($this->user) >= M_READWRITE)) || $this->user->isAdmin()) && ($status["status"]!=S_OBSOLETE)) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if attributes may be edited
	 *
	 * Setting the object attributes
	 * is only allowed if version modification is turned on in
	 * the settings and the document has not been obsoleted.
	 * The admin may set the comment even if is
	 * disallowed in the settings.
	 */
	function mayEditAttributes($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$latestContent = $document->getLatestContent();
			$status = $latestContent->getStatus();
			$workflow = $latestContent->getWorkflow();
			if ((($this->settings->_enableVersionModification && ($document->getAccessMode($this->user) >= M_READWRITE)) || $this->user->isAdmin()) && ($status["status"]==S_DRAFT_REV || ($workflow && $workflow->getInitState()->getID() == $latestContent->getWorkflowState()->getID()))) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if document content may be reviewed
	 *
	 * Reviewing a document content is only allowed if the document was not
	 * obsoleted. There are other requirements which are not taken into
	 * account here.
	 */
	function mayReview($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$latestContent = $document->getLatestContent();
			$status = $latestContent->getStatus();
			if ($status["status"]!=S_OBSOLETE) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if document content may be approved
	 *
	 * Approving a document content is only allowed if the document was not
	 * obsoleted and the document is not in review status.
	 * There are other requirements which are not taken into
	 * account here.
	 */
	function mayApprove($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$latestContent = $document->getLatestContent();
			$status = $latestContent->getStatus();
			if ($status["status"]!=S_OBSOLETE && $status["status"]!=S_DRAFT_REV && $status["status"]!=S_REJECTED) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if document content may be receipted
	 *
	 * Reviewing a document content is only allowed if the document was not
	 * obsoleted. There are other requirements which are not taken into
	 * account here.
	 */
	function mayReceipt($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$latestContent = $document->getLatestContent();
			$status = $latestContent->getStatus();
			if ($status["status"]!=S_OBSOLETE) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check if document content may be revised
	 *
	 * Revising a document content is only allowed if the document was not
	 * obsoleted. There are other requirements which are not taken into
	 * account here.
	 */
	function mayRevise($document) { /* {{{ */
		if(get_class($document) == $this->dms->getClassname('document')) {
			$latestContent = $document->getLatestContent();
			$status = $latestContent->getStatus();
			if ($status["status"]!=S_OBSOLETE) {
				return true;
			}
		}
		return false;
	} /* }}} */

	/**
	 * Check for access permission
	 *
	 * @param object $dms Instanz of dms
	 * @param object $role role of currently logged in user
	 * @param string $scope 'Views', 'Controllers'
	 * @param string $script Scriptname without 'out.' and '.php'
	 * @param string $get query parameters
	 * @return boolean true if access is allowed otherwise false
	 */
	function check_view_access($view, $get=array()) { /* {{{ */
		$scope = 'Views';
		$script = $view->getParam('class');
		$action = (isset($get['action']) && $get['action']) ? $get['action'] : 'show';
		$acl = new SeedDMS_Acl($this->dms);
		$aro = SeedDMS_Aro::getInstance($this->user->getRole(), $this->dms);
		$aco = SeedDMS_Aco::getInstance($scope.'/'.$script.'/'.$action, $this->dms);
		return $acl->check($aro, $aco);
	} /* }}} */
}
?>
