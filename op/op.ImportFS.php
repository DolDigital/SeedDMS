<?php
include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
include("../inc/inc.DBInit.php");
include("../inc/inc.Language.php");
include("../inc/inc.ClassUI.php");
include("../inc/inc.ClassAccessOperation.php");
include("../inc/inc.Authentication.php");

if (!isset($_GET["targetid"]) || !is_numeric($_GET["targetid"]) || $_GET["targetid"]<1) {
	UI::exitError(getMLText("admin_tools"),getMLText("invalid_target_folder"));
}
$targetid = $_GET["targetid"];
$folder = $dms->getFolder($targetid);
if (!is_object($folder)) {
	echo "Could not find specified folder\n";
	exit(1);
}

if ($folder->getAccessMode($user) < M_READWRITE) {
	UI::exitError(getMLText("admin_tools"),getMLText("access_denied"));
}

if (empty($_GET["dropfolderfileform1"])) {
	UI::exitError(getMLText("admin_tools"),getMLText("invalid_target_folder"));
}
$dirname = $settings->_dropFolderDir.'/'.$user->getLogin()."/".$_GET["dropfolderfileform1"];
if(!is_dir($dirname)) {
	UI::exitError(getMLText("admin_tools"),getMLText("invalid_target_folder"));
}

function import_folder($dirname, $folder) { /* {{{ */
	global $user;

	$d = dir($dirname);
	$sequence = 1;
	while(false !== ($entry = $d->read())) {
		$path = $dirname.'/'.$entry;
		if($entry != '.' && $entry != '..' && $entry != '.svn') {
			if(is_file($path)) {
				$name = basename($path);
				$filetmp = $path;

				$reviewers = array();
				$approvers = array();
				$comment = '';
				$version_comment = '';
				$reqversion = 1;
				$expires = false;
				$keywords = '';
				$categories = array();

				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mimetype = finfo_file($finfo, $path);
				$lastDotIndex = strrpos($path, ".");
				if (is_bool($lastDotIndex) && !$lastDotIndex) $filetype = ".";
				else $filetype = substr($path, $lastDotIndex);

				echo $mimetype." - ".$filetype." - ".$path."\n";
				$res = $folder->addDocument($name, $comment, $expires, $user, $keywords,
																		$categories, $filetmp, $name,
																		$filetype, $mimetype, $sequence, $reviewers,
																		$approvers, $reqversion, $version_comment);

				if (is_bool($res) && !$res) {
					echo "Could not add document to folder\n";
					exit(1);
				}
				set_time_limit(1200);
			} elseif(is_dir($path)) {
				$name = basename($path);
				$newfolder = $folder->addSubFolder($name, '', $user, $sequence);
				import_folder($path, $newfolder);
			}
			$sequence++;
		}
	}
} /* }}} */

header("Content-Type: text/plain");
import_folder($dirname, $folder);

