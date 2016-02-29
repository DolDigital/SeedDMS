<?php
/**
 * Implementation of Acl view
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
 * Class which outputs the html page for Acl view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2016 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_Acl extends SeedDMS_Bootstrap_Style {

	function js() { /* {{{ */
		$selrole = $this->params['selrole'];
		header('Content-Type: application/javascript');
?>
$('#acostree').tree({
	autoOpen: 0,
	saveState: 'acostree<?php echo $selrole->getID(); ?>',
	openedIcon: '<i class="icon-minus-sign"></i>',
	closedIcon: '<i class="icon-plus-sign"></i>',
	onCreateLi: function(node, $li) {
		switch(node.permission) {
			case "-1":
				$li.find('.jqtree-element span:last-child').after('<span style="position: absolute; right:10px;" class="jqtree-remove-permission" data-acoid="'+node.acoid+'" data-aroid="'+node.aroid+'"><i class="icon-minus-sign"></i></span> <span style="position: absolute; right:50px;" class="jqtree-toggle-permission" data-acoid="'+node.acoid+'" data-aroid="'+node.aroid+'"><i class="icon-exchange"></i></span>');
				$li.attr('style', 'background-color:#FDD');
				break;
			case "1":
				$li.find('.jqtree-element span:last-child').after('<span style="position: absolute; right:10px;" class="jqtree-remove-permission" data-acoid="'+node.acoid+'" data-aroid="'+node.aroid+'"><i class="icon-minus-sign"></i></span> <span style="position: absolute; right:50px;" class="jqtree-toggle-permission" data-acoid="'+node.acoid+'" data-aroid="'+node.aroid+'"><i class="icon-exchange"></i></span>');
				$li.attr('style', 'background-color:#DFD');
				break;
			default:
				$li.find('.jqtree-element span:last-child').after('<span style="position: absolute; right:10px;" class="jqtree-add-permission" data-acoid="'+node.acoid+'" data-aroid="'+node.aroid+'"><i class="icon-plus-sign"></i></span>');
		}
 }
});
$('#acostree').on('click', '.jqtree-toggle-permission', function(event) {
	acoid = $(event.target).parent().attr('data-acoid');
	aroid = $(event.target).parent().attr('data-aroid');
	$.ajax('../op/op.Acl.php?action=toggle_permission&acoid='+acoid+'&aroid='+aroid, {
		dataType: 'json',
		success: function(data, textStatus) {
			if(data.type == 'success')  {
				timeout = 1500;
				$('#acostree').tree('loadDataFromUrl');
			} else {
				timeout = 3500;
			}
			noty({text: data.msg, type: data.type, dismissQueue: true, layout: 'topRight', theme: 'defaultTheme', timeout: timeout});
		},
	});
});

$('#acostree').on('click', '.jqtree-add-permission', function(event) {
	acoid = $(event.target).parent().attr('data-acoid');
	aroid = $(event.target).parent().attr('data-aroid');
	$.ajax('../op/op.Acl.php?action=add_permission&acoid='+acoid+'&aroid='+aroid, {
		dataType: 'json',
		success: function(data, textStatus) {
			if(data.type == 'success')  {
				timeout = 1500;
				$('#acostree').tree('loadDataFromUrl');
			} else {
				timeout = 3500;
			}
			noty({text: data.msg, type: data.type, dismissQueue: true, layout: 'topRight', theme: 'defaultTheme', timeout: timeout});
		},
	});
});

$('#acostree').on('click', '.jqtree-remove-permission', function(event) {
	acoid = $(event.currentTarget).attr('data-acoid');
	aroid = $(event.currentTarget).attr('data-aroid');
	$.ajax('../op/op.Acl.php?action=remove_permission&acoid='+acoid+'&aroid='+aroid, {
		dataType: 'json',
		success: function(data, textStatus) {
			if(data.type == 'success')  {
				timeout = 1500;
				$('#acostree').tree('loadDataFromUrl');
			} else {
				timeout = 3500;
			}
			noty({text: data.msg, type: data.type, dismissQueue: true, layout: 'topRight', theme: 'defaultTheme', timeout: timeout});
		},
	});
});

$(document).ready( function() {
	$( "#selector" ).change(function() {
		window.location='out.Acl.php?action=show&roleid=' + $(this).val();
//		$('#acostree').tree({dataUrl: 'out.Acl.php?action=tree&roleid=' + $(this).val()});
	});
});
<?php
	} /* }}} */

	/**
	 * Show tree of acos
	 *
	 */
	private function _tree($aro=null, $aco=null) { /* {{{ */
		$children = array();
		$tchildren = $aco->getChildren();
		if($tchildren) {
			foreach($tchildren as $child) {
				$node = array();
				if(false === ($perm = $child->getPermission($aro)))
					$node['permission'] = 0;
				else
					$node['permission'] = $perm;
				$node['id'] = $child->getID();
				$node['label'] = $child->getAlias();
				$node['acoid'] = $child->getID();
				$node['aroid'] = $aro->getID();

				$nchildren = $this->_tree($aro, $child);
				if($nchildren) {
					$node['is_folder'] = true;
					$node['children'] = $nchildren;
				}
				$children[] = $node;
			}
		}
		return $children;
	} /* }}} */

	/**
	 * List all registered hooks
	 *
	 */
	public function tree() { /* {{{ */
		$dms = $this->params['dms'];
		$selrole = $this->params['selrole'];

		$result = array();
		if($selrole) {
			$aro = SeedDMS_Aro::getInstance($selrole, $dms);

			$acos = SeedDMS_Aco::getRoot($dms);
			foreach($acos as $aco) {
				if(false === ($perm = $aco->getPermission($aro)))
					$tree['permission'] = 0;
				else
					$tree['permission'] = $perm;
				$tree['id'] = $aco->getID();
				$tree['label'] = $aco->getAlias();
				$tree['acoid'] = $aco->getID();
				$tree['aroid'] = $aro->getID();
				$tree['is_folder'] = true;
				$tree['children'] = $this->_tree($aro, $aco);
				$result[] = $tree;
			}
		}
		echo json_encode($result);
	} /* }}} */

	public function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$roles = $this->params['allroles'];
		$selrole = $this->params['selrole'];

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");
?>
<div class="row-fluid">
<div class="span4">
<?php
		$this->contentHeading("Role");
?>
<select class="chzn-select" id="selector">
	<option value="-1"><?php echo getMLText("choose_role")?>
<?php
		foreach ($roles as $currRole) {
			print "<option value=\"".$currRole->getID()."\" ".($selrole && $currRole->getID()==$selrole->getID() ? 'selected' : '').">" . htmlspecialchars($currRole->getName());
		}
?>
</select>
</div>

<div class="span8">
<?php
		$this->contentHeading("Acl");
?>
	<div id="acostree" data-url="out.Acl.php?action=tree&roleid=<?= ($selrole ? $selrole->getID() : 0) ?>">Berechtigungen werden geladen ...</div>
</div>
</div>
<?php
		$this->htmlEndPage();
	} /* }}} */
}


