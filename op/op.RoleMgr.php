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
include("../inc/inc.Authentication.php");
include("../inc/inc.ClassPasswordStrength.php");

if (!$user->isAdmin()) {
	UI::exitError(getMLText("admin_tools"),getMLText("access_denied"));
}

if (isset($_POST["action"])) $action=$_POST["action"];
else $action=NULL;

// add new role ---------------------------------------------------------
if ($action == "addrole") {
	
	/* Check if the form data comes for a trusted request */
	if(!checkFormKey('addrole')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	$name    = $_POST["name"];
	$role    = preg_replace('/[^0-2]+/', '', $_POST["role"]);

	if (is_object($dms->getRoleByName($name))) {
		UI::exitError(getMLText("admin_tools"),getMLText("role_exists"));
	}

	$newRole = $dms->addRole($name, $role);
	if ($newRole) {
	}
	else UI::exitError(getMLText("admin_tools"),getMLText("access_denied"));
	
	$roleid=$newRole->getID();
	
	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_add_role')));

	add_log_line(".php&action=addrole&name=".$name);
}

// delete role ------------------------------------------------------------
else if ($action == "removerole") {

	/* Check if the form data comes for a trusted request */
	if(!checkFormKey('removerole')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	if (isset($_POST["roleid"])) {
		$roleid = $_POST["roleid"];
	}

	if (!isset($roleid) || !is_numeric($roleid) || intval($roleid)<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_role_id"));
	}

	$roleToRemove = $dms->getRole($roleid);
	if (!is_object($roleToRemove)) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_role_id"));
	}

	if (!$roleToRemove->remove()) {
		UI::exitError(getMLText("admin_tools"),getMLText("error_occured"));
	}
		
	add_log_line(".php&action=removerole&roleid=".$roleid);
	
	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_rm_role')));
	$roleid=-1;
}

// modify role ------------------------------------------------------------
else if ($action == "editrole") {

	/* Check if the form data comes for a trusted request */
	if(!checkFormKey('editrole')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	if (!isset($_POST["roleid"]) || !is_numeric($_POST["roleid"]) || intval($_POST["roleid"])<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_role_id"));
	}
	
	$roleid=$_POST["roleid"];
	$editedRole = $dms->getRole($roleid);
	
	if (!is_object($editedRole)) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_role_id"));
	}

	$name    = $_POST["name"];
	$role    = preg_replace('/[^0-2]+/', '', $_POST["role"]);
	$noaccess = isset($_POST['noaccess']) ? $_POST['noaccess'] : null;
	
	if ($editedRole->getName() != $name)
		$editedRole->setName($name);
	if ($editedRole->getRole() != $role)
		$editedRole->setRole($role);
	$editedRole->setNoAccess($noaccess);

	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_edit_role')));
	add_log_line(".php&action=editrole&roleid=".$roleid);
}
else UI::exitError(getMLText("admin_tools"),getMLText("unknown_command"));

header("Location:../out/out.RoleMgr.php?roleid=".$roleid);

?>
