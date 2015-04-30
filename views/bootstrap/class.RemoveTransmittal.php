<?php
/**
 * Implementation of RemoveTransmittal view
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
 * Class which outputs the html page for RemoveTransmittal view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_RemoveTransmittal extends SeedDMS_Bootstrap_Style {

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$rmtransmittal = $this->params['rmtransmittal'];

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");
		$this->contentHeading(getMLText("rm_transmittal"));
		$this->contentContainerStart();

?>
<form action="../op/op.UsrMgr.php" name="form1" method="post">
<input type="hidden" name="transmittalid" value="<?php print $rmtransmittal->getID();?>">
<input type="hidden" name="action" value="removetransmittal">
<?php echo createHiddenFieldWithKey('removetransmittal'); ?>
<p>
<?php printMLText("confirm_rm_transmittal", array ("name" => htmlspecialchars($rmtransmittal->getName())));?>
</p>

<p><button type="submit" class="btn"><i class="icon-remove"></i> <?php printMLText("rm_transmittal");?></button></p>

</form>
<?php
		$this->contentContainerEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
