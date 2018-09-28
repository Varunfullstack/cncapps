/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function (config) {
    config.contentsCss = '/screen.css';
//	config.scayt_autoStartup = true;
    config.toolbarStartupExpanded = false;
    config.disableNativeSpellChecker = false;

    config.toolbar = 'CNCToolbar';

  config.toolbar_CNCToolbar =
  [
   ['Source', '-', '-', 'Bold','Italic','Underline','Strike'],
   ['NumberedList','BulletedList','-', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
   ['Table'],
   ['Styles','Format','Font','FontSize','TextColor','BGColor']
  ];
  
  CKEDITOR.config.width = '870';
  CKEDITOR.config.resize_minWidth = '760';
    CKEDITOR.config.disableNativeSpellChecker = false;
    CKEDITOR.config.removePlugins = 'liststyle,tabletools,scayt,menubutton,contextmenu';

};
