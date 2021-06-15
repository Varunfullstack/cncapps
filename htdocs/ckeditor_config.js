/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function (config) {
    config.contentsCss = '/screen.css';
    config.toolbarStartupExpanded = false;
    config.toolbar = 'CNCToolbar';
    config.toolbar_CNCToolbar =
        [
            ['Source', '-', '-', 'Bold', 'Italic', 'Underline', 'TextColor'],
            ['NumberedList', 'BulletedList'],
            ['Table'],
            ['Font', 'FontSize'],
            ['Link'],
        ];
    config.extraPlugins = 'font,wordcount,pastebase64';
    config.fontSize_sizes = '8/8pt;9/9pt;10/10pt;11/11pt;12/12pt;14/14pt;16/16pt;18/18pt;20/20pt;22/22pt;24/24pt;26/26pt;28/28pt;36/36pt;48/48pt;72/72pt';
    config.wordcount = {
        showParagraphs: false,
        showCharCount: true,
    };
    CKEDITOR.config.width = '870';
    CKEDITOR.config.height = '220';
    CKEDITOR.config.resize_minWidth = '760';
    CKEDITOR.config.removePlugins = 'liststyle,tabletools,language,tableselection,scayt,wsc';
};
