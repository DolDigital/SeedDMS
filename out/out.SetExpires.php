<?php
//    MyDMS. Document Management System
//    Copyright (C) 2002-2005  Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//    Copyright (C) 2010 Matteo Lucarelli
//
//    This program is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program; if not, write to the Free Software
//    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
include("../inc/inc.DBInit.php");
include("../inc/inc.Language.php");
include("../inc/inc.ClassUI.php");
include("../inc/inc.Authentication.php");

if (!isset($_GET["documentid"]) || !is_numeric($_GET["documentid"]) || intval($_GET["documentid"])<1) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("invalid_doc_id"));
}
$documentid = intval($_GET["documentid"]);
$document = $dms->getDocument($documentid);

if (!is_object($document)) {
	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("invalid_doc_id"));
}

$folder = $document->getFolder();
$docPathHTML = getFolderPathHTML($folder, true). " / <a href=\"../out/out.ViewDocument.php?documentid=".$documentid."\">".htmlspecialchars($document->getName())."</a>";

if ($document->getAccessMode($user) < M_READWRITE) {
	UI::exitError(getMLText("document_title", array("documentname" => htmlspecialchars($document->getName()))),getMLText("access_denied"));
}

UI::htmlStartPage(getMLText("document_title", array("documentname" => htmlspecialchars($document->getName()))));
UI::globalNavigation($folder);
UI::pageNavigation($docPathHTML, "view_document");
UI::contentHeading(getMLText("set_expiry"));
UI::contentContainerStart();

?>

<form action="../op/op.SetExpires.php" method="POST">
<input type="hidden" name="documentid" value="<?php print $documentid;?>">
	
<table>
<tr>
	<td><?php printMLText("expires");?>:</td>
	<td>
	<input type="radio" name="expires" value="false" <?php echo ($document->expires()?"":"checked") ?> ><?php printMLText("does_not_expire");?><br>
	<input type="radio" name="expires" value="true"  <?php echo ($document->expires()?"checked":"") ?> ><?php UI::printDateChooser(($document->expires()?$document->getExpires():-1), "exp");?>
	</td>
</tr>
</table>

<p>
<input type="Submit" value="<?php printMLText("update");?>">
</p>
	
</form>
<?php
UI::contentContainerEnd();
UI::htmlEndPage();
?>
