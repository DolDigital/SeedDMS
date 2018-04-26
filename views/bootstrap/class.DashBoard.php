<?php
/**
 * Implementation of DashBoard view
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
 * Class which outputs the html page for DashBoard view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_DashBoard extends SeedDMS_Bootstrap_Style {

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$orderby = $this->params['orderby'];
		$enableFolderTree = $this->params['enableFolderTree'];
		$showtree = $this->params['showtree'];
		$cachedir = $this->params['cachedir'];

		$this->htmlStartPage(getMLText("dashboard"));

		$this->globalNavigation($folder);
		$this->contentStart();

		$this->contentHeading("Willkommen im Onlineportal");
?>
		<div class="row-fluid">
		<div class="span12">
		  <?php $this->contentHeading('Gruppen'); ?>
			<?php	$this->contentContainerStart(); ?>
			  Hier eine Übersicht der Gruppen, auf die der Anwender zugreifen darf.
			<?php	$this->contentContainerEnd(); ?>
		</div>
		<div>
		<div class="row-fluid">
		<div class="span4">
		  <?php $this->contentHeading('Lesezeichen'); ?>
			<?php	$this->contentContainerStart(); ?>
			  <table class="table"><thead>
				<tr>
				<th></th>
				<th>Name</th>
				<th>Besitzer</th>
				<th>Status</th>

				</tr>
				</thead>
				<tbody>
				<tr><td><a href="../op/op.Download.php?documentid=403&version=1"><img class="mimeicon" width="40"src="../op/op.Preview.php?documentid=403&version=1&width=40" title="application/pdf"></a></td><td><a href="out.ViewDocument.php?documentid=403&showtree=1">walking-paper-4hxq62d9.pdf</a></td>
				<td>Admin</td><td>freigegeben</td></tr>
				</tbody>
				</table>
			<?php	$this->contentContainerEnd(); ?>
		  <?php $this->contentHeading('Neue Dokumente'); ?>
			<?php	$this->contentContainerStart(); ?>
			<?php	$this->contentContainerEnd(); ?>
		  <?php $this->contentHeading('Dokumente zur Prüfung'); ?>
			<?php	$this->contentContainerStart(); ?>
			<?php	$this->contentContainerEnd(); ?>
		  <?php $this->contentHeading('Dokumente zur Genehmigung'); ?>
			<?php	$this->contentContainerStart(); ?>
			<?php	$this->contentContainerEnd(); ?>
		</div>
		<div class="span4">
		  <?php $this->contentHeading('Neue Beiträge im Wiki'); ?>
			<?php	$this->contentContainerStart(); ?>
			  <table class="table"><thead>
				<tr>
				<th></th>
				<th>Name</th>
				<th>Besitzer</th>
				<th>Geändert</th>

				</tr>
				</thead>
				<tbody>
				<tr><td><a href="../op/op.Download.php?documentid=403&version=1"><img class="mimeicon" width="40"src="../op/op.Preview.php?documentid=403&version=1&width=40" title="application/pdf"></a></td><td><a href="out.ViewDocument.php?documentid=403&showtree=1">Konzept Bebauung Waldstr.</a></td>
				<td>H. Huber</td><td>28.11.2013</td></tr>
				</tbody>
				</table>
			<?php	$this->contentContainerEnd(); ?>
		  <?php $this->contentHeading('Zuletzt bearbeitet'); ?>
			<?php	$this->contentContainerStart(); ?>
			<?php	$this->contentContainerEnd(); ?>
		</div>
		<div class="span4">
		  <?php $this->contentHeading('Neue Beiträge im Diskussionsforum'); ?>
			<?php	$this->contentContainerStart(); ?>
			<?php	$this->contentContainerEnd(); ?>
		</div>
		</div>
		
<?php
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}

?>
