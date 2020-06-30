/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	config.language = 'nl';
	config.uiColor = geefInstelling('artikelkleur');
	config.contentsCss = ['/vendor/twbs/bootstrap/dist/css/bootstrap-reboot.min.css', '/vendor/twbs/bootstrap/dist/css/bootstrap.min.css', '/css/cyndaron.css', '/user.css'];
	config.allowedContent = true;
	config.disallowedContent = 'table[cellspacing,cellpadding,border]';
};
