<?php
/**
 * Implementation of WorkspaceMgr view
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
 * Class which outputs the html page for WorkspaceMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_WorkflowGraph extends SeedDMS_Bootstrap_Style {

	function js() { /* {{{ */
		$this->workflow = $this->params['workflow'];
		header('Content-Type: application/javascript; charset=UTF-8');

		$renderdata = '';
?>
var cy = cytoscape({
	container: document.getElementById('canvas'),

	style: [
	{
		selector: 'node',
		style: {
			'content': 'data(name)',
			'height': 40,
			'width': 40,
			'text-valign': 'top',
			'text-halign': 'center',
//			'color': '#fff',
			'background-color': '#11479e',
//			'text-outline-color': '#11479e',
//			'text-outline-width': '3px',
//			'font-size': '10px',
			'text-wrap': 'wrap'
		}
	},

	{
		selector: 'node.action',
		style: {
			'shape': 'roundrectangle',
			'height': 30,
			'width': 30,
			'background-color': '#91479e',
//			'text-outline-color': '#91479e'
		}
	},

	{
		selector: 'node.init',
		style: {
			'background-color': '#ff9900',
//			'text-outline-color': '#b06000'
		}
	},

	{
		selector: 'node.released',
		style: {
			'background-color': '#00b000',
			'text-valign': 'bottom',
			'text-margin-y': '3px',
//			'text-outline-color': '#00b000'
		}
	},

	{
		selector: 'node.rejected',
		style: {
			'background-color': '#b00000',
			'text-valign': 'bottom',
			'text-margin-y': '3px',
//			'text-outline-color': '#b00000'
		}
	},

	{
		selector: 'edge',
		style: {
			'width': 4,
			'curve-style': 'bezier',
			'target-arrow-shape': 'triangle',
			'line-color': '#9dbaea',
			'target-arrow-color': '#9dbaea',
			'curve-style': 'bezier'
		}
	}]
<?php if($renderdata) echo ",".$renderdata; ?>
}

);

cy.on('free', 'node', function(evt) {
	$('#png').attr('src', cy.png({'full': true}));
});

cy.on('tap', 'node', function(evt) {
	var node = evt.cyTarget;
	var scratch = node.scratch('app');
	noty({
		text: (scratch.users ? '<p><?php printMLText('users'); ?>: ' + scratch.users + '</p>' : '') + (scratch.groups ? '<?php printMLText('groups'); ?>: ' + scratch.groups + '</p>' : ''),
		type: 'information',
		dismissQueue: true,
		layout: 'topCenter',
		theme: 'defaultTheme',
		timeout: 4000,
		killer: true,
	});
});
<?php
		if(!$renderdata)
			$this->printGraph();
?>
	cy.layout({ name: '<?php echo $renderdata ? 'preset' : 'cose'; ?>', condense: true, ready: function() {$('#png').attr('src', cy.png({'full': true}))} });
//	$('#png').attr('src', cy.png({'full': true}));

$(document).ready(function() {
	$('body').on('click', '#setlayout', function(ev){
		ev.preventDefault();
		var element = $(this);
		cy.layout({name: element.data('layout'), ready: function() {$('#png').attr('src', cy.png({'full': true}))}});
	});
});
<?php
	} /* }}} */

	function printGraph() { /* {{{ */
		$transitions = $this->workflow->getTransitions();	
		if($transitions) {

			$this->seentrans = array();
			$this->states = array();
			$this->actions = array();
			foreach($transitions as $transition) {
				$action = $transition->getAction();
				$maxtime = $transition->getMaxTime();
				$state = $transition->getState();
				$nextstate = $transition->getNextState();

				if(1 || !isset($this->actions[$action->getID()])) {
					$color = "#4B4";
					$iscurtransition = $this->curtransition && $transition->getID() == $this->curtransition->getID();
					if($iscurtransition) {
						$color = "#D00";
					} else {
						if($this->wkflog) {
							foreach($this->wkflog as $entry) {
								if($entry->getTransition()->getID() == $transition->getID()) {
									$color = "#DDD";
									break;
								}
							}
						}
					}
					$this->actions[$action->getID()] = $action->getID();
					$transusers = $transition->getUsers();
					$unames = array();
					foreach($transusers as $transuser) {
						$unames[] = $transuser->getUser()->getFullName();
					}
					$transgroups = $transition->getGroups();
					$gnames = array();
					foreach($transgroups as $transgroup) {
						$gnames[] = $transgroup->getGroup()->getName();
					}
					echo "cy.add({
						data: {
							id: 'A".$transition->getID()."-".$action->getID()."',
							name: \"".str_replace('"', "\\\"", $action->getName())/*.($unames ? "\\n(".str_replace('"', "\\\"", implode(", ", $unames)).")" : '').($gnames ? "\\n(".str_replace('"', "\\\"", implode(", ", $gnames)).")" : '')*/."\"
						},
						classes: 'action',
						scratch: {
							app: {groups: '".implode(", ", $gnames)."', users: '".implode(", ", $unames)."'}
						}
					});\n";
				}

				if(!isset($this->states[$state->getID()])) {
					$this->states[$state->getID()] = $state;
					$initstate = '';
					if($state == $this->workflow->getInitState())
						$initstate = getMLText('workflow_initstate');
					echo "cy.add({
						data: {
							id: 'S".$state->getID()."',
							name: \"".str_replace('"', "\\\"", $state->getName()/*."\\n".$initstate*/)."\"
						},
						classes: 'state ".($state == $this->workflow->getInitState() ? 'init' : '')."'
					});\n";
				}
				if(!isset($this->states[$nextstate->getID()])) {
					$this->states[$nextstate->getID()] = $nextstate;
					$docstatus = $nextstate->getDocumentStatus();
					echo "cy.add({
						data: {
							id: 'S".$nextstate->getID()."',
							name: '".str_replace('"', "\\\"", $nextstate->getName())/*.($docstatus == S_RELEASED || $docstatus == S_REJECTED ? "\\n(".getOverallStatusText($docstatus).")" : '')*/."'
						},
						classes: 'state".($docstatus == S_RELEASED ? ' released' : ($docstatus == S_REJECTED ? ' rejected' : ''))."'
					});\n";
				}

			}

			foreach($transitions as $transition) {
				if(!in_array($transition->getID(), $this->seentrans)) {
					$state = $transition->getState();
					$nextstate = $transition->getNextState();
					$action = $transition->getAction();
					$iscurtransition = $this->curtransition && $transition->getID() == $this->curtransition->getID();

					echo "cy.add({
						data: {
							id: 'E1-".$transition->getID()."',
							source: 'S".$state->getID()."',
							target: 'A".$transition->getID()."-".$action->getID()."'
						}
					});\n";
					echo "cy.add({
						data: {
							id: 'E2-".$transition->getID()."',
							source: 'A".$transition->getID()."-".$action->getID()."',
							target: 'S".$nextstate->getID()."'
						}
					});\n";
					$this->seentrans[] = $transition->getID();
				}
			}
		}
?>
<?php
	} /* }}} */

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$this->workflow = $this->params['workflow'];
		$this->curtransition = $this->params['transition'];
		$document = $this->params['document'];

		if($document) {
			$latestContent = $document->getLatestContent();
			$this->wkflog = $latestContent->getWorkflowLog();
		} else {
			$this->wkflog = array();
		}

		$this->htmlAddHeader(
			'<script type="text/javascript" src="../styles/bootstrap/cytoscape/cytoscape.min.js"></script>'."\n");
		$this->htmlAddHeader('
<style type="text/css">
body {padding: 0px;}
div.buttons {float: right; padding-left: 4px; height: 100px; width: 120px; margin-right: 5px;}
div.buttons button {margin: 3px; float: right;}
#legend {display: inline-block; margin-left: 10px;}
#preview {height: 115px; background: #f5f5f5; border-top: 1px solid #e3e3e3;}
#preview img {float: left;border: 1px solid #bbb; background: #fff; min-height: 100px; min-width: 100px; height: 100px; _width: 100px; padding: 3px; margin: 3px;}
</style>
', 'css');
		$this->htmlStartPage(getMLText("admin_tools"));
//		$this->contentContainerStart();

?>
<div id="canvas" style="width: 100%; height:545px; _border: 1px solid #bbb;"></div>
<div id="preview">
	<img id="png" />
	<div id="legend">
		<i class="icon-circle" style="color: #ff9900;"></i> <?php printMLText("workflow_initstate"); ?><br />
		<i class="icon-circle" style="color: #00b000;"></i> <?php echo getOverallStatusText(S_RELEASED); ?><br />
		<i class="icon-circle" style="color: #b00000;"></i> <?php echo getOverallStatusText(S_REJECTED); ?><br />
		<i class="icon-circle" style="color: #11479e;"></i> <?php echo getOverallStatusText(S_IN_WORKFLOW); ?><br />
		<i class="icon-sign-blank" style="color: #91479e;"></i> <?php echo printMLText('global_workflow_actions'); ?>
	</div>
	<div class="buttons">
		<button class="btn btn-mini" id="setlayout" data-layout="cose">Redraw</button>
	</div>
</div>
<?php
//		$this->contentContainerEnd();
		if(method_exists($this, 'js'))
			echo '<script src="../out/out.'.$this->params['class'].'.php?action=js&'.$_SERVER['QUERY_STRING'].'"></script>'."\n";
		echo "</body>\n</html>\n";
	} /* }}} */
}
?>
