<?php
//    MyDMS. Document Management System
//    Copyright (C) 2002-2005  Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//
//    This program is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program; if not, write to the Free Software
//    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

include("../inc/inc.Settings.php");
include("../inc/inc.LogInit.php");
include("../inc/inc.DBInit.php");
include("../inc/inc.Language.php");
include("../inc/inc.ClassUI.php");
include("../inc/inc.Authentication.php");

/* Check if the form data comes for a trusted request */
if(!checkFormKey('removedocument')) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_request_token"))),getMLText("invalid_request_token"));
}

if (!isset($_POST["documentid"]) || !is_numeric($_POST["documentid"]) || intval($_POST["documentid"])<1) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("invalid_doc_id"));
}
$documentid = $_POST["documentid"];
$document = $dms->getDocument($documentid);

if (!is_object($document)) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("invalid_doc_id"));
}

if ($document->getAccessMode($user) < M_ALL) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("access_denied"));
}

if($document->isLocked()) {
	$lockingUser = $document->getLockingUser();
	if (($lockingUser->getID() != $user->getID()) && ($document->getAccessMode($user) != M_ALL)) {
		UI::exitError(getMLText("document_title", array("documentname" => htmlspecialchars($document->getName()))),getMLText("lock_message", array("email" => $lockingUser->getEmail(), "username" => htmlspecialchars($lockingUser->getFullName()))));
	}
}

$folder = $document->getFolder();

/* Get the notify list before removing the document */
$dnl =	$document->getNotifyList();
$fnl =	$folder->getNotifyList();
$nl = array(
	'users'=>array_merge($dnl['users'], $fnl['users']),
	'groups'=>array_merge($dnl['groups'], $fnl['groups'])
);
$docname = $document->getName();
if (!$document->remove()) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("error_occured"));
} else {

	/* Remove the document from the fulltext index */
	if($settings->_enableFullSearch) {
		$index = $indexconf['Indexer']::open($settings->_luceneDir);
		if($index) {
			$lucenesearch = new $indexconf['Search']($index);
			if($hit = $lucenesearch->getDocument($documentid)) {
				$index->delete($hit->id);
				$index->commit();
			}
		}
	}

	if ($notifier){
		$subject = "document_deleted_email_subject";
		$message = "document_deleted_email_body";
		$params = array();
		$params['name'] = $docname;
		$params['folder_path'] = $folder->getFolderPathPlain();
		$params['username'] = $user->getFullName();
		$params['sitename'] = $settings->_siteName;
		$params['http_root'] = $settings->_httpRoot;
		$notifier->toList($user, $nl["users"], $subject, $message, $params);
		foreach ($nl["groups"] as $grp) {
			$notifier->toGroup($user, $grp, $subject, $message, $params);
		}
	}

	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_rm_document')));
}

add_log_line("?documentid=".$documentid);

header("Location:../out/out.ViewFolder.php?folderid=".$folder->getID());

?>
