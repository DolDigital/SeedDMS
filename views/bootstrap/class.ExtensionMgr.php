<?php
/**
 * Implementation of ExtensionMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2013 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
require_once("class.Bootstrap.php");

/**
 * Class which outputs the html page for ExtensionMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2013 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_ExtensionMgr extends SeedDMS_Bootstrap_Style {

	function js() { /* {{{ */
		$partitionsize = $this->params['partitionsize'];
		$maxuploadsize = $this->params['maxuploadsize'];

		header('Content-Type: application/javascript');
?>
		$(document).ready( function() {
			$('a.download').click(function(ev){
				var element = $(this);
				$('#'+element.data('extname')+'-download').submit();
/*
				var element = $(this);
				ev.preventDefault();
				$.ajax({url: '../op/op.ExtensionMgr.php',
					type: 'POST',
					dataType: "json",
					data: {action: 'download', 'formtoken': '<?= createFormKey('extensionmgr') ?>', 'extname': element.data('extname')},
					success: function(data) {
						noty({
							text: data.msg,
							type: (data.error) ? 'error' : 'success',
							dismissQueue: true,
							layout: 'topRight',
							theme: 'defaultTheme',
							timeout: 1500,
						});
					}
				});
*/
			});
		});
<?php
	} /* }}} */

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$httproot = $this->params['httproot'];
		$version = $this->params['version'];

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");
		$this->contentHeading(getMLText("extension_manager"));
?>
<div class="row-fluid">
	<div class="span4">
		<form class="form-horizontal" method="post" enctype="multipart/form-data" action="../op/op.ExtensionMgr.php">
			<?= createHiddenFieldWithKey('extensionmgr') ?>
			<input type="hidden" name="action" value="upload" />
			<div class="control-group">
				<label class="control-label" for="upload"><?= getMLText('extension_archive'); ?></label>
				<div class="controls">
					<?php $this->printFileChooser('userfile', false); ?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="enddate"></label>
				<div class="controls">
					<button id="upload" type="_submit" class="btn"><i class="icon-upload"></i> <?= getMLText("upload_extension"); ?></button>
				</div>
			</div>
		</form>
	</div>
	<div class="span8">
<?php
//		$this->contentContainerStart();
		echo "<table class=\"table _table-condensed\">\n";
		print "<thead>\n<tr>\n";
		print "<th></th>\n";	
		print "<th>".getMLText('name')."</th>\n";	
		print "<th>".getMLText('version')."</th>\n";	
		print "<th>".getMLText('author')."</th>\n";	
		print "</tr></thead>\n";
		$errmsgs = array();
		foreach($GLOBALS['EXT_CONF'] as $extname=>$extconf) {
			$errmsgs = array();
			if(!isset($extconf['disable']) || $extconf['disable'] == false) {
				/* check dependency on specific seeddms version */
				if(!isset($extconf['constraints']['depends']['seeddms']))
					$errmsgs[] = "Missing dependency on SeedDMS";
				if(!isset($extconf['constraints']['depends']['php']))
					$errmsgs[] = "Missing dependency on PHP";

				if(isset($extconf['constraints']['depends'])) {
					foreach($extconf['constraints']['depends'] as $dkey=>$dval) {
						switch($dkey) {
						case 'seeddms':
							$tmp = explode('-', $dval, 2);
							if(cmpVersion($tmp[0], $version->version()) > 0 || ($tmp[1] && cmpVersion($tmp[1], $version->version()) < 0))
								$errmsgs[] = sprintf("Incorrect SeedDMS version (needs version %s)", $extconf['constraints']['depends']['seeddms']);
							break;
						case 'php':
							$tmp = explode('-', $dval, 2);
							if(cmpVersion($tmp[0], phpversion()) > 0 || ($tmp[1] && cmpVersion($tmp[1], phpversion()) < 0))
								$errmsgs[] = sprintf("Incorrect PHP version (needs version %s)", $extconf['constraints']['depends']['php']);
							break;
						default:
							$tmp = explode('-', $dval, 2);
							if(isset($GLOBALS['EXT_CONF'][$dkey]['version'])) {
								if(cmpVersion($tmp[0], $GLOBALS['EXT_CONF'][$dkey]['version']) > 0 || ($tmp[1] && cmpVersion($tmp[1], $GLOBALS['EXT_CONF'][$dkey]['version']) < 0))
									$errmsgs[] = sprintf("Incorrect version of extension '%s' (needs version '%s' but provides '%s')", $dkey, $dval, $GLOBALS['EXT_CONF'][$dkey]['version']);
							} else {
								$errmsgs[] = sprintf("Missing extension or version for '%s'", $dkey);
							}
							break;
						}
					}
				}

				if($errmsgs)
					echo "<tr class=\"error\">";
				else
					echo "<tr class=\"success\">";
			} else
				echo "<tr class=\"warning\">";
			echo "<td>";
			if($extconf['icon'])
				echo "<img src=\"".$httproot."ext/".$extname."/".$extconf['icon']."\">";
			echo "</td>";
			echo "<td>".$extconf['title']."<br /><small>".$extconf['description']."</small>";
			if($errmsgs)
				echo "<div><img src=\"".$this->getImgPath("attention.gif")."\"> ".implode('<br /><img src="'.$this->getImgPath("attention.gif").'"> ', $errmsgs)."</div>";
			echo "</td>";
			echo "<td nowrap>".$extconf['version']."<br /><small>".$extconf['releasedate']."</small>";
			echo "<div class=\"list-action\">";
			if($extconf['config'])
				echo "<a href=\"../out/out.Settings.php?currenttab=extensions#".$extname."\" title=\"".getMLText('configure_extension')."\"><i class=\"icon-cogs\"></i></a>";
			echo "<form style=\"display: inline-block; margin: 0px;\" method=\"post\" action=\"../op/op.ExtensionMgr.php\" id=\"".$extname."-download\">".createHiddenFieldWithKey('extensionmgr')."<input type=\"hidden\" name=\"action\" value=\"download\" /><input type=\"hidden\" name=\"extname\" value=\"".$extname."\" /><a class=\"download\" data-extname=\"".$extname."\" title=\"".getMLText('download_extension')."\"><i class=\"icon-download\"></i></a></form>";
			echo "</div>";
			echo "</td>";
			echo "<td nowrap><a href=\"mailto:".$extconf['author']['email']."\">".$extconf['author']['name']."</a><br /><small>".$extconf['author']['company']."</small></td>";
			echo "</tr>\n";
		}
		echo "</table>\n";
?>
<form action="../op/op.ExtensionMgr.php" name="form1" method="post">
  <?php echo createHiddenFieldWithKey('extensionmgr'); ?>
	<input type="hidden" name="action" value="refresh" />
	<p><button type="submit" class="btn"><i class="icon-refresh"></i> <?php printMLText("refresh");?></button></p>
</form>
<?php
//		$this->contentContainerEnd();
?>
  </div>
 </div>
<?php
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
