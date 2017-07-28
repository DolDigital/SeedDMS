<?php
/**
 * Implementation of RemoveUserFromProcesses view
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2017 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
require_once("class.Bootstrap.php");

/**
 * Class which outputs the html page for RemoveUserFromProcesses view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2017 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_RemoveUserFromProcesses extends SeedDMS_Bootstrap_Style {

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$rmuser = $this->params['rmuser'];

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");
		$this->contentHeading(getMLText("rm_user"));
		$this->contentContainerStart();

?>
<form action="../op/op.UsrMgr.php" name="form1" method="post">
<input type="hidden" name="userid" value="<?php print $rmuser->getID();?>">
<input type="hidden" name="action" value="removefromprocesses">
<?php echo createHiddenFieldWithKey('removefromprocesses'); ?>
<p>
<?php printMLText("confirm_rm_user_from_processes", array ("username" => htmlspecialchars($rmuser->getFullName())));?>
</p>

<p><button type="submit" class="btn"><i class="icon-remove"></i> <?php printMLText("rm_user");?></button></p>

</form>
<?php
		$this->contentContainerEnd();
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
