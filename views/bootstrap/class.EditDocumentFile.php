<?php
/**
 * Implementation of EditDocumentFile view
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
 * Class which outputs the html page for EditDocumentFile view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_EditDocumentFile extends SeedDMS_Bootstrap_Style {

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$document = $this->params['document'];
		$file = $this->params['file'];

		$this->htmlStartPage(getMLText("document_title", array("documentname" => htmlspecialchars($document->getName()))));
		$this->globalNavigation($folder);
		$this->contentStart();
		$this->pageNavigation($this->getFolderPathHTML($folder, true, $document), "view_document", $document);
		$this->contentHeading(getMLText("edit"));
		$this->contentContainerStart();

?>
<form action="../op/op.EditDocumentFile.php" class="form-horizontal" name="form1" method="post">
  <?php echo createHiddenFieldWithKey('editdocumentfile'); ?>
	<input type="hidden" name="documentid" value="<?php echo $document->getID()?>">
	<input type="hidden" name="fileid" value="<?php echo $file->getID()?>">
<?php
		$html = '<select name="version" id="version">
			<option value="">'.getMLText('document').'</option>';
		$versions = $document->getContent();
		foreach($versions as $version)
			$html .= "<option value=\"".$version->getVersion()."\"".($version->getVersion() == $file->getVersion() ? " selected" : "").">".getMLText('version')." ".$version->getVersion()."</option>";
		$html .= "</select>";
		$this->formField(
			getMLText("version"),
			$html
		);
		$this->formField(
			getMLText("name"),
			'<input name="name" type="text" value="'.htmlspecialchars($file->getName()).'"/>'
		);
		$this->formField(
			getMLText("comment"),
			'<textarea name="comment" rows="4" cols="80">'.htmlspecialchars($file->getComment()).'</textarea>'
		);
		$this->formField(
			getMLText("document_link_public"),
			'<input name="public" type="checkbox" value="true"'.($file->isPublic() ? " checked" : "").' />'
		);
?>
<?php
		$this->formSubmit("<i class=\"icon-save\"></i> ".getMLText('save'));
?>
</form>
<?php
		$this->contentContainerEnd();
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
