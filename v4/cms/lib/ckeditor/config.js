/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
        config.width = 800;
        config.extraPlugins = 'codemirror';
        config.autoGrow_maxHeight = 800;
        config.autoGrow_onStartup = true;
        config.removePlugins = "resize"; 
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
};

CKEDITOR.plugins.load('pgrfilemanager');
CKEDITOR.plugins.load('codemirror'); 