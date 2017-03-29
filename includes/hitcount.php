<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2017 kiwitrees.net
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
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Only record hits for certain pages
switch (WT_SCRIPT_NAME) {
case 'index.php':
	switch (safe_REQUEST($_REQUEST, 'ctype', array('gedcom', 'user'), WT_USER_ID ? 'user' : 'gedcom')) {
	case 'user':
		$page_parameter='user:'.WT_USER_ID;
		break;
	default:
		$page_parameter='gedcom:'.WT_GED_ID;
		break;
	}
	break;
case 'individual.php':
	$page_parameter=safe_GET('pid', WT_REGEX_XREF);
	break;
case 'family.php':
	$page_parameter=safe_GET('famid', WT_REGEX_XREF);
	break;
case 'source.php':
	$page_parameter=safe_GET('sid', WT_REGEX_XREF);
	break;
case 'repo.php':
	$page_parameter=safe_GET('rid', WT_REGEX_XREF);
	break;
case 'note.php':
	$page_parameter=safe_GET('nid', WT_REGEX_XREF);
	break;
case 'mediaviewer.php':
	$page_parameter=safe_GET('mid', WT_REGEX_XREF);
	break;
default:
	$page_parameter='';
	break;
}
if ($page_parameter) {
	$hitCount=WT_DB::prepare(
		"SELECT page_count FROM `##hit_counter`".
		" WHERE gedcom_id=? AND page_name=? AND page_parameter=?"
	)->execute(array(WT_GED_ID, WT_SCRIPT_NAME, $page_parameter))->fetchOne();

	// Only record one hit per session
	if ($page_parameter && empty($WT_SESSION->SESSION_PAGE_HITS[WT_SCRIPT_NAME.$page_parameter])) {
		$WT_SESSION->SESSION_PAGE_HITS[WT_SCRIPT_NAME.$page_parameter]=true;
		if (is_null($hitCount)) {
			$hitCount=1;
			WT_DB::prepare(
				"INSERT INTO `##hit_counter` (gedcom_id, page_name, page_parameter, page_count) VALUES (?, ?, ?, ?)"
			)->execute(array(WT_GED_ID, WT_SCRIPT_NAME, $page_parameter, $hitCount));
		} else {
			$hitCount++;
			WT_DB::prepare(
				"UPDATE `##hit_counter` SET page_count=?".
				" WHERE gedcom_id=? AND page_name=? AND page_parameter=?"
			)->execute(array($hitCount, WT_GED_ID, WT_SCRIPT_NAME, $page_parameter));
		}
	}
} else {
	$hitCount=1;
}

$hitCount='<span class="hit-counter">'.WT_I18N::number($hitCount).'</span>';

unset($page_name, $page_parameter);
