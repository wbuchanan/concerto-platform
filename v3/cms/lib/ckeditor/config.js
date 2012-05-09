/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
    config.width = 800;
    config.extraPlugins = 'codemirror,autogrow';
    config.removePlugins = 'resize,maximize';
};

CKEDITOR.plugins.load('pgrfilemanager');
CKEDITOR.plugins.load('codemirror');
