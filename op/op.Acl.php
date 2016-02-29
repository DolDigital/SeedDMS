<?php
//    MyDMS. Document Management System
//    Copyright (C) 2002-2005  Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//    Copyright (C) 2010 Matteo Lucarelli
//    Copyright (C) 2010-2012 Uwe Steinmann
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
include("../inc/inc.Utils.php");
include("../inc/inc.Language.php");
include("../inc/inc.Init.php");
include("../inc/inc.Extension.php");
include("../inc/inc.DBInit.php");
include("../inc/inc.ClassUI.php");
include("../inc/inc.ClassAcl.php");
include("../inc/inc.Authentication.php");

if (!$user->isAdmin()) {
	$result = array('type'=>'error', 'msg'=>getMLText("access_denied"));
	echo json_encode($result);
	exit;
}

if (isset($_GET["action"])) $action=$_GET["action"];
else $action=NULL;

if (isset($_GET["aroid"])) {
	if(!($aro = SeedDMS_Aro::getInstance((int) $_GET["aroid"], $dms))) {
		$result = array('type'=>'error', 'msg'=>'1'.getMLText("access_denied"));
		echo json_encode($result);
		exit;
	}
} else {
	$result = array('type'=>'error', 'msg'=>'2'.getMLText("access_denied"));
	echo json_encode($result);
	exit;
}

if (isset($_GET["acoid"])) {
	if(!($aco = SeedDMS_Aco::getInstance((int) $_GET["acoid"], $dms))) {
		$result = array('type'=>'error', 'msg'=>'3'.getMLText("access_denied"));
		echo json_encode($result);
		exit;
	}
} else {
	$result = array('type'=>'error', 'msg'=>'4'.getMLText("access_denied"));
	echo json_encode($result);
	exit;
}

switch($action) {
case "toggle_permission":
	$acl = new SeedDMS_Acl($dms);
	if($acl->toggle($aro, $aco))
		$result = array('type'=>'success', 'msg'=>'lasjfl');
	else
		$result = array('type'=>'error', 'msg'=>'lasjfl');
	header('Content-Type: application/json');
	echo json_encode($result);
	break;
case "add_permission":
	$acl = new SeedDMS_Acl($dms);
	if($acl->add($aro, $aco))
		$result = array('type'=>'success', 'msg'=>'lasjfl');
	else
		$result = array('type'=>'error', 'msg'=>'lasjfl');
	header('Content-Type: application/json');
	$result = array('type'=>'success', 'msg'=>'lasjfl');
	echo json_encode($result);
	break;
case "remove_permission":
	$acl = new SeedDMS_Acl($dms);
	if($acl->remove($aro, $aco))
		$result = array('type'=>'success', 'msg'=>'lasjfl');
	else
		$result = array('type'=>'error', 'msg'=>'lasjfl');
	header('Content-Type: application/json');
	$result = array('type'=>'success', 'msg'=>'lasjfl');
	echo json_encode($result);
	break;
}

