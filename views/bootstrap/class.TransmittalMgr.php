<?php
/**
 * Implementation of TransmittalMgr view
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
 * Class which outputs the html page for TransmittalMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_TransmittalMgr extends SeedDMS_Bootstrap_Style {

	function js() { /* {{{ */
		header('Content-Type: application/javascript; charset=UTF-8');
		$this->printDeleteDocumentButtonJs();
		$this->printDeleteItemButtonJs();
		$this->printUpdateItemButtonJs();
	} /* }}} */

	/**
	 * Print button for updating the transmittal item to the newest version
	 *
	 * @param object $item
	 * @param string $msg message shown in case of successful update
	 */
	function printUpdateItemButton($item, $msg, $return=false){ /* {{{ */
		$itemid = $item->getID();
		$content = '';
    $content .= '<a class="update-transmittalitem-btn" rel="'.$itemid.'" msg="'.htmlspecialchars($msg, ENT_QUOTES).'" confirmmsg="'.htmlspecialchars(getMLText("confirm_update_transmittalitem"), ENT_QUOTES).'"><i class="icon-refresh"></i></a>';
		if($return)
			return $content;
		else
			echo $content;
		return '';
	} /* }}} */

	function printUpdateItemButtonJs(){ /* {{{ */
		echo "
		$(document).ready(function () {
			$('body').on('click', 'a.update-transmittalitem-btn', function(ev){
				id = $(ev.currentTarget).attr('rel');
				confirmmsg = $(ev.currentTarget).attr('confirmmsg');
				msg = $(ev.currentTarget).attr('msg');
				formtoken = '".createFormKey('updatetransmittalitem')."';
				bootbox.dialog(confirmmsg, [{
					\"label\" : \"<i class='icon-refresh'></i> ".getMLText("update_transmittalitem")."\",
					\"class\" : \"btn-danger\",
					\"callback\": function() {
						$.get('../op/op.Ajax.php',
							{ command: 'updatetransmittalitem', id: id, formtoken: formtoken },
							function(data) {
								if(data.success) {
									$('#table-row-document-'+id).hide('slow');
									noty({
										text: msg,
										type: 'success',
										dismissQueue: true,
										layout: 'topRight',
										theme: 'defaultTheme',
										timeout: 1500,
									});
								} else {
									noty({
										text: data.message,
										type: 'error',
										dismissQueue: true,
										layout: 'topRight',
										theme: 'defaultTheme',
										timeout: 3500,
									});
								}
							},
							'json'
						);
					}
				}, {
					\"label\" : \"".getMLText("cancel")."\",
					\"class\" : \"btn-cancel\",
					\"callback\": function() {
					}
				}]);
			});
		});
		";
	} /* }}} */

	/**
	 * Print button with link for deleting a transmittal item
	 *
	 * This button works just like the printDeleteDocumentButton()
	 *
	 * @param object $item transmittal item to be deleted
	 * @param string $msg message shown in case of successful deletion
	 * @param boolean $return return html instead of printing it
	 * @return string html content if $return is true, otherwise an empty string
	 */
	function printDeleteItemButton($item, $msg, $return=false){ /* {{{ */
		$itemid = $item->getID();
		$content = '';
    $content .= '<a class="delete-transmittalitem-btn" rel="'.$itemid.'" msg="'.htmlspecialchars($msg, ENT_QUOTES).'" confirmmsg="'.htmlspecialchars(getMLText("confirm_rm_transmittalitem"), ENT_QUOTES).'"><i class="icon-remove"></i></a>';
		if($return)
			return $content;
		else
			echo $content;
		return '';
	} /* }}} */

	function printDeleteItemButtonJs(){ /* {{{ */
		echo "
		$(document).ready(function () {
			$('body').on('click', 'a.delete-transmittalitem-btn', function(ev){
				id = $(ev.currentTarget).attr('rel');
				confirmmsg = $(ev.currentTarget).attr('confirmmsg');
				msg = $(ev.currentTarget).attr('msg');
				formtoken = '".createFormKey('removetransmittalitem')."';
				bootbox.dialog(confirmmsg, [{
					\"label\" : \"<i class='icon-remove'></i> ".getMLText("rm_transmittalitem")."\",
					\"class\" : \"btn-danger\",
					\"callback\": function() {
						$.get('../op/op.Ajax.php',
							{ command: 'removetransmittalitem', id: id, formtoken: formtoken },
							function(data) {
								if(data.success) {
									$('#table-row-document-'+id).hide('slow');
									noty({
										text: msg,
										type: 'success',
										dismissQueue: true,
										layout: 'topRight',
										theme: 'defaultTheme',
										timeout: 1500,
									});
								} else {
									noty({
										text: data.message,
										type: 'error',
										dismissQueue: true,
										layout: 'topRight',
										theme: 'defaultTheme',
										timeout: 3500,
									});
								}
							},
							'json'
						);
					}
				}, {
					\"label\" : \"".getMLText("cancel")."\",
					\"class\" : \"btn-cancel\",
					\"callback\": function() {
					}
				}]);
			});
		});
		";
	} /* }}} */

	function showTransmittalForm($transmittal) { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
?>
	<form action="../op/op.TransmittalMgr.php" method="post" enctype="multipart/form-data" name="form<?php print $transmittal ? $transmittal->getID() : '0';?>">
<?php
		if($transmittal) {
			echo createHiddenFieldWithKey('edittransmittal');
?>
	<input type="hidden" name="transmittalid" value="<?php print $transmittal->getID();?>">
	<input type="hidden" name="action" value="edittransmittal">
<?php
		} else {
			echo createHiddenFieldWithKey('addtransmittal');
?>
	<input type="hidden" name="action" value="addtransmittal">
<?php
		}
?>
	<table class="table-condensed">
<?php
	if($transmittal) {
?>
		<tr>
			<td></td>
			<td><a class="standardText btn" href="../out/out.RemoveTransmittal.php?transmittalid=<?php print $transmittal->getID();?>"><i class="icon-remove"></i> <?php printMLText("rm_transmittal");?></a></td>
		</tr>
<?php
	}
?>
		<tr>
			<td><?php printMLText("transmittal_name");?>:</td>
			<td><input type="text" name="name" value="<?php print $transmittal ? htmlspecialchars($transmittal->getName()) : "";?>"></td>
		</tr>
		<tr>
			<td><?php printMLText("transmittal_comment");?>:</td>
			<td><input type="text" name="comment" value="<?php print $transmittal ? htmlspecialchars($transmittal->getComment()) : "";?>"></td>
		</tr>
		<tr>
			<td></td>
			<td><button type="submit" class="btn"><i class="icon-save"></i> <?php printMLText($transmittal ? "save" : "add_transmittal")?></button></td>
		</tr>
	</table>
	</form>
<?php
	} /* }}} */

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$seltransmittal = $this->params['seltransmittal'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewconverters'];
		$timeout = $this->params['timeout'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout);
		$previewer->setConverters($previewconverters);

		$this->htmlAddHeader('<script type="text/javascript" src="../styles/'.$this->theme.'/bootbox/bootbox.min.js"></script>'."\n", 'js');

		$this->htmlStartPage(getMLText("my_transmittals"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("my_transmittals"), "my_documents");
		$this->contentHeading(getMLText("my_transmittals"));
?>
<div class="row-fluid">
<div class="span4">
<?php
		$this->contentContainerStart();

		$transmittals = $dms->getAllTransmittals($user);

		if ($transmittals){
			print "<table class=\"table table-condensed\">";
			print "<thead>\n<tr>\n";
			print "<th>".getMLText("name")."</th>\n";
			print "<th>".getMLText("comment")."</th>\n";
			print "<th>".getMLText("transmittal_size")."</th>\n";
			print "<th></th>\n";
			print "</tr>\n</thead>\n<tbody>\n";
			foreach($transmittals as $transmittal) {
				print "<tr>\n";
				print "<td>".$transmittal->getName()."</td>";
				print "<td>".$transmittal->getComment()."</td>";
				$items = $transmittal->getItems();
				print "<td>".count($items)." <em>(".SeedDMS_Core_File::format_filesize($transmittal->getSize()).")</em></td>";
				print "<td>";
				print "<div class=\"list-action\">";
				print "<a href=\"../out/out.TransmittalMgr.php?transmittalid=".$transmittal->getID()."\" title=\"".getMLText("edit_transmittal_props")."\"><i class=\"icon-edit\"></i></a>";
				print "</div>";
				print "</td>";
				print "</tr>\n";
			}
			print "</tbody>\n</table>\n";
		}

		$this->contentContainerEnd();
?>
</div>
<div class="span8">
<?php
		$this->contentContainerStart();
		$this->showTransmittalForm($seltransmittal);
		$this->contentContainerEnd();

		if($seltransmittal) {
			$items = $seltransmittal->getItems();
			if($items) {
				print "<table class=\"table table-condensed\">";
				print "<thead>\n<tr>\n";
				print "<th></th>\n";
				print "<th>".getMLText("name")."</th>\n";
				print "<th>".getMLText("status")."</th>\n";
				print "<th>".getMLText("document")."</th>\n";
				print "<th>".getMLText("action")."</th>\n";
				print "</tr>\n</thead>\n<tbody>\n";
				foreach($items as $item) {
					if($content = $item->getContent()) {
						$document = $content->getDocument();
						$latestcontent = $document->getLatestContent();
						if ($document->getAccessMode($user) >= M_READ) {
							echo "<tr id=\"table-row-transmittalitem-".$item->getID()."\">";
							echo $this->documentListRow($document, $previewer, true, $content->getVersion());
							echo "<td><div class=\"list-action\">";
							$this->printDeleteItemButton($item, getMLText('transmittalitem_removed'));
							if($latestcontent->getVersion() != $content->getVersion())
								$this->printUpdateItemButton($item, getMLText('transmittalitem_updated', array('prevversion'=>$content->getVersion(), 'newversion'=>$latestcontent->getVersion())));
							echo "</div></td>";
							echo "</tr>";
						}
					} else {
						echo "<tr id=\"table-row-transmittalitem-".$item->getID()."\">";
						echo "<td colspan=\"5\">content ist weg</td>";
						echo "</tr>";
					}
				}
				print "</tbody>\n</table>\n";
				print "<a class=\"btn btn-default\" href=\"../op/op.TransmittalDownload.php?transmittalid=".$seltransmittal->getID()."\">".getMLText('download')."</a>";
			}
		}
?>
</div>
</div>
<?php
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
