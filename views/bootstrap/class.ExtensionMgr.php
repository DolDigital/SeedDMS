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

			$('a.import').click(function(ev){
				var element = $(this);
				$('#'+element.data('extname')+'-import').submit();
			});
		});
<?php
	} /* }}} */

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$httproot = $this->params['httproot'];
		$version = $this->params['version'];
		$extmgr = $this->params['extmgr'];
		$currenttab = $this->params['currenttab'];

		$reposurl = 'http://seeddms.steinmann.cx/repository';

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");
		$this->contentHeading(getMLText("extension_manager"));
?>
<div class="row-fluid">
	<div class="span4">
<?php
		if($extmgr->isWritableExitDir()) {
?>
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
					<button id="upload" type="_submit" class="btn"><i class="icon-upload"></i> <?= getMLText("import_extension"); ?></button>
				</div>
			</div>
		</form>
<?php
		} else {
			echo "<div class=\"alert alert-warning\">".getMLText('extension_mgr_no_upload')."</div>";
		}
?>
	</div>
	<div class="span8">
		<ul class="nav nav-tabs" id="extensionstab">
			<li class="<?php if(!$currenttab || $currenttab == 'installed') echo 'active'; ?>"><a data-target="#installed" data-toggle="tab"><?= getMLText('extension_mgr_installed'); ?></a></li>
			<li class="<?php if($currenttab == 'repository') echo 'active'; ?>"><a data-target="#repository" data-toggle="tab"><?= getMLText('extension_mgr_repository'); ?></a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane <?php if(!$currenttab || $currenttab == 'installed') echo 'active'; ?>" id="installed">
<?php
//		$this->contentContainerStart();
		echo "<table class=\"table _table-condensed\">\n";
		print "<thead>\n<tr>\n";
		print "<th></th>\n";	
		print "<th>".getMLText('name')."</th>\n";	
		print "<th>".getMLText('version')."</th>\n";	
		print "<th>".getMLText('author')."</th>\n";	
		print "<th></th>\n";	
		print "</tr></thead><tbody>\n";
		$errmsgs = array();
		foreach($GLOBALS['EXT_CONF'] as $extname=>$extconf) {
			$errmsgs = array();
			if(!isset($extconf['disable']) || $extconf['disable'] == false) {
				$extmgr->checkExtension($extname);
				$errmsgs = $extmgr->getErrorMsgs();
				if($errmsgs)
					echo "<tr class=\"error\">";
				else
					echo "<tr class=\"success\">";
			} else
				echo "<tr class=\"warning\">";
			echo "<td>";
			if($extconf['icon'])
				echo "<img src=\"".$httproot."ext/".$extname."/".$extconf['icon']."\" alt=\"".$extname."\" title=\"".$extname."\">";
			echo "</td>";
			echo "<td>".$extconf['title']."<br /><small>".$extconf['description']."</small>";
			if($errmsgs)
				echo "<div><img src=\"".$this->getImgPath("attention.gif")."\"> ".implode('<br /><img src="'.$this->getImgPath("attention.gif").'"> ', $errmsgs)."</div>";
			echo "</td>";
			echo "<td nowrap>".$extconf['version']."<br /><small>".$extconf['releasedate']."</small>";
			echo "</td>";
			echo "<td nowrap><a href=\"mailto:".$extconf['author']['email']."\">".$extconf['author']['name']."</a><br /><small>".$extconf['author']['company']."</small></td>";
			echo "<td nowrap>";
			echo "<div class=\"list-action\">";
			if($extconf['config'])
				echo "<a href=\"../out/out.Settings.php?currenttab=extensions#".$extname."\" title=\"".getMLText('configure_extension')."\"><i class=\"icon-cogs\"></i></a>";
			echo "<form style=\"display: inline-block; margin: 0px;\" method=\"post\" action=\"../op/op.ExtensionMgr.php\" id=\"".$extname."-download\">".createHiddenFieldWithKey('extensionmgr')."<input type=\"hidden\" name=\"action\" value=\"download\" /><input type=\"hidden\" name=\"extname\" value=\"".$extname."\" /><a class=\"download\" data-extname=\"".$extname."\" title=\"".getMLText('download_extension')."\"><i class=\"icon-download\"></i></a></form>";
			echo "</div>";
			echo "</td>";
			echo "</tr>\n";
		}
		echo "</tbody></table>\n";
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

			<div class="tab-pane <?php if($currenttab == 'repository') echo 'active'; ?>" id="repository">
<?php
		echo "<table class=\"table _table-condensed\">\n";
		print "<thead>\n<tr>\n";
		print "<th></th>\n";	
		print "<th>".getMLText('name')."</th>\n";	
		print "<th>".getMLText('version')."</th>\n";	
		print "<th>".getMLText('author')."</th>\n";	
		print "<th></th>\n";	
		print "</tr></thead><tbody>\n";
		$list = $extmgr->importExtensionList($reposurl);
		foreach($list as $e) {
			if($e[0] != '#') {
				$re = json_decode($e, true);
				$extmgr->checkExtension($re);
				$checkmsgs = $extmgr->getErrorMsgs();
				$needsupdate = !isset($GLOBALS['EXT_CONF'][$re['name']]) || SeedDMS_Extension_Mgr::cmpVersion($re['version'], $GLOBALS['EXT_CONF'][$re['name']]['version']) > 0;
				echo "<tr";
				if(isset($GLOBALS['EXT_CONF'][$re['name']])) {
					if($needsupdate)
						echo " class=\"warning\"";
					else
						echo " class=\"success\"";
				}
				echo ">";
				echo "<td></td>";
				echo "<td>".$re['title']."<br /><small>".$re['description']."</small>";
				if($checkmsgs)
					echo "<div><img src=\"".$this->getImgPath("attention.gif")."\"> ".implode('<br /><img src="'.$this->getImgPath("attention.gif").'"> ', $checkmsgs)."</div>";
				echo "</td>";
				echo "<td nowrap>".$re['version']."<br /><small>".$re['releasedate']."</small></td>";
				echo "<td nowrap>".$re['author']['name']."<br /><small>".$re['author']['company']."</small></td>";
				echo "<td nowrap>";
				echo "<div class=\"list-action\">";
				if($needsupdate && !$checkmsgs && $extmgr->isWritableExitDir())
					echo "<form style=\"display: inline-block; margin: 0px;\" method=\"post\" action=\"../op/op.ExtensionMgr.php\" id=\"".$extname."-import\">".createHiddenFieldWithKey('extensionmgr')."<input type=\"hidden\" name=\"action\" value=\"import\" /><input type=\"hidden\" name=\"url\" value=\"".$re['filename']."\" /><a class=\"import\" data-extname=\"".$extname."\" title=\"".getMLText('import_extension')."\"><i class=\"icon-download\"></i></a></form>";
				echo "</div>";
				echo "</td>";
				echo "</tr>";
			}
		}	
		echo "</tbody></table>\n";
?>
			</div>
		</div>
  </div>
 </div>
<?php
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
