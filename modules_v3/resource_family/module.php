<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}
class resource_family_WT_Module extends WT_Module implements WT_Module_Resources {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ WT_I18N::translate('Family');
	}
	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Research tasks” module */ WT_I18N::translate('A report of family members and their details.');
	}
	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}
	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_PUBLIC;
	}
	// Implement WT_Module_Resources
	public function getResourceMenus() {
		global $controller;
		$fam_xref = $controller->getSignificantFamily()->getXref();
		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $fam_xref . '&amp;ged=' . WT_GEDURL,
			'menu-resources-' . $this->getName()
		);
		$menus[] = $menu;
		return $menus;
	}
	// Implement class WT_Module_Resources
	public function show() {
		global $controller, $GEDCOM;
		require WT_ROOT.'includes/functions/functions_resource.php';
		require WT_ROOT.'includes/functions/functions_edit.php';
		$controller = new WT_Controller_Family();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
			->addInlineJavascript('
				autocomplete();
				jQuery("#accordion")
					.accordion({event: "click", collapsible: true, heightStyle: "content"})
					.find("h3 a").click(function(ev){
					    ev.stopPropagation();
					});
				jQuery("#container").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			');
		session_write_close();
		//-- args
		$go 			= WT_Filter::post('go');
		$rootid 		= WT_Filter::get('rootid');
		$root_id		= WT_Filter::post('root_id');
		$rootid			= empty($root_id) ? $rootid : $root_id;
		$ged			= WT_Filter::post('ged') ? WT_Filter::post('ged') : $GEDCOM;
		$showsources	= WT_Filter::post('showsources') ? WT_Filter::post('showsources') : 0;
		$shownotes		= WT_Filter::post('shownotes') ? WT_Filter::post('shownotes') : 0;
		$missing		= WT_Filter::post('missing') ? WT_Filter::post('missing') : 0;
		$showmedia		= WT_Filter::post('showmedia') ? WT_Filter::post('showmedia') : 0;
		$exclude_tags	= array('CHAN','NAME','SEX','SOUR','NOTE','OBJE','RESN','FAMC','FAMS','TITL','CHIL','HUSB','WIFE','BIRT','CHR','BAPM','DEAT','CREM','BURI','_UID','_WT_OBJE_SORT');
		?>
		<div id="resource-page" class="family_report">
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="noprint">
				<h5><?php echo $this->getDescription(); ?></h5>
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;rootid=<?php echo $rootid; ?>&amp;ged=<?php echo WT_GEDURL; ?>">
					<input type="hidden" name="go" value="1">
					<div class="chart_options">
						<label for = "rootid"><?php echo WT_I18N::translate('Family'); ?></label>
						<input data-autocomplete-type="FAM" type="text" id="root_id" name="root_id" value="<?php echo $rootid; ?>">
					</div>
					<div class="chart_options">
						<label for = "showsources"><?php echo WT_I18N::translate('Show sources'); ?></label>
						<input type="checkbox" id="showsources" name="showsources" value="1"
							<?php if ($showsources) echo ' checked="checked"'; ?>
						>
					</div>
					<div class="chart_options">
						<label for = "shownotes"><?php echo WT_I18N::translate('Show notes'); ?></label>
						<input type="checkbox" id="shownotes" name="shownotes" value="1"
							<?php if ($shownotes) echo ' checked="checked"'; ?>
						>
					</div>
					<div class="chart_options">
						<label for = "missing"><?php echo WT_I18N::translate('Show basic events when blank'); ?></label>
						<input type="checkbox" id="missing" name="missing" value="1"
							<?php if ($missing) echo ' checked="checked"'; ?>
						>
					</div>
					<div class="chart_options">
						<label for = "showmedia"><?php echo WT_I18N::translate('Show media'); ?></label>
						<input type="checkbox" id="showmedia" name="showmedia" value="1"
							<?php if ($showmedia == 1) echo ' checked="checked"'; ?>
						>
					</div>
	 				<button class="btn btn-primary" type="submit" value="<?php echo WT_I18N::translate('show'); ?>">
						<i class="fa fa-eye"></i>
						<?php echo WT_I18N::translate('show'); ?>
					</button>
				</form>
			</div>
			<hr style="clear:both;">
			<!-- end of form -->
			<?php if ($go == 1) { ?>
				<div class="loading-image">&nbsp;</div>
				<div id="container" style="visibility:hidden;">
					<?php
					$family		= WT_Family::getInstance($rootid);
					$sections	= array('husband', 'wife', 'children');
					if ($family && $family->canDisplayDetails()) { ; ?>
						<h2><?php echo $family->getFullName(); ?></h2>
						<div id="accordion">
							<?php
							$number			= 0;
							$sup			= '';
							$source_list	= array();
							$data			= array();
							$husb = $family->getHusband();
							$wife = $family->getWife();
							foreach($sections as $section) {
								if ($section == 'husband') {
									$children = array($husb);
								}
								if ($section == 'wife') {
									$children = array($wife);
								}
								if ($section == 'children') {
									$children = $family->getChildren();
								}
								foreach ($children as $title) {
									$person	= WT_Person::getInstance($title->getXref());
									$person->add_family_facts(false);
									$indifacts = $person->getIndiFacts();
									switch ($section) {
										case 'husband' :
											$header = WT_I18N::translate('Husband');
											break;
										case 'wife' :
											$header = WT_I18N::translate('Wife');
											break;
										case 'children' :
											$header = getCloseRelationshipName($husb ? $husb : $wife, $title);
											break;
									}
									sort_facts($indifacts);
									if ($person && $person->canDisplayDetails()) {
										$birth			= false;
										$death			= false;
										$marr			= false;
										$chr			= false;
										$buri			= false;
										$crem			= false;
										?>
										<h3>
											<a href="<?php echo $title->getHtmlUrl(); ?>">
												<span class="relationship"><?php echo $header; ?></span>
												&nbsp;-&nbsp;
												<?php echo $title->getLifespanName(); ?>
											</a>
										</h3>
										<div class="<?php echo $section; ?>">
											<!-- Image display -->
											<?php $image = $title->displayImage(true);
											if ($showmedia && $image) { ?>
												<div class="indi_mainimage"><?php echo $image; ?></div>
											<?php } ?>
											<!-- facts and events for this individual -->
											<div class="facts_events">
												<!-- Birth fact -->
												<?php
												foreach ($indifacts as $fact) {
													if ($fact->getTag() === 'BIRT' && ($title->getBirthDate()->isOK() || $title->getBirthPlace())) {
														$birth		 = true;
														$data		 = getResourcefact($fact, $family, $sup, $source_list, $number);
														$sup		 = $data[0];
														$source_list = $data[1];
														$number		 = $data[2]; ?>
														<p class="report_fact">
															<span class="label"><?php echo print_fact_label($fact, $family) . $showsources ? $sup : ''; ?></span>
															<span class="details">
																<?php echo $data[3]['date']; ?>
																<?php echo $data[3]['place']; ?>
																<?php echo $data[3]['addr']; ?>
																<?php echo $data[3]['detail']; ?>
															</span>
														</p>
													<?php }
												}
												if ($missing && !$birth) { ?>
													<p class="report_fact">
														<span class="label"><?php echo WT_I18N::translate('Birth'); ?></span>
														<span class="details"></span>
													</p>
												<?php } ?>
												<!-- Christening fact -->
												<?php
												foreach ($indifacts as $fact) {
													if ($fact->getTag() === 'CHR' || $fact->getTag() === 'BAPM') {
														$chr = true;
														$data		 = getResourcefact($fact, $family, $sup, $source_list, $number);
														$sup		 = $data[0];
														$source_list = $data[1];
														$number		 = $data[2]; ?>
														<p class="report_fact">
															<span class="label"><?php echo print_fact_label($fact, $family) . $showsources ? $sup : ''; ?></span>
															<span class="details">
																<?php echo $data[3]['date']; ?>
																<?php echo $data[3]['place']; ?>
																<?php echo $data[3]['addr']; ?>
																<?php echo $data[3]['detail']; ?>
															</span>
														</p>
													<?php }
												}
												if ($missing && !$chr) { ?>
													<p class="report_fact">
														<span class="label"><?php echo WT_I18N::translate('Christening'); ?></span>
														<span class="details"></span>
													</p>
												<?php } ?>
												<!-- Other facts -->
												<?php
												foreach ($indifacts as $fact) {
													if (!in_array($fact->getTag(), $exclude_tags)) {
														$data		 = getResourcefact($fact, $family, $sup, $source_list, $number);
														$sup		 = $data[0];
														$source_list = $data[1];
														$number		 = $data[2]; ?>
														<p class="report_fact">
															<span class="label"><?php echo print_fact_label($fact, $family) . $showsources ? $sup : ''; ?></span>
															<span class="details">
																<?php echo $data[3]['date']; ?>
																<?php echo $data[3]['place']; ?>
																<?php echo $data[3]['addr']; ?>
																<?php echo $data[3]['detail']; ?>
															</span>
														</p>
														<?php
														// Add spouse details
														if (in_array($fact->getTag(), array('MARR', '_NMR')) && $fact->getSpouse()) { ?>
															<p class="report_fact">
																<span class="label"><?php echo $fact->getDate()->isOK() ? WT_I18N::translate('Spouse') : WT_I18N::translate('Partner'); ?></span>
																<span class="details"><span class="field"><?php echo $fact->getSpouse()->getLifespanName(); ?></span></span>
															</p>
														<?php }
														if ($fact->getTag() === 'MARR' || $fact->getTag() === '_NMR') {
															$marr = true;
														}
													}
												} ?>
												<!-- Marriage fact -->
												<?php
												if ($missing && !$marr) { ?>
													<p class="report_fact">
														<span class="label"><?php echo WT_I18N::translate('Marriage') ; ?></span>
														<span class="details"> <span class="field"></span> </span>
													</p>
												<?php } ?>
												<!-- Death fact -->
												<?php
												foreach ($indifacts as $fact) {
													if ($fact->getTag() === 'DEAT' && ($title->getDeathDate()->isOK() || $title->getDeathPlace())) {
														$death = true;
														$data		 = getResourcefact($fact, $family, $sup, $source_list, $number);
														$sup		 = $data[0];
														$source_list = $data[1];
														$number		 = $data[2]; ?>
														<p class="report_fact">
															<span class="label"><?php echo print_fact_label($fact, $family) . $showsources ? $sup : ''; ?></span>
															<span class="details">
																<?php echo $data[3]['date']; ?>
																<?php echo $data[3]['place']; ?>
																<?php echo $data[3]['addr']; ?>
																<?php echo $data[3]['detail']; ?>
															</span>
														</p>
													<?php }
												}
												if ($missing && !$death) { ?>
													<p class="report_fact">
														<span class="label"><?php echo WT_I18N::translate('Death'); ?></span>
														<span class="details"></span>
													</p>
												<?php } ?>
												<!-- Cremation fact -->
												<?php
												foreach ($indifacts as $fact) {
													if ($fact->getTag() === 'CREM') {
														$crem = true;
														$data		 = getResourcefact($fact, $family, $sup, $source_list, $number);
														$sup		 = $data[0];
														$source_list = $data[1];
														$number		 = $data[2]; ?>
														<p class="report_fact">
															<span class="label"><?php echo print_fact_label($fact, $family) . $showsources ? $sup : ''; ?></span>
															<span class="details">
																<?php echo $data[3]['date']; ?>
																<?php echo $data[3]['place']; ?>
																<?php echo $data[3]['addr']; ?>
																<?php echo $data[3]['detail']; ?>
															</span>
														</p>
													<?php }
												}
												if ($missing && !$crem) { ?>
													<p class="report_fact">
														<span class="label"><?php echo WT_I18N::translate('Cremation'); ?></span>
														<span class="details"></span>
													</p>
												<?php } ?>
												<!-- Burial fact -->
												<?php
												foreach ($indifacts as $fact) {
													if ($fact->getTag() === 'BURI') {
														$buri = true;
														$data		 = getResourcefact($fact, $family, $sup, $source_list, $number);
														$sup		 = $data[0];
														$source_list = $data[1];
														$number		 = $data[2]; ?>
														<p class="report_fact">
															<span class="label"><?php echo print_fact_label($fact, $family) . $showsources ? $sup : ''; ?></span>
															<span class="details">
																<?php echo $data[3]['date']; ?>
																<?php echo $data[3]['place']; ?>
																<?php echo $data[3]['addr']; ?>
																<?php echo $data[3]['detail']; ?>
															</span>
														</p>
													<?php }
												}
												if ($missing && !$buri) { ?>
													<p class="report_fact">
														<span class="label"><?php echo WT_I18N::translate('Burial'); ?></span>
														<span class="details"></span>
													</p>
												<?php }
												// parents
												if ($section !== 'children') {
													$parent_families = $person->getChildFamilies();
													foreach ($parent_families as $parent_family) {
														$p_husb	= $parent_family->getHusband();
														$p_wife	= $parent_family->getWife();
														if (!empty($p_husb)) { ?>
															<p class="report_fact">
																<span class="label"><?php echo WT_I18N::translate('Father'); ?></span>
																<span class="field"><?php echo $p_husb->getLifeSpanName(); ?></span>
															</p>
														<?php }
														if (!empty($p_wife)) { ?>
															<p class="report_fact">
																<span class="label"><?php echo WT_I18N::translate('Mother'); ?></span>
																<span class="field"><?php echo $p_wife->getLifeSpanName(); ?></span>
															</p>
														<?php }
													}
												}
												// Notes
												$otherfacts = $person->getOtherFacts();
												if ($otherfacts && $shownotes) { ?>
													<div id="notes">
														<h4><?php echo WT_I18N::translate('Notes'); ?></h4>
														<ol>
															<?php foreach ($otherfacts as $fact) {
																if ($fact->getTag() === 'NOTE') { ?>
																	<li><?php echo print_resourcenotes($fact, 1, true, true); ?></li>
																<?php }
															} ?>
														</ol>
													</div>
												<?php } ?>
											</div>
										</div>
									<?php }
								}
							} ?>
						</div>
						<?php if ($showsources) {?>
							<div id="facts_sources">
								<h3><?php echo WT_I18N::translate('Sources'); ?></h3>
								<?php
								foreach ($source_list as $source) {
									echo '
										<p>
											<span>' . ($source['key']) . '</span>
											<span>' . $source['value'] . '</span>
										</p>
									';
								}
								?>
							</div>
						<?php }
					} elseif ($family && $family->canDisplayName()) { ?>
						<h2><?php echo $this->getTitle() . '&nbsp;-&nbsp;' . $family->getFullName(); ?></h2>
						<p class="ui-state-highlight"><?php echo WT_I18N::translate('The details of this family are private.'); ?></p>
						<?php exit;
					} else { ?>
						<h2><?php echo $this->getTitle(); ?></h2>
						<p class="ui-state-error"><?php echo WT_I18N::translate('This family does not exist or you do not have permission to view it.'); ?></p>
						<?php exit;
					}
				} ?>
			</div>
		</div>
	<?php }
}
