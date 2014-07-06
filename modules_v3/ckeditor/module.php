<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2014 webtrees development team.
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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class ckeditor_WT_Module extends WT_Module {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module.  CKEditor is a trademark.  Do not translate it?  http://ckeditor.com */ WT_I18N::translate('CKEditor™');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “CKEditor” module.  WYSIWYG = “what you see is what you get” */ WT_I18N::translate('Allow other modules to edit text using a “WYSIWYG” editor, instead of using HTML codes.');
	}

	// Convert <textarea class="html-edit"> fields to CKEditor fields
	public static function enableEditor($controller) {
		$controller
			->addExternalJavascript(WT_MODULES_DIR.'ckeditor/ckeditor.js')
			->addExternalJavascript(WT_MODULES_DIR.'ckeditor/adapters/jquery.js')
			// Need to specify the path before we load the libary
			->addInlineJavascript('var CKEDITOR_BASEPATH="'.WT_MODULES_DIR.'ckeditor/";', WT_Controller_Base::JS_PRIORITY_HIGH)
			// Activate the editor
			->addInlineJavascript('
				jQuery(".html-edit").ckeditor(function(){}, {
					skin : "moono",
					allowedContent: true,
					width: "100%",
					height: "400px",
					extraPlugins: "slideshow",
					filebrowserImageBrowseUrl:	"'.WT_MODULES_DIR.'ckeditor/kcfinder/browse.php?opener=ckeditor&type=images",
					filebrowserImageUploadUrl:	"'.WT_MODULES_DIR.'ckeditor/kcfinder/upload.php?opener=ckeditor&type=images"
				});
			');
	}
	// Convert <textarea class="html-edit"> fields to CKEditor fields with bassic settings only for messsaging
	public static function enableBasicEditor($controller) {
		$controller
			->addExternalJavascript(WT_MODULES_DIR.'ckeditor/ckeditor.js')
			->addExternalJavascript(WT_MODULES_DIR.'ckeditor/adapters/jquery.js')
			// Need to specify the path before we load the libary
			->addInlineJavascript('var CKEDITOR_BASEPATH="'.WT_MODULES_DIR.'ckeditor/";', WT_Controller_Base::JS_PRIORITY_HIGH)
			// Activate the editor
			->addInlineJavascript('
				jQuery(".html-edit").ckeditor(function(){}, {
					skin : "moono",
					width: "100%",
					height: "150px",
					toolbar: [["Source", "-", "Bold", "Italic", "Underline", "-", "Cut", "Copy", "Paste", "PasteText", "-", "Font", "FontSize", "TextColor", "BGColor"]]
				});
			');
	}
}
