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

	/**
	 * Add some javascript at the bottom of the page
	 */
	function addAdditionalJS(){ /* {{{ */
		$this->addFooterJS("
	$('body').on('click', 'button.removetransmittalitem', function(ev){
		ev.preventDefault();
		var element = $(this);
		attr_rel = $(ev.currentTarget).attr('rel');
		attr_msg = $(ev.currentTarget).attr('msg');
		attr_formtoken = $(ev.currentTarget).attr('formtoken');
		id = attr_rel;
		$.get('../op/op.Ajax.php',
			{ command: 'removetransmittalitem', id: id, formtoken: attr_formtoken },
			function(data) {
//				console.log(data);
				if(data.success) {
					$('#table-row-transmittalitem-'+id).hide('slow');
					noty({
						text: attr_msg,
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
	});
	$('body').on('click', 'button.updatetransmittalitem', function(ev){
		ev.preventDefault();
		var element = $(this);
		attr_rel = $(ev.currentTarget).attr('rel');
		attr_msg = $(ev.currentTarget).attr('msg');
		attr_formtoken = $(ev.currentTarget).attr('formtoken');
		id = attr_rel;
		$.get('../op/op.Ajax.php',
			{ command: 'updatetransmittalitem', id: id, formtoken: attr_formtoken },
			function(data) {
//				console.log(data);
				if(data.success) {
					$('#update-transmittalitem-btn-'+id).hide('slow');
					noty({
						text: attr_msg,
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
		$('#update-transmittalitem-btn-'+id).popover('hide');
	});
");
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
    $content .= '<a id="update-transmittalitem-btn-'.$itemid.'" rel="'.$itemid.'" msg="'.htmlspecialchars($msg, ENT_QUOTES).'"><i class="icon-refresh"></i></a>';
		$this->addFooterJS("
$('#update-transmittalitem-btn-".$itemid."').popover({
	title: '".getMLText("update_transmittalitem")."',
	placement: 'left',
	html: true,
	content: \"<div>".htmlspecialchars(getMLText("confirm_update_transmittalitem"), ENT_QUOTES)."</div><div><button class='btn btn-danger updatetransmittalitem' style='float: right; margin:10px 0px;' rel='".$itemid."' msg='".htmlspecialchars($msg, ENT_QUOTES)."' formtoken='".createFormKey('updatetransmittalitem')."' id='confirm-update-transmittalitem-btn-".$itemid."'><i class='icon-refresh'></i> ".getMLText("update_transmittalitem")."</button> <button type='button' class='btn' style='float: right; margin:10px 10px;' onclick='$(&quot;#update-transmittalitem-btn-".$itemid."&quot;).popover(&quot;hide&quot;);'>".getMLText('cancel')."</button></div>\"});
");
		if($return)
			return $content;
		else
			echo $content;
		return '';
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
    $content .= '<a id="delete-transmittalitem-btn-'.$itemid.'" rel="'.$itemid.'" msg="'.htmlspecialchars($msg, ENT_QUOTES).'"><i class="icon-remove"></i></a>';
		$this->addFooterJS("
$('#delete-transmittalitem-btn-".$itemid."').popover({
	title: '".getMLText("rm_transmittalitem")."',
	placement: 'left',
	html: true,
	content: \"<div>".htmlspecialchars(getMLText("confirm_rm_transmittalitem"), ENT_QUOTES)."</div><div><button class='btn btn-danger removetransmittalitem' style='float: right; margin:10px 0px;' rel='".$itemid."' msg='".htmlspecialchars($msg, ENT_QUOTES)."' formtoken='".createFormKey('removetransmittalitem')."' id='confirm-delete-transmittalitem-btn-".$itemid."'><i class='icon-remove'></i> ".getMLText("rm_transmittalitem")."</button> <button type='button' class='btn' style='float: right; margin:10px 10px;' onclick='$(&quot;#delete-transmittalitem-btn-".$itemid."&quot;).popover(&quot;hide&quot;);'>".getMLText('cancel')."</button></div>\"});
");
		if($return)
			return $content;
		else
			echo $content;
		return '';
	} /* }}} */

	function showTransmittalForm($transmittal) { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
?>
	<form action="../op/op.TransmittalMgr.php" method="post" enctype="multipart/form-data" name="form<?php print $transmittal ? $transmittal->getID() : '0';?>" onsubmit="return checkForm('<?php print $transmittal ? $transmittal->getID() : '0';?>');">
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

		$db = $dms->getDB();
		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth);
		$previewer->setConverters($previewconverters);

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
				$this->addAdditionalJS();
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
		$this->htmlEndPage();
	} /* }}} */
}
?>
