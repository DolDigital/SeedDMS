<?php
/**
 * Implementation of Settings view
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
 * Class which outputs the html page for Settings view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_Settings extends SeedDMS_Bootstrap_Style {

	protected function showTextField($name, $value, $type='', $placeholder='') { /* {{{ */
		if($type != 'password' && strlen($value) > 80)
			echo '<textarea class="input-xxlarge" name="'.$name.'">'.$value.'</textarea>';
		else {
			if(strlen($value) > 40)
				$class = 'input-xxlarge';
			elseif(strlen($value) > 30)
				$class = 'input-xlarge';
			elseif(strlen($value) > 18)
				$class = 'input-large';
			elseif(strlen($value) > 12)
				$class = 'input-medium';
			else
				$class = 'input-small';
			echo '<input '.($type=='password' ? 'type="password"' : 'type="text"').'" class="'.$class.'" name="'.$name.'" value="'.$value.'" placeholder="'.$placeholder.'"/>';
		}
	} /* }}} */

	/**
	 * Place arbitrary html in a headline
	 *
	 * @param string $text html code to be shown as headline
	 */
	protected function showRawConfigHeadline($text) { /* {{{ */
?>
      <tr><td><b><?= $text ?></b></td></tr>
<?php
	} /* }}} */

	/**
	 * Place text in a headline
	 *
	 * @param string $text text to be shown as headline
	 */
	protected function showConfigHeadline($title) { /* {{{ */
		$this->showRawConfigHeadline(htmlspecialchars(getMLText($title)));
	} /* }}} */

	/**
	 * Show a text input configuration option
	 *
	 * @param string $title title of the option
	 * @param string $name name of html input field
	 */
	protected function showConfigText($title, $name) { /* {{{ */
		$settings = $this->params['settings'];
?>
      <tr title="<?= getMLText($title."_desc") ?>">
        <td><?= getMLText($title) ?>:</td>
				<td><?php $this->showTextField($name, $settings->{"_".$name}); ?></td>
			</tr>
<?php
	} /* }}} */

	/**
	 * Show a checkbox configuration option
	 *
	 * @param string $title title of the option
	 * @param string $name name of html input field
	 */
	protected function showConfigCheckbox($title, $name) { /* {{{ */
		$settings = $this->params['settings'];
?>
      <tr title="<?= getMLText($title."_desc") ?>">
        <td><?= getMLText($title) ?>:</td>
				<td><input name="<?= $name ?>" type="checkbox" <?php if ($settings->{"_".$name}) echo "checked" ?> /></td>
      </tr>
<?php
	} /* }}} */

	protected function showConfigOption($title, $name, $values, $multiple=false, $translate=false) { /* {{{ */
		$settings = $this->params['settings'];
		$isass = count(array_filter(array_keys($values), 'is_string')) > 0;
//		var_dump($values);
//		echo $isass ? 'asso' : 'indexed';
?>
      <tr title="<?= getMLText($title."_desc") ?>">
        <td><?= getMLText($title) ?>:</td>
				<td>
<?php if($multiple) { ?>
					<select name="<?= $name ?>[]" multiple>
<?php } else { ?>
					<select name="<?= $name ?>">
<?php }
		foreach($values as $i=>$value) {
			$optval = trim($isass ? $i : $value);
			echo '<option value="' . $optval . '" ';
			if (($multiple && in_array($optval, $settings->{"_".$name})) || (!$multiple && $optval == $settings->{"_".$name}))
				echo "selected";
			echo '>' . ($translate ? getMLText($value) : $value). '</option>';
		}
?>
          </select>
        </td>
      </tr>
<?php
	} /* }}} */

	function js() { /* {{{ */

		header('Content-Type: application/javascript');
?>
		$(document).ready( function() {
			$('#settingstab li a').click(function(event) {
				$('#currenttab').val($(event.currentTarget).data('target').substring(1));
			});

			$('a.sendtestmail').click(function(ev){
				ev.preventDefault();
				$.ajax({url: '../op/op.Ajax.php',
					type: 'GET',
					dataType: "json",
					data: {command: 'testmail'},
					success: function(data) {
						console.log(data);
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
			});
		});
<?php
	} /* }}} */

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$currenttab = $this->params['currenttab'];

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");
		$this->contentHeading(getMLText("settings"));

?>
  <form action="../op/op.Settings.php" method="post" enctype="multipart/form-data" name="form0" >
  <input type="hidden" name="action" value="saveSettings" />
	<input type="hidden" id="currenttab" name="currenttab" value="<?php echo $currenttab ? $currenttab : 'site'; ?>" />
<?php
if(!is_writeable($settings->_configFilePath)) {
	print "<div class=\"alert alert-warning\">";
	echo "<p>".getMLText("settings_notwritable")."</p>";
	print "</div>";
}
?>

  <ul class="nav nav-tabs" id="settingstab">
		<li class="<?php if(!$currenttab || $currenttab == 'site') echo 'active'; ?>"><a data-target="#site" data-toggle="tab"><?php printMLText('settings_Site'); ?></a></li>
	  <li class="<?php if($currenttab == 'system') echo 'active'; ?>"><a data-target="#system" data-toggle="tab"><?php printMLText('settings_System'); ?></a></li>
	  <li class="<?php if($currenttab == 'advanced') echo 'active'; ?>"><a data-target="#advanced" data-toggle="tab"><?php printMLText('settings_Advanced'); ?></a></li>
	  <li class="<?php if($currenttab == 'extensions') echo 'active'; ?>"><a data-target="#extensions" data-toggle="tab"><?php printMLText('settings_Extensions'); ?></a></li>
	</ul>

	<div class="tab-content">
	  <div class="tab-pane <?php if(!$currenttab || $currenttab == 'site') echo 'active'; ?>" id="site">
<?php		$this->contentContainerStart(); ?>
    <table class="table-condensed">
      <!--
        -- SETTINGS - SITE - DISPLAY
			-->
<?php $this->showConfigHeadline('settings_Display'); ?>
<?php $this->showConfigText('settings_siteName', 'siteName'); ?>
<?php $this->showConfigText('settings_footNote', 'footNote'); ?>
<?php $this->showConfigCheckbox('settings_printDisclaimer', 'printDisclaimer'); ?>
<?php $this->showConfigOption('settings_available_languages', 'availablelanguages', getAvailableLanguages(), true, true); ?>
<?php $this->showConfigOption('settings_language', 'language', getAvailableLanguages(), false, true); ?>
<?php $this->showConfigOption('settings_theme', 'theme', UI::getStyles(), false, false); ?>
<?php $this->showConfigText('settings_previewWidthList', 'previewWidthList'); ?>
<?php $this->showConfigText('settings_previewWidthMenuList', 'previewWidthMenuList'); ?>
<?php $this->showConfigText('settings_previewWidthDropFolderList', 'previewWidthDropFolderList'); ?>
<?php $this->showConfigText('settings_previewWidthDetail', 'previewWidthDetail'); ?>
<?php $this->showConfigCheckbox('settings_showFullPreview', 'showFullPreview'); ?>
<?php $this->showConfigCheckbox('settings_convertToPdf', 'convertToPdf'); ?>
<?php $this->showConfigText('settings_maxItemsPerPage', 'maxItemsPerPage'); ?>
<?php $this->showConfigText('settings_incItemsPerPage', 'incItemsPerPage'); ?>

      <!--
        -- SETTINGS - SITE - EDITION
      -->
<?php $this->showConfigHeadline('settings_Edition'); ?>
<?php $this->showConfigCheckbox('settings_strictFormCheck', 'strictFormCheck'); ?>
      <tr title="<?php printMLText("settings_viewOnlineFileTypes_desc");?>">
        <td><?php printMLText("settings_viewOnlineFileTypes");?>:</td>
				<td><?php $this->showTextField("viewOnlineFileTypes", $settings->getViewOnlineFileTypesToString()); ?></td>
      </tr>
      <tr title="<?php printMLText("settings_editOnlineFileTypes_desc");?>">
        <td><?php printMLText("settings_editOnlineFileTypes");?>:</td>
				<td><?php $this->showTextField("editOnlineFileTypes", $settings->getEditOnlineFileTypesToString()); ?></td>
      </tr>
<?php $this->showConfigCheckbox('settings_enableConverting', 'enableConverting'); ?>
<?php $this->showConfigCheckbox('settings_enableEmail', 'enableEmail'); ?>
<?php $this->showConfigCheckbox('settings_enableUsersView', 'enableUsersView'); ?>
<?php $this->showConfigCheckbox('settings_enableFullSearch', 'enableFullSearch'); ?>
<?php $this->showConfigText('settings_maxSizeForFullText', 'maxSizeForFullText'); ?>
<?php $this->showConfigOption('settings_fullSearchEngine', 'fullSearchEngine', array('lucene'=>'settings_fullSearchEngine_vallucene', 'sqlitefts'=>'settings_fullSearchEngine_valsqlitefts'), false, true); ?>
<?php $this->showConfigOption('settings_defaultSearchMethod', 'defaultSearchMethod', array('database'=>'settings_defaultSearchMethod_valdatabase', 'fulltext'=>'settings_defaultSearchMethod_valfulltext'), false, true); ?>
<?php $this->showConfigCheckbox('settings_showSingleSearchHit', 'showSingleSearchHit'); ?>
<?php $this->showConfigText('settings_stopWordsFile', 'stopWordsFile'); ?>
<?php $this->showConfigCheckbox('settings_enableClipboard', 'enableClipboard'); ?>
<?php $this->showConfigCheckbox('settings_enableMenuTasks', 'enableMenuTasks'); ?>
<?php $this->showConfigCheckbox('settings_enableDropFolderList', 'enableDropFolderList'); ?>
<?php $this->showConfigCheckbox('settings_enableSessionList', 'enableSessionList'); ?>
<?php $this->showConfigCheckbox('settings_enableDropUpload', 'enableDropUpload'); ?>
<?php $this->showConfigCheckbox('settings_enableMultiUpload', 'enableMultiUpload'); ?>
<?php $this->showConfigCheckbox('settings_enableFolderTree', 'enableFolderTree'); ?>
<?php $this->showConfigOption('settings_expandFolderTree', 'expandFolderTree', array(' 0'=>'settings_expandFolderTree_val0', ' 1'=>'settings_expandFolderTree_val1', ' 2'=>'settings_expandFolderTree_val2'), false, true); ?>
<?php $this->showConfigCheckbox('settings_enableRecursiveCount', 'enableRecursiveCount'); ?>
<?php $this->showConfigText('settings_maxRecursiveCount', 'maxRecursiveCount'); ?>
<?php $this->showConfigCheckbox('settings_enableLanguageSelector', 'enableLanguageSelector'); ?>
<?php $this->showConfigCheckbox('settings_enableHelp', 'enableHelp'); ?>
<?php $this->showConfigCheckbox('settings_enableThemeSelector', 'enableThemeSelector'); ?>
<?php $this->showConfigOption('settings_sortUsersInList', 'sortUsersInList', array(' '=>'settings_sortUsersInList_val_login', 'fullname'=>'settings_sortUsersInList_val_fullname'), false, true); ?>
<?php $this->showConfigOption('settings_sortFoldersDefault', 'sortFoldersDefault', array('u'=>'settings_sortFoldersDefault_val_unsorted', 's'=>'settings_sortFoldersDefault_val_sequence', 'n'=>'settings_sortFoldersDefault_val_name'), false, true); ?>
<?php $this->showConfigOption('settings_defaultDocPosition', 'defaultDocPosition', array('end'=>'settings_defaultDocPosition_val_end', 'start'=>'settings_defaultDocPosition_val_start'), false, true); ?>

      <!--
        -- SETTINGS - SITE - WEBDAV
      -->
<?php $this->showConfigHeadline('settings_webdav'); ?>
<?php $this->showConfigCheckbox('settings_enableWebdavReplaceDoc', 'enableWebdavReplaceDoc'); ?>

      <!--
        -- SETTINGS - SITE - CALENDAR
      -->
<?php $this->showConfigHeadline('settings_Calendar'); ?>
<?php $this->showConfigCheckbox('settings_enableCalendar', 'enableCalendar'); ?>
<?php $this->showConfigOption('settings_calendarDefaultView', 'calendarDefaultView', array('w'=>'week_view', 'm'=>'month_view', 'y'=>'year_view'), false, true); ?>
<?php $this->showConfigOption('settings_firstDayOfWeek', 'firstDayOfWeek', array(' 0'=>'sunday', ' 1'=>'monday', ' 2'=>'tuesday', ' 3'=>'wednesday', ' 4'=>'thursday', ' 5'=>'friday', ' 6'=>'saturday'), false, true); ?>
    </table>
<?php		$this->contentContainerEnd(); ?>
  </div>

	  <div class="tab-pane <?php if($currenttab == 'system') echo 'active'; ?>" id="system">
<?php		$this->contentContainerStart(); ?>
    <table class="table-condensed">
     <!--
        -- SETTINGS - SYSTEM - SERVER
      -->
<?php $this->showConfigHeadline('settings_Server'); ?>
<?php $this->showConfigText('settings_rootDir', 'rootDir'); ?>
<?php $this->showConfigText('settings_httpRoot', 'httpRoot'); ?>
<?php $this->showConfigText('settings_contentDir', 'contentDir'); ?>
<?php $this->showConfigText('settings_backupDir', 'backupDir'); ?>
<?php $this->showConfigText('settings_cacheDir', 'cacheDir'); ?>
<?php $this->showConfigText('settings_stagingDir', 'stagingDir'); ?>
<?php $this->showConfigText('settings_luceneDir', 'luceneDir'); ?>
<?php $this->showConfigText('settings_dropFolderDir', 'dropFolderDir'); ?>
<?php $this->showConfigText('settings_repositoryUrl', 'repositoryUrl'); ?>
<?php $this->showConfigCheckbox('settings_logFileEnable', 'logFileEnable'); ?>
<?php $this->showConfigOption('settings_logFileRotation', 'logFileRotation', array('h'=>'hourly', 'd'=>'daily', 'm'=>'monthly'), false, true); ?>
<?php $this->showConfigCheckbox('settings_enableLargeFileUpload', 'enableLargeFileUpload'); ?>
<?php $this->showConfigText('settings_partitionSize', 'partitionSize'); ?>
<?php $this->showConfigText('settings_maxUploadSize', 'maxUploadSize'); ?>
      <!--
        -- SETTINGS - SYSTEM - AUTHENTICATION
      -->
<?php $this->showConfigHeadline('settings_Authentication'); ?>
<?php $this->showConfigCheckbox('settings_enableGuestLogin', 'enableGuestLogin'); ?>
<?php $this->showConfigCheckbox('settings_enableGuestAutoLogin', 'enableGuestAutoLogin'); ?>
<?php $this->showConfigCheckbox('settings_restricted', 'restricted'); ?>
<?php $this->showConfigCheckbox('settings_enableUserImage', 'enableUserImage'); ?>
<?php $this->showConfigCheckbox('settings_disableSelfEdit', 'disableSelfEdit'); ?>
<?php $this->showConfigCheckbox('settings_enablePasswordForgotten', 'enablePasswordForgotten'); ?>
<?php $this->showConfigText('settings_passwordStrength', 'passwordStrength'); ?>
<?php $this->showConfigOption('settings_passwordStrengthAlgorithm', 'passwordStrengthAlgorithm', array('simple'=>'settings_passwordStrengthAlgorithm_valsimple', 'advanced'=>'settings_passwordStrengthAlgorithm_valadvanced'), false, true); ?>
<?php $this->showConfigText('settings_passwordExpiration', 'passwordExpiration'); ?>
<?php $this->showConfigText('settings_passwordHistory', 'passwordHistory'); ?>
<?php $this->showConfigText('settings_loginFailure', 'loginFailure'); ?>
<?php $this->showConfigText('settings_autoLoginUser', 'autoLoginUser'); ?>
<?php $this->showConfigText('settings_quota', 'quota'); ?>
<?php $this->showConfigText('settings_undelUserIds', 'undelUserIds'); ?>
<?php $this->showConfigText('settings_encryptionKey', 'encryptionKey'); ?>
<?php $this->showConfigText('settings_cookieLifetime', 'cookieLifetime'); ?>
<?php $this->showConfigOption('settings_defaultAccessDocs', 'defaultAccessDocs', array(' 0'=>'inherited', ' '.M_NONE=>'access_mode_none', ' '.M_READ=>'access_mode_read', ' '.M_READWRITE=>'access_mode_readwrite'), false, true); ?>

      <!-- TODO Connectors -->

     <!--
        -- SETTINGS - SYSTEM - DATABASE
      -->
<?php $this->showConfigHeadline('settings_Database'); ?>
<?php $this->showConfigText('settings_dbDriver', 'dbDriver'); ?>
<?php $this->showConfigText('settings_dbHostname', 'dbHostname'); ?>
<?php $this->showConfigText('settings_dbDatabase', 'dbDatabase'); ?>
<?php $this->showConfigText('settings_dbUser', 'dbUser'); ?>
      <tr title="<?php printMLText("settings_dbPass_desc");?>">
        <td><?php printMLText("settings_dbPass");?>:</td>
        <td><?php $this->showTextField("dbPass", $settings->_dbPass, 'password'); ?></td>
      </tr>

     <!--
        -- SETTINGS - SYSTEM - SMTP
      -->
<?php $this->showConfigHeadline('settings_SMTP'); ?>
<?php $this->showConfigText('settings_smtpServer', 'smtpServer'); ?>
<?php $this->showConfigText('settings_smtpPort', 'smtpPort'); ?>
<?php $this->showConfigText('settings_smtpSendFrom', 'smtpSendFrom'); ?>
<?php $this->showConfigText('settings_smtpUser', 'smtpUser'); ?>
      <tr title="<?php printMLText("settings_smtpPassword_desc");?>">
        <td><?php printMLText("settings_smtpPassword");?>:</td>
        <td><input type="password" name="smtpPassword" value="<?php echo $settings->_smtpPassword ?>" /></td>
      </tr>

    </table>
<?php		$this->contentContainerEnd(); ?>
  </div>

	  <div class="tab-pane <?php if($currenttab == 'advanced') echo 'active'; ?>" id="advanced">
<?php		$this->contentContainerStart(); ?>
    <table class="table-condensed">
      <!--
        -- SETTINGS - ADVANCED - DISPLAY
      -->
<?php $this->showConfigHeadline('settings_Display'); ?>
<?php $this->showConfigText('settings_siteDefaultPage', 'siteDefaultPage'); ?>
<?php $this->showConfigText('settings_rootFolderID', 'rootFolderID'); ?>
<?php $this->showConfigCheckbox('settings_titleDisplayHack', 'titleDisplayHack'); ?>
<?php $this->showConfigCheckbox('settings_showMissingTranslations', 'showMissingTranslations'); ?>

      <!--
        -- SETTINGS - ADVANCED - AUTHENTICATION
      -->
<?php $this->showConfigHeadline('settings_Authentication'); ?>
<?php $this->showConfigText('settings_guestID', 'guestID'); ?>
<?php $this->showConfigText('settings_adminIP', 'adminIP'); ?>

      <!--
        -- SETTINGS - ADVANCED - EDITION
      -->
<?php $this->showConfigHeadline('settings_Edition'); ?>
<?php $this->showConfigOption('settings_workflowMode', 'workflowMode', array('traditional'=>'settings_workflowMode_valtraditional', 'traditional_only_approval'=>'settings_workflowMode_valtraditional_only_approval', 'advanced'=>'settings_workflowMode_valadvanced'), false, true); ?>
<?php $this->showConfigText('settings_versioningFileName', 'versioningFileName'); ?>
<?php $this->showConfigText('settings_presetExpirationDate', 'presetExpirationDate'); ?>
<?php $this->showConfigCheckbox('settings_allowReviewerOnly', 'allowReviewerOnly'); ?>
<?php $this->showConfigCheckbox('settings_enableAdminRevApp', 'enableAdminRevApp'); ?>
<?php $this->showConfigCheckbox('settings_enableOwnerRevApp', 'enableOwnerRevApp'); ?>
<?php $this->showConfigCheckbox('settings_enableSelfRevApp', 'enableSelfRevApp'); ?>
<?php $this->showConfigCheckbox('settings_enableUpdateRevApp', 'enableUpdateRevApp'); ?>
<?php $this->showConfigCheckbox('settings_enableVersionDeletion', 'enableVersionDeletion'); ?>
<?php $this->showConfigCheckbox('settings_enableVersionModification', 'enableVersionModification'); ?>
<?php $this->showConfigCheckbox('settings_enableDuplicateDocNames', 'enableDuplicateDocNames'); ?>
<?php $this->showConfigCheckbox('settings_overrideMimeType', 'overrideMimeType'); ?>
<?php $this->showConfigCheckbox('settings_removeFromDropFolder', 'removeFromDropFolder'); ?>

      <!--
        -- SETTINGS - ADVANCED - NOTIFICATION
      -->
<?php $this->showConfigHeadline('settings_Notification'); ?>
<?php $this->showConfigCheckbox('settings_enableOwnerNotification', 'enableOwnerNotification'); ?>
<?php $this->showConfigCheckbox('settings_enableNotificationAppRev', 'enableNotificationAppRev'); ?>
<?php $this->showConfigCheckbox('settings_enableNotificationWorkflow', 'enableNotificationWorkflow'); ?>

      <!--
        -- SETTINGS - ADVANCED - SERVER
      -->
<?php $this->showConfigHeadline('settings_Server'); ?>
<?php $this->showConfigText('settings_coreDir', 'coreDir'); ?>
<?php $this->showConfigText('settings_luceneClassDir', 'luceneClassDir'); ?>
<?php $this->showConfigText('settings_extraPath', 'extraPath'); ?>
<?php $this->showConfigText('settings_contentOffsetDir', 'contentOffsetDir'); ?>
<?php $this->showConfigText('settings_maxDirID', 'maxDirID'); ?>
<?php $this->showConfigText('settings_updateNotifyTime', 'updateNotifyTime'); ?>
<?php $this->showConfigText('settings_maxExecutionTime', 'maxExecutionTime'); ?>
<?php $this->showConfigText('settings_cmdTimeout', 'cmdTimeout'); ?>

<?php
  foreach(array('fulltext', 'preview', 'pdf') as $target) {
		$this->showConfigHeadline($target."_converters");
		if(!empty($settings->_converters[$target])) {
			foreach($settings->_converters[$target] as $mimetype=>$cmd) {
?>
      <tr title="<?php echo $mimetype;?>">
        <td><?php echo $mimetype;?>:</td>
        <td><?php $this->showTextField("converters[".$target."][".$mimetype."]", htmlspecialchars($cmd)); ?></td>
      </tr>
<?php
			}
		}
?>
      <tr title="">
        <td><?php $this->showTextField("converters[".$target."][newmimetype]", "", '', getMLText('converter_new_mimetype')); ?>:</td>
        <td><?php $this->showTextField("converters[".$target."][newcmd]", "", "", getMLText('converter_new_cmd')); ?></td>
      </tr>
<?php
	}
?>
    </table>
<?php		$this->contentContainerEnd(); ?>
  </div>

  <div class="tab-pane <?php if($currenttab == 'extensions') echo 'active'; ?>" id="extensions">
<?php		$this->contentContainerStart(); ?>
    <table class="table-condensed">
      <!--
        -- SETTINGS - ADVANCED - DISPLAY
      -->
<?php
	foreach($GLOBALS['EXT_CONF'] as $extname=>$extconf) {
		$this->showRawConfigHeadline("<a name=\"".$extname."\"></a>".$extconf['title']);
		foreach($extconf['config'] as $confkey=>$conf) {
?>
      <tr title="<?php echo isset($conf['help']) ? $conf['help'] : '';?>">
        <td><?php echo $conf['title'];?>:</td><td>
<?php
						switch($conf['type']) {
							case 'checkbox':
?>
        <input type="checkbox" name="<?php echo "extensions[".$extname."][".$confkey."]"; ?>" value="1" <?php if(isset($settings->_extensions[$extname][$confkey]) && $settings->_extensions[$extname][$confkey]) echo 'checked'; ?> />
<?php
								break;
							case 'select':
								if(!empty($conf['options'])) {
									$selections = explode(",", $settings->_extensions[$extname][$confkey]);
									echo "<select class=\"chzn-select\" name=\"extensions[".$extname."][".$confkey."][]\"".(!empty($conf['multiple']) ? "  multiple" : "").(!empty($conf['size']) ? "  size=\"".$conf['size']."\"" : "").">";
									foreach($conf['options'] as $key=>$opt) {
										echo "<option value=\"".$key."\"";
										if(in_array($key, $selections, true))
											echo " selected";
										echo ">".htmlspecialchars($opt)."</option>";
									}
									echo "</select>";
								} elseif(!empty($conf['internal'])) {
									$selections = empty($settings->_extensions[$extname][$confkey]) ? array() : explode(",", $settings->_extensions[$extname][$confkey]);
									$allowempty = empty($conf['allow_empty']) ? false : $conf['allow_empty'];
									switch($conf['internal']) {
									case "categories":
										$categories = $dms->getDocumentCategories();
										if($categories) {
											echo "<select class=\"chzn-select".($allowempty ? "-deselect" : "")."\" name=\"extensions[".$extname."][".$confkey."][]\"".(!empty($conf['multiple']) ? "  multiple" : "").(!empty($conf['size']) ? "  size=\"".$conf['size']."\"" : "")." data-placeholder=\"".getMLText("select_category")."\">";
											if($allowempty)
												echo "<option value=\"\"></option>";
											foreach($categories as $category) {
												echo "<option value=\"".$category->getID()."\"";
												if(in_array($category->getID(), $selections))
													echo " selected";
												echo ">".htmlspecialchars($category->getName())."</option>";
											}
											echo "</select>";
										}
										break;
									case "users":
										$users = $dms->getAllUsers();
										if($users) {
											echo "<select class=\"chzn-select".($allowempty ? "-deselect" : "")."\" name=\"extensions[".$extname."][".$confkey."][]\"".(!empty($conf['multiple']) ? "  multiple" : "").(!empty($conf['size']) ? "  size=\"".$conf['size']."\"" : "")." data-placeholder=\"".getMLText("select_user")."\">";
											if($allowempty)
												echo "<option value=\"\"></option>";
											foreach($users as $curuser) {
												echo "<option value=\"".$curuser->getID()."\"";
												if(in_array($curuser->getID(), $selections))
													echo " selected";
												echo ">".htmlspecialchars($curuser->getLogin()." - ".$curuser->getFullName())."</option>";
											}
											echo "</select>";
										}
										break;
									case "groups":
										$recs = $dms->getAllGroups();
										if($recs) {
											echo "<select class=\"chzn-select".($allowempty ? "-deselect" : "")."\" name=\"extensions[".$extname."][".$confkey."][]\"".(!empty($conf['multiple']) ? "  multiple" : "").(!empty($conf['size']) ? "  size=\"".$conf['size']."\"" : "")." data-placeholder=\"".getMLText("select_group")."\">";
											if($allowempty)
												echo "<option value=\"\"></option>";
											foreach($recs as $rec) {
												echo "<option value=\"".$rec->getID()."\"";
												if(in_array($rec->getID(), $selections))
													echo " selected";
												echo ">".htmlspecialchars($rec->getName())."</option>";
											}
											echo "</select>";
										}
										break;
									case "attributedefinitions":
										$recs = $dms->getAllAttributeDefinitions();
										if($recs) {
											echo "<select class=\"chzn-select".($allowempty ? "-deselect" : "")."\" name=\"extensions[".$extname."][".$confkey."][]\"".(!empty($conf['multiple']) ? "  multiple" : "").(!empty($conf['size']) ? "  size=\"".$conf['size']."\"" : "")." data-placeholder=\"".getMLText("select_attribute_value")."\">";
											if($allowempty)
												echo "<option value=\"\"></option>";
											foreach($recs as $rec) {
												echo "<option value=\"".$rec->getID()."\"";
												if(in_array($rec->getID(), $selections))
													echo " selected";
												echo ">".htmlspecialchars($rec->getName())."</option>";
											}
											echo "</select>";
										}
										break;
									}
								}
								break;
							default:
								$this->showTextField("extensions[".$extname."][".$confkey."]", isset($settings->_extensions[$extname][$confkey]) ? $settings->_extensions[$extname][$confkey] : '', '', '');
								/*
?>
        <input type="text" name="<?php echo "extensions[".$extname."][".$confkey."]"; ?>" title="<?php echo isset($conf['help']) ? $conf['help'] : ''; ?>" value="<?php if(isset($settings->_extensions[$extname][$confkey])) echo $settings->_extensions[$extname][$confkey]; ?>" <?php echo isset($conf['size']) ? 'size="'.$conf['size'].'"' : ""; ?>" />
<?php
*/
						}
?>
      </td></tr>
<?php
					}
				}
?>
		</table>
<?php		$this->contentContainerEnd(); ?>
  </div>
  </div>
<?php
if(is_writeable($settings->_configFilePath)) {
?>
  <button type="submit" class="btn"><i class="icon-save"></i> <?php printMLText("save")?></button>
<?php
}
?>
	</form>


<?php
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
