<?php
//    MyDMS. Document Management System
//    Copyright (C) 2002-2005  Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//    Copyright (C) 2010-2016 Uwe Steinmann
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

/* Check if the form data comes from a trusted request */
if(!checkFormKey('removefolder')) {
	UI::exitError(getMLText("folder_title", array("foldername" => getMLText("invalid_request_token"))),getMLText("invalid_request_token"));
}

if (!isset($_POST["folderid"]) || !is_numeric($_POST["folderid"]) || intval($_POST["folderid"])<1) {
	UI::exitError(getMLText("folder_title", array("foldername" => getMLText("invalid_folder_id"))),getMLText("invalid_folder_id"));
}
$folderid = $_POST["folderid"];
$folder = $dms->getFolder($folderid);

if (!is_object($folder)) {
	UI::exitError(getMLText("folder_title", array("foldername" => getMLText("invalid_folder_id"))),getMLText("invalid_folder_id"));
}

if ($folderid == $settings->_rootFolderID || !$folder->getParent()) {
	UI::exitError(getMLText("folder_title", array("foldername" => $folder->getName())),getMLText("cannot_rm_root"));
}

if ($folder->getAccessMode($user) < M_ALL) {
	UI::exitError(getMLText("folder_title", array("foldername" => $folder->getName())),getMLText("access_denied"));
}

$parent=$folder->getParent();

/* Register a callback which removes each document from the fulltext index
 * The callback must return true other the removal will be canceled.
 */
if($settings->_enableFullSearch) {
	function removeFromIndex($arr, $document) {
		$index = $arr[0];
		$indexconf = $arr[1];
		$lucenesearch = new $indexconf['Search']($index);
		if($hit = $lucenesearch->getDocument($document->getID())) {
			$index->delete($hit->id);
			$index->commit();
		}
		return true;
	}
	$index = $indexconf['Indexer']::open($settings->_luceneDir);
	if($index)
		$dms->setCallback('onPreRemoveDocument', 'removeFromIndex', array($index, $indexconf));
}

function removePreviews($arr, $document) {
	$previewer = $arr[0];

	$previewer->deleteDocumentPreviews($document);
	return true;
}
require_once("SeedDMS/Preview.php");
$previewer = new SeedDMS_Preview_Previewer($settings->_cacheDir);
$dms->addCallback('onPreRemoveDocument', 'removePreviews', array($previewer));

$nl =	$folder->getNotifyList();
$foldername = $folder->getName();
if ($folder->remove()) {
	// Send notification to subscribers.
	if ($notifier) {
		$subject = "folder_deleted_email_subject";
		$message = "folder_deleted_email_body";
		$params = array();
		$params['name'] = $foldername;
		$params['folder_path'] = $parent->getFolderPathPlain();
		$params['username'] = $user->getFullName();
		$params['sitename'] = $settings->_siteName;
		$params['http_root'] = $settings->_httpRoot;
		$params['url'] = "http".((isset($_SERVER['HTTPS']) && (strcmp($_SERVER['HTTPS'],'off')!=0)) ? "s" : "")."://".$_SERVER['HTTP_HOST'].$settings->_httpRoot."out/out.ViewFolder.php?folderid=".$parent->getID();
		$notifier->toList($user, $nl["users"], $subject, $message, $params);
		foreach ($nl["groups"] as $grp) {
			$notifier->toGroup($user, $grp, $subject, $message, $params);
		}
	}
} else {
	UI::exitError(getMLText("folder_title", array("foldername" => $folder->getName())),getMLText("error_remove_folder"));
}

add_log_line("?folderid=".$folderid."&name=".$foldername);

header("Location:../out/out.ViewFolder.php?folderid=".$parent->getID()."&showtree=".$_POST["showtree"]);

?>
