<?php
/**
 * Implementation of AddToTransmittal view
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
 * Class which outputs the html page for AddToTransmittal view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_AddToTransmittal extends SeedDMS_Bootstrap_Style {

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$transmittals = $this->params['transmittals'];
		$content = $this->params['version'];

		$this->htmlStartPage(getMLText("my_documents"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("my_documents"), "my_documents");
		$this->contentHeading(getMLText("add_to_transmittal"));
		$this->contentContainerStart();

?>
<form action="../op/op.AddToTransmittal.php" name="form1" method="post">
<input type="hidden" name="documentid" value="<?php print $content->getDocument()->getID();?>">
<input type="hidden" name="version" value="<?php print $content->getVersion();?>">
<input type="hidden" name="action" value="addtotransmittal">
<?php echo createHiddenFieldWithKey('addtotransmittal'); ?>

<p>
<?php printMLText("transmittal"); ?>:
<select name="assignTo">
<?php
		foreach ($transmittals as $transmittal) {
			print "<option value=\"".$transmittal->getID()."\">" . htmlspecialchars($transmittal->getName()) . "</option>";
		}
?>
</select>
</p>

<p><button type="submit" class="btn"><i class="icon-plus"></i> <?php printMLText("add");?></button></p>

</form>
<?php
		$this->contentContainerEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
