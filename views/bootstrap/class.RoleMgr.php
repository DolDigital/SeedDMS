<?php
/**
 * Implementation of RoleMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
require_once("class.Bootstrap.php");

/**
 * Class which outputs the html page for RoleMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_RoleMgr extends SeedDMS_Bootstrap_Style {

	function js() { /* {{{ */
		$selrole = $this->params['selrole'];

		header('Content-Type: application/javascript');
?>
function checkForm()
{
	msg = new Array();

	if($("#name").val() == "") msg.push("<?php printMLText("js_no_name");?>");
	if (msg != "") {
  	noty({
  		text: msg.join('<br />'),
  		type: 'error',
      dismissQueue: true,
  		layout: 'topRight',
  		theme: 'defaultTheme',
			_timeout: 1500,
  	});
		return false;
	}
	else
		return true;
}

$(document).ready( function() {
	$('body').on('submit', '#form', function(ev){
		if(checkForm()) return;
		event.preventDefault();
	});
	$( "#selector" ).change(function() {
		$('div.ajax').trigger('update', {roleid: $(this).val()});
	});
});
<?php
	} /* }}} */

	function info() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$selrole = $this->params['selrole'];
		$settings = $this->params['settings'];

		if($selrole) {
			$this->contentHeading(getMLText("role_info"));
			$users = $selrole->getUsers();
			if($users) {
				echo "<table class=\"table table-condensed\"><thead><tr><th>".getMLText('name')."</th><th></th></tr></thead><tbody>\n";
				foreach($users as $currUser) {
					echo "<tr>";
					echo "<td>";
					echo htmlspecialchars($currUser->getFullName())." (".htmlspecialchars($currUser->getLogin()).")";
					echo "<br /><a href=\"mailto:".$currUser->getEmail()."\">".htmlspecialchars($currUser->getEmail())."</a>";
					if($currUser->getComment())
						echo "<br /><small>".htmlspecialchars($currUser->getComment())."</small>";
					echo "</td>";
					echo "<td>";
					if($this->check_access(array('UsrMgr', 'RemoveUser'))) {
						echo "<div class=\"list-action\">";
						echo $this->html_link('UsrMgr', array('userid'=>$currUser->getID()), array(), '<i class="icon-edit"></i>', false);
						echo $this->html_link('RemoveUser', array('userid'=>$currUser->getID()), array(), '<i class="icon-remove"></i>', false);
						echo "</div>";
					}
					echo "</td>";
					echo "</tr>";
				}
				echo "</tbody></table>";
			}
		}
	} /* }}} */

	function form() { /* {{{ */
		$selrole = $this->params['selrole'];

		$this->showRoleForm($selrole);
	} /* }}} */

	function showRoleForm($currRole) { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$accessop = $this->params['accessobject'];
		$roles = $this->params['allroles'];

		if($currRole && !$currRole->isUsed() && $accessop->check_controller_access('RoleMgr', array('action'=>'removerole'))) {
?>
			<form style="display: inline-block;" method="post" action="../op/op.RoleMgr.php" >
				<?php echo createHiddenFieldWithKey('removerole'); ?>
				<input type="hidden" name="roleid" value="<?php echo $currRole->getID()?>">
				<input type="hidden" name="action" value="removerole">
				<button type="submit" class="btn"><i class="icon-remove"></i> <?php echo getMLText("rm_role")?></button>
			</form>
<?php
		}
?>
	<form action="../op/op.RoleMgr.php" method="post" enctype="multipart/form-data" name="form" id="form">
<?php
		if($currRole) {
			echo createHiddenFieldWithKey('editrole');
?>
	<input type="hidden" name="roleid" id="roleid" value="<?php print $currRole->getID();?>">
	<input type="hidden" name="action" value="editrole">
<?php
		} else {
			echo createHiddenFieldWithKey('addrole');
?>
	<input type="hidden" id="roleid" value="0">
	<input type="hidden" name="action" value="addrole">
<?php
		}
?>
	<table class="table-condensed">
		<tr>
			<td><?php printMLText("role_name");?>:</td>
			<td><input type="text" name="name" id="name" value="<?php print $currRole ? htmlspecialchars($currRole->getName()) : "";?>"></td>
		</tr>
		<tr>
			<td><?php printMLText("role_type");?>:</td>
			<td><select name="role"><option value="<?php echo SeedDMS_Core_Role::role_user ?>"><?php printMLText("role_user"); ?></option><option value="<?php echo SeedDMS_Core_Role::role_admin ?>" <?php if($currRole && $currRole->getRole() == SeedDMS_Core_Role::role_admin) echo "selected"; ?>><?php printMLText("role_admin"); ?></option><option value="<?php echo SeedDMS_Core_Role::role_guest ?>" <?php if($currRole && $currRole->getRole() == SeedDMS_Core_Role::role_guest) echo "selected"; ?>><?php printMLText("role_guest"); ?></option></select></td>
		</tr>
<?php
		if($currRole && $currRole->getRole() != SeedDMS_Core_Role::role_admin) {
			echo "<tr>";
			echo "<td>".getMLText('restrict_access')."</td>";
			echo "<td>";
			foreach(array(S_DRAFT_REV, S_DRAFT_APP, S_IN_WORKFLOW, S_REJECTED, S_RELEASED, S_IN_REVISION, S_DRAFT, S_OBSOLETE) as $status) {
				echo "<input type=\"checkbox\" name=\"noaccess[]\" value=\"".$status."\" ".(in_array($status, $currRole->getNoAccess()) ? "checked" : "")."> ".getOverallStatusText($status)."<br />";
			}
			echo "</td>";
			echo "</tr>";
		}
		if($currRole && $accessop->check_controller_access('RoleMgr', array('action'=>'editrole')) || !$currRole && $accessop->check_controller_access('RoleMgr', array('action'=>'addrole'))) {
?>
		<tr>
			<td></td>
			<td><button type="submit" class="btn"><i class="icon-save"></i> <?php printMLText($currRole ? "save" : "add_role")?></button></td>
		</tr>
<?php
		}
?>
	</table>
	</form>
<?php
	} /* }}} */

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$accessop = $this->params['accessobject'];
		$selrole = $this->params['selrole'];
		$roles = $this->params['allroles'];

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");

		$this->contentHeading(getMLText("role_management"));
?>
<div class="row-fluid">
<div class="span4">
<div class="well">
<?php echo getMLText("selection")?>:
<select class="chzn-select" id="selector">
<option value="-1"><?php echo getMLText("choose_role")?>
<?php if($accessop->check_controller_access('RoleMgr', array('action'=>'addrole'))) { ?>
<option value="0"><?php echo getMLText("add_role")?>
<?php } ?>
<?php
		foreach ($roles as $currRole) {
			print "<option value=\"".$currRole->getID()."\" ".($selrole && $currRole->getID()==$selrole->getID() ? 'selected' : '').">" . htmlspecialchars($currRole->getName());
		}
?>
</select>
</div>
<div class="ajax" data-view="RoleMgr" data-action="info" <?php echo ($selrole ? "data-query=\"roleid=".$selrole->getID()."\"" : "") ?>></div>
</div>

<div class="span8">
	<div class="well">
		<div class="ajax" data-view="RoleMgr" data-action="form" <?php echo ($selrole ? "data-query=\"roleid=".$selrole->getID()."\"" : "") ?>></div>
	</div>
</div>

<?php
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
