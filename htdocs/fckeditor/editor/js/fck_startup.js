﻿/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2004 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * This file has been compacted for best loading performance.
 * 
 * Version: 2.0 RC3
 * Created: 2005-03-02 14:14:12
 */
Array.prototype.addItem = function (item) {
    var i = this.length;
    this[i] = item;
    return i;
};
Array.prototype.indexOf = function (value) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] == value) return i;
    }
    return -1;
};
String.prototype.startsWith = function (value) {
    return (this.substr(0, value.length) == value);
};
String.prototype.endsWith = function (value) {
    var L1 = this.length;
    var L2 = value.length;
    if (L2 > L1) return false;
    return (L2 == 0 || this.substr(L1 - L2, L2) == value);
};
String.prototype.remove = function (start, length) {
    var s = '';
    if (start > 0) s = this.substring(0, start);
    if (start + length < this.length) s += this.substring(start + length, this.length);
    return s;
};
String.prototype.trim = function () {
    return this.replace(/(^\s*)|(\s*$)/g, '');
};
String.prototype.ltrim = function () {
    return this.replace(/^\s*/g, '');
};
String.prototype.rtrim = function () {
    return this.replace(/\s*$/g, '');
};
String.prototype.replaceNewLineChars = function (replacement) {
    return this.replace(/\n/g, replacement);
};
FCK_STATUS_NOTLOADED=window.parent.FCK_STATUS_NOTLOADED=0;FCK_STATUS_ACTIVE=window.parent.FCK_STATUS_ACTIVE=1;FCK_STATUS_COMPLETE=window.parent.FCK_STATUS_COMPLETE=2;FCK_TRISTATE_OFF=window.parent.FCK_TRISTATE_OFF=0;FCK_TRISTATE_ON=window.parent.FCK_TRISTATE_ON=1;FCK_TRISTATE_DISABLED=window.parent.FCK_TRISTATE_DISABLED=-1;FCK_UNKNOWN=window.parent.FCK_UNKNOWN=-1000;FCK_TOOLBARITEM_ONLYICON=window.parent.FCK_TOOLBARITEM_ONLYTEXT=0;FCK_TOOLBARITEM_ONLYTEXT=window.parent.FCK_TOOLBARITEM_ONLYTEXT=1;FCK_TOOLBARITEM_ICONTEXT=window.parent.FCK_TOOLBARITEM_ONLYTEXT=2;FCK_EDITMODE_WYSIWYG=window.parent.FCK_EDITMODE_WYSIWYG=0;FCK_EDITMODE_SOURCE=window.parent.FCK_EDITMODE_SOURCE=1;
var FCKBrowserInfo = {};
var sAgent = navigator.userAgent.toLowerCase();
FCKBrowserInfo.IsIE = sAgent.indexOf("msie") != -1;
FCKBrowserInfo.IsGecko = !FCKBrowserInfo.IsIE;
FCKBrowserInfo.IsNetscape = sAgent.indexOf("netscape") != -1;
if (FCKBrowserInfo.IsIE) {
    FCKBrowserInfo.MajorVer = navigator.appVersion.match(/MSIE (.)/)[1];
    FCKBrowserInfo.MinorVer = navigator.appVersion.match(/MSIE .\.(.)/)[1];
} else {
    FCKBrowserInfo.MajorVer = 0;
    FCKBrowserInfo.MinorVer = 0;
}
FCKBrowserInfo.IsIE55OrMore = FCKBrowserInfo.IsIE && (FCKBrowserInfo.MajorVer > 5 || FCKBrowserInfo.MinorVer >= 5);
var FCKScriptLoader = {};
FCKScriptLoader.IsLoading = false;
FCKScriptLoader.Queue = [];
FCKScriptLoader.AddScript = function (scriptPath) {
    FCKScriptLoader.Queue[FCKScriptLoader.Queue.length] = scriptPath;
    if (!this.IsLoading) this.CheckQueue();
};
FCKScriptLoader.CheckQueue = function () {
    if (this.Queue.length > 0) {
        this.IsLoading = true;
        var sScriptPath = this.Queue[0];
        var oTempArray = [];
        for (i = 1; i < this.Queue.length; i++) oTempArray[i - 1] = this.Queue[i];
        this.Queue = oTempArray;
        var e;
        if (sScriptPath.lastIndexOf('.css') > 0) {
            e = document.createElement('LINK');
            e.rel = 'stylesheet';
            e.type = 'text/css';
        } else {
            e = document.createElement("script");
            e.type = "text/javascript";
        }
        document.getElementsByTagName("head")[0].appendChild(e);
        var oEvent = function () {
            if (this.tagName == 'LINK' || !this.readyState || this.readyState == 'loaded') FCKScriptLoader.CheckQueue();
        };
        if (e.tagName == 'LINK') {
            if (FCKBrowserInfo.IsIE) e.onload = oEvent; else FCKScriptLoader.CheckQueue();
            e.href = sScriptPath;
        } else {
            e.onload = e.onreadystatechange = oEvent;
            e.src = sScriptPath;
        }
    } else {
        this.IsLoading = false;
        if (this.OnEmpty) this.OnEmpty();
    }
};
var FCKURLParams={};var aParams=document.location.search.substr(1).split('&');for (i=0;i<aParams.length;i++){var aParam=aParams[i].split('=');var sParamName=aParam[0];var sParamValue=aParam[1];FCKURLParams[sParamName]=sParamValue;}
var FCK={};FCK.Name=FCKURLParams['InstanceName'];FCK.Status=FCK_STATUS_NOTLOADED;FCK.EditMode=FCK_EDITMODE_WYSIWYG;FCK.PasteEnabled=false;FCK.LinkedField=window.parent.document.getElementById(FCK.Name);if (!FCK.LinkedField) FCK.LinkedField=window.parent.document.getElementsByName(FCK.Name)[0];
var FCKConfig = FCK.Config = {};
if (document.location.protocol == 'file:') {
    FCKConfig.BasePath = document.location.pathname.substr(1);
    FCKConfig.BasePath = FCKConfig.BasePath.replace(/\\/gi, '/');
    FCKConfig.BasePath = 'file://' + FCKConfig.BasePath.substring(0, FCKConfig.BasePath.lastIndexOf('/') + 1);
} else {
    FCKConfig.BasePath = document.location.pathname.substring(0, document.location.pathname.lastIndexOf('/') + 1);
    FCKConfig.FullBasePath = document.location.protocol + '//' + document.location.host + FCKConfig.BasePath;
}
FCKConfig.LoadHiddenField = function () {
    var oConfigField = window.parent.document.getElementById(FCK.Name + '___Config');
    if (!oConfigField) return;
    var aCouples = oConfigField.value.split('&');
    for (var i = 0; i < aCouples.length; i++) {
        if (aCouples[i].length == 0) continue;
        var aConfig = aCouples[i].split('=');
        var sConfigName = unescape(aConfig[0]);
        var sConfigValue = unescape(aConfig[1]);
        if (sConfigValue.toLowerCase() == "true") FCKConfig[sConfigName] = true; else if (sConfigValue.toLowerCase() == "false") FCKConfig[sConfigName] = false; else if (!isNaN(sConfigValue)) FCKConfig[sConfigName] = parseInt(sConfigValue); else FCKConfig[sConfigName] = sConfigValue;
    }
};
FCKConfig.ToolbarSets = {};
FCKConfig.Plugins = {};
FCKConfig.Plugins.Items = [];
FCKConfig.Plugins.Add = function (name, langs, path) {
    FCKConfig.Plugins.Items.addItem([name, langs, path]);
};
var FCKeditorAPI;if (!window.parent.FCKeditorAPI){FCKeditorAPI=window.parent.FCKeditorAPI={};FCKeditorAPI.__Instances={};FCKeditorAPI.Version='2.0 RC3';FCKeditorAPI.GetInstance=function(instanceName){return this.__Instances[instanceName];};}else FCKeditorAPI=window.parent.FCKeditorAPI;FCKeditorAPI.__Instances[FCK.Name]=FCK;
window.document.oncontextmenu = function (e) {
    if (e) e.preventDefault();
    return false;
};
if (!FCKBrowserInfo.IsIE) {
    window.onresize = function () {
        var oFrame = document.getElementById('eEditorArea');
        oFrame.height = 0;
        var oCell = document.getElementById(FCK.EditMode == FCK_EDITMODE_WYSIWYG ? 'eWysiwygCell' : 'eSource');
        var iHeight = oCell.offsetHeight;
        oFrame.height = iHeight - 2;
    };
}
window.onload = function () {
    if (FCKBrowserInfo.IsNetscape) document.getElementById('eWysiwygCell').style.paddingRight = '2px';
    FCKScriptLoader.OnEmpty = function () {
        FCKScriptLoader.OnEmpty = null;
        FCKConfig.LoadHiddenField();
        if (FCKConfig.CustomConfigurationsPath.length > 0) FCKScriptLoader.AddScript(FCKConfig.CustomConfigurationsPath);
        LoadStyles();
    };
    FCKScriptLoader.AddScript('../fckconfig.js');
};
function LoadStyles() {
    FCKScriptLoader.OnEmpty = LoadScripts;
    FCKScriptLoader.AddScript(FCKConfig.SkinPath + 'fck_editor.css');
    FCKScriptLoader.AddScript(FCKConfig.SkinPath + 'fck_contextmenu.css');
}
function LoadScripts() {
    FCKScriptLoader.OnEmpty = null;
    if (FCKBrowserInfo.IsIE) FCKScriptLoader.AddScript('js/fckeditorcode_ie_1.js'); else FCKScriptLoader.AddScript('js/fckeditorcode_gecko_1.js');
}
function LoadLanguageFile() {
    FCKScriptLoader.OnEmpty = function () {
        FCKScriptLoader.OnEmpty = null;
        if (FCKLang) window.document.dir = FCKLang.Dir;
        FCK.StartEditor();
    };
    FCKScriptLoader.AddScript('lang/' + FCKLanguageManager.ActiveLanguage.Code + '.js');
}
