<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class tab_dna_KT_Module extends KT_Module implements KT_Module_Tab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('DNA connections');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the "Facts and events" module */ KT_I18N::translate('A tab listing all recorded DNA links for an individual.');
	}

	// Implement KT_Module_Tab
	public function defaultTabOrder() {
		return 250;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return false;
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'add-dna':
			$this->addDNA('add');
			break;
		case 'edit-dna':
			$this->addDNA('edit');
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Tab
	public function getTabContent() {
		global $controller;

		self::updateSchema(); // make sure the favorites table has been created

		$person			= $controller->getSignificantIndividual();
		$fullname		= $controller->record->getFullName();
		$xref			= $controller->record->getXref();

		$controller->addExternalJavascript(KT_JQUERY_DATATABLES_URL);
		if (KT_USER_CAN_EDIT) {
			$controller
				->addExternalJavascript(KT_JQUERY_DT_HTML5)
				->addExternalJavascript(KT_JQUERY_DT_BUTTONS);
		}
		$controller->addInlineJavascript('
			jQuery("#dnaTable").dataTable({
				"sDom": \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
				' . KT_I18N::datatablesI18N() . ',
				buttons: [{extend: "csv", exportOptions: {}}],
				jQueryUI: true,
				autoWidth: false,
				displayLength: 30,
				pagingType: "full_numbers",
				sorting: [[2,"desc"]],
				columns: [
					/*  0 name				*/ { },
					/*  1 relationship		*/ { },
					/*  2 cms				*/ { className: "dt-body-right" },
					/*  3 segments			*/ { className: "dt-body-right" },
					/*  4 common ancestor	*/ null,
					/*  5 source			*/ null,
					/*  6 note				*/ null,
					/*  7 date added		*/ null,
					/*  8 edit				*/ { className: "dt-body-center" },
					/*  9 delete			*/ { className: "dt-body-center" },
				],
				stateSave: true,
				stateDuration: -1,
			});
		');
		?>

		<style>
			#dnaTable {font-size: 90%; width:100%;}
			#dnaTable a {color: #3383bb;}
			#dnaTable th {font-weight: 600; height: 25px; padding: 2px 4px; white-space: nowrap;}
			#dnaTable td {padding: 6px;}
			#dnaTable td.dt-body-right {text-align: right; padding-right: 15px;}
			#dnaTable td.dt-body-center {text-align: center;}
		</style>
		<div id="tab_dna_content">
			<!-- Show header Links -->
			<?php if (KT_USER_CAN_EDIT) { ?>
				<div class="descriptionbox rela">
					<span>
						<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=add-dna&amp;pid=<?php echo $xref; ?>&amp;ged=<?php echo KT_GEDCOM; ?>" target="_blank">
							<i style="margin: 0 3px 0 10px;" class="icon-image_add"></i>
							<?php echo KT_I18N::translate('Add DNA data'); ?>
						</a>
					</span>
				</div>
			<?php } ?>
			<?php if ($person && $person->canDisplayDetails()) { ?>
				<h3><?php echo KT_I18N::translate('Recorded DNA connections for %s', $fullname); ?></h3>
			<?php } ?>
			<table id="dnaTable">
				<thead>
					<tr>
						<th><?php echo KT_I18N::translate('Name'); ?></th>
						<th><?php echo KT_I18N::translate('Relationship'); ?></th>
						<th><?php echo KT_I18N::translate('cMs'); ?></th>
						<th><?php echo KT_I18N::translate('Segments'); ?></th>
						<th><?php echo KT_I18N::translate('Common ancestors'); ?></th>
						<th><?php echo KT_I18N::translate('Source'); ?></th>
						<th><?php echo KT_I18N::translate('Note'); ?></th>
						<th><?php echo KT_I18N::translate('Date added'); ?></th>
						<th><?php echo KT_I18N::translate('Edit'); ?></th>
						<?php //-- Select & delete
						if (KT_USER_GEDCOM_ADMIN) { ?>
							<th>
								<div class="delete_dna">
									<?php echo '
										<input type="button" value="'. KT_I18N::translate('Delete'). '" onclick="if (confirm(\''. htmlspecialchars(KT_I18N::translate('Permanently delete these records?')). '\')) {return checkbox_delete(\'dna\', \'' . $xref . '\');} else {return false;}">
										<input type="checkbox" onClick="toggle_select(this)" style="vertical-align:middle;">
									' ?>
								</div>
							</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php $rows = $this->getData($xref);
					foreach ($rows as $row) {
						($xref == $row->id_a) ? $personA = KT_Person::getInstance($row->id_b) : $personA = KT_Person::getInstance($row->id_a);
						if (!$personA) {continue;} ?>
						<tr>
							<td>
								<a href="<?php echo $personA->getHtmlUrl(); ?>" target="_blank">
									<?php echo $personA->getFullName(); ?>
								</a>
							</td>
							<td>
								<?php $relationship = ucfirst($this->findRelationship($person, $personA));
								if ($relationship){ ?>
									<a href="relationship.php?pid1=<?php echo $person->getXref(); ?>&amp;pid2=<?php echo $personA->getXref(); ?>&amp;ged=<?php echo KT_GEDCOM; ?>&amp;find=1" target="_blank">
										<?php echo $relationship; ?>
									</a>
								<?php } else {
									echo KT_I18N::translate('No relationship found');
								} ?>
							</td>
							<td><?php echo $row->cms; ?></td>
							<td><?php echo $row->seg; ?></td>
							<td>
								<?php echo $this->findCommonAncestor($person, $personA); ?>
							<td>
								<?php $source = KT_Source::getInstance($row->source);
								if ($source) { ?>
									<a href="<?php echo $source->getHtmlUrl(); ?>" target="_blank">
										<?php echo$source->getFullName(); ?>
									</a>
								<?php } ?>
							</td>
							<td><?php echo $row->note; ?></td>
							<td>
								<?php echo timestamp_to_gedcom_date(strtotime($row->date))->Display(); ?>
							</td>
							<td>
								<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=edit-dna&amp;pid=<?php echo $xref; ?>&amp;ged=<?php echo KT_GEDCOM; ?>&amp;dna-id=<?php echo $row->dna_id; ?>" target="_blank" title="<?php echo KT_I18N::translate('Edit DNA data'); ?>">
									<i style="margin: 0 3px 0 10px;" class="icon-edit"></i>
								</a>
							</td>
							<?php //-- Select & delete
							if (KT_USER_GEDCOM_ADMIN) { ?>
								<td>
									<div class="delete_src">
										<input type="checkbox" name="del_places[]" class="check" value="<?php echo $row->dna_id; ?>" title="<?php echo KT_I18N::translate('Delete'); ?>">
									</div>
								</td>
							<?php } ?>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	// Implement KT_Module_Tab
	public function addDNA($type) {
		global $controller;
		require KT_ROOT . 'includes/functions/functions_edit.php';

		$pid		= KT_Filter::get('pid');
		$person		= KT_Person::getInstance($pid);
		$fullname	= $person->getFullName();
		$xref		= $person->getXref();
		$action		= KT_Filter::post('action');
		$dna_id_b	= $cms = $seg = $source = $note = '';

		$controller	= new KT_Controller_Page;
		$controller
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('autocomplete();');

		switch ($type) {
			case 'add':
				$controller->setPageTitle(KT_I18N::translate('Add DNA data') . ' - ' . $person->getLifespanName());
				if ($action == 'update_dna') {
					$dna_id_a	= $pid;
					$dna_id_b	= KT_Filter::post('dna_id_b');
					$cms		= KT_Filter::post('cms');
					$seg		= KT_Filter::post('seg');
					$source		= KT_Filter::post('source');
					$note		= KT_Filter::post('note');

					KT_DB::prepare(
						"INSERT INTO `##dna` (id_a, id_b, cms, seg, source, note) VALUES (?, ?, ?, ?, ?, ?)"
					)->execute(array(
						$dna_id_a, $dna_id_b, $cms, $seg, $source, $note
					));

					echo "
						<script>
							opener.location.reload();
							window.close();
						</script>
					";

				}
				break;

			case 'edit':
				$controller->setPageTitle(KT_I18N::translate('Edit DNA data') . ' - ' . $person->getLifespanName());
				$dna_id_b	= $cms = $seg = $source = $note = '';

				$dna_id		= KT_Filter::get('dna-id');
				$row		= $this->getData($pid, $dna_id);
				$dna_id_b	= KT_Filter::post('id_b', NULL, $row->id_b);
				$cms		= KT_Filter::post('cms', NULL, $row->cms);
				$seg		= KT_Filter::post('seg', NULL, $row->seg);
				$source		= KT_Filter::post('source', NULL, $row->source);
				$note		= KT_Filter::post('note', NULL, $row->note);

				$person_b	= KT_Person::getInstance($dna_id_b, KT_GED_ID);
				$source_b	= KT_Source::getInstance($source, KT_GED_ID);

				if ($action == 'update_dna') {

					KT_DB::prepare(
						"UPDATE `##dna`
							SET
								id_b		= ?,
								cms			= ?,
								seg			= ?,
								source		= ?,
								note		= ?
							WHERE dna_id	= ?
						"
					)->execute(array($dna_id_b, $cms, $seg, $source, $note, $dna_id));

					echo "
						<script>
							opener.location.reload();
							window.close();
						</script>
					";

				}
				break;
		}


		?>
		<div id="edit_interface-page">
			<h2><?php echo $controller->getPageTitle(); ?></h2>
			<form name="adddna_form" method="post" action="">
				<input type="hidden" name="action" value="update_dna">
				<input type="hidden" name="pid" value="<?php echo $pid; ?>">
				<div id="add_facts">
					<div id="adddna1_factdiv">
						<label><?php echo KT_I18N::translate('Person connected by DNA'); ?></label>
						<div class="input">
							<input class="addDna_form" data-autocomplete-type="INDI" type="text" name="dna_id_b" id="dna_id_b" value="<?php echo $dna_id_b; ?>" autocomplete="off">
							<div class="autocomplete_label"><?php echo $dna_id_b ? $person_b->getLifespanName() : ''; ?></div>
						</div>
					</div>
					<div id="adddna2_factdiv">
						<label><?php echo KT_I18N::translate('CentiMorgans'); ?></label>
						<div class="input">
							<input class="addDna_form" type="text" name="cms" id="cms" value="<?php echo $cms; ?>">
						</div>
					</div>
					<div id="adddna3_factdiv">
						<label><?php echo KT_I18N::translate('Segments'); ?></label>
						<div class="input">
							<input class="addDna_form" type="text" name="seg" id="seg" value="<?php echo $seg; ?>">
						</div>
					</div>
					<div id="adddna4_factdiv">
						<label><?php echo KT_I18N::translate('Source'); ?></label>
						<div class="input">
							<input class="addDna_form" data-autocomplete-type="SOUR" type="text" name="source" id="source" value="<?php echo $source; ?>" autocomplete="off">
							<a href="#" onclick="addnewsource(document.getElementById('SOUR')); return false;" class="icon-button_addsource" title="Create a new source"></a>
							<div class="autocomplete_label"><?php echo $source ? strip_tags($source_b->getFullName()) : ''; ?></div>
						</div>
					</div>
					<div id="adddna5_factdiv">
						<label><?php echo KT_I18N::translate('Note'); ?></label>
						<div class="input">
							<textarea id="note" name="note" style="overflow: hidden; overflow-wrap: break-word; resize: horizontal; height: 32px;" value="<?php echo $note; ?>"></textarea>
						</div>
					</div>
				</div>
				<p id="save-cancel">
					<button class="btn btn-primary" type="submit">
						<i class="fa fa-save"></i>
						<?php echo KT_I18N::translate('Save'); ?>
					</button>
					<button class="btn btn-primary" type="button" onclick="window.close();">
						<i class="fa fa-times"></i>
						<?php echo KT_I18N::translate('close'); ?>
					</button>
				</p>
			</form>
		</div> <!-- id="edit_interface-page" -->
		<?php
	}

	// Implement KT_Module_Tab
	public function hasTabContent() {
		return KT_USER_CAN_EDIT || count($this->getData());
	}

	// Implement KT_Module_Tab
	public function isGrayedOut() {
		return count($this->getData()) == 0;
	}

	// Implement KT_Module_Tab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	// get data from ##dna table for specific individual
	public function getData($xref = false, $id = false) {
		global $controller;

		self::updateSchema(); // make sure the favorites table has been created

		if (!$xref) {
			$xref = $controller->record->getXref();
		}

		if ($id) {
			$sql	= "SELECT * FROM `##dna` WHERE dna_id=?";
			$arr	= array($id);
			$rows	= KT_DB::prepare($sql)->execute($arr)->fetchOneRow();
		} else {
			$sql	= "SELECT * FROM `##dna` WHERE id_a=? OR id_b=? AND id_a <> id_b";
			$arr	= array($xref, $xref);
			$rows	= KT_DB::prepare($sql)->execute($arr)->fetchAll();
		}

		return $rows;

	}


	// check relationship between two individuals
	public function findRelationship($person, $personA) {
		$controller	 = new KT_Controller_Relationship();
		$paths		 = $controller->calculateRelationships_123456($person, $personA, 1, 0);
		foreach ($paths as $path) {
			$relationships = $controller->oldStyleRelationshipPath($path);
			if (empty($relationships)) {
				// Cannot see one of the families/individuals, due to privacy;
				continue;
			}
			return get_relationship_name_from_path(implode('', $relationships), $person, $personA);

		}
	}

	// find common ancestor for two individuals
	public function findCommonAncestor($person, $personA) {
		global $GEDCOM_ID_PREFIX;
		$slcaController = new KT_Controller_Relationship;
		$caAndPaths = $slcaController->calculateCaAndPaths_123456($person, $personA, 1, 0, false);
		$html = '';
		foreach ($caAndPaths as $caAndPath) {
			$slcaKey = $caAndPath->getCommonAncestor();
			if (substr($slcaKey, 0, 1) === $GEDCOM_ID_PREFIX) {
				$indi = KT_Person::getInstance($slcaKey, KT_GED_ID);
				if (($person !== $indi) && ($personA !== $indi)) {
					$html = '';
					$html .= '<a href="' . $indi->getHtmlUrl() . '" title="' . strip_tags($indi->getFullName()) . '">';
					$html .= highlight_search_hits($indi->getFullName()) . '</a>';
				}
			} else {
				$fam = KT_Family::getInstance($slcaKey, KT_GED_ID);
				$names = array();
				foreach ($fam->getSpouses() as $indi) {
					$html = '';
					$html .= '<a href="' . $indi->getHtmlUrl() . '" title="' . strip_tags($indi->getFullName()) . '">';
					$html .= highlight_search_hits($indi->getFullName()) . '</a>';

					$names[] = $indi->getFullName();
				}
				$famName = implode(' & ', $names);
				$html = '';
				$html .= '<a href="' . $fam->getHtmlUrl() . '" title="' . strip_tags($famName) . '">';
				$html .= highlight_search_hits($famName) . '</a>';
			}
		}

		if ($html) {
			return $html;
		} else {
			return KT_I18N::translate('No common ancestor found');
		}

	}

	protected static function updateSchema() {
		// Create tables, if not already present
		try {
			KT_DB::updateSchema(KT_ROOT . KT_MODULES_DIR . 'tab_dna/db_schema/', 'DNA_SCHEMA_VERSION', 1);
		} catch (PDOException $ex) {
			// The schema update scripts should never fail.  If they do, there is no clean recovery.
			die($ex);
		}
	}

}
