/**
 *
 * ieSpell API JavaScript
 * Copyright (C) 2002-2004 Red Egg Software. 
 * All rights reserved. 
 *
 * @author Sidney Chong
 * @version $Revision: 1.1 $ $Date: 2004/11/19 16:06:03 $
 * @srcpath $Source: e:/coderepo/iespell/src/app/api/ieSpell.js,v $
 *
 **/

var myAgent = navigator.userAgent.toLowerCase();
var myVersion = parseInt(navigator.appVersion);

var is_ie = ((myAgent.indexOf("msie") != -1) 
         && (myAgent.indexOf("opera") == -1));
var is_nav = ((myAgent.indexOf('mozilla')!=-1) 
           && (myAgent.indexOf('spoofer')==-1) 
           && (myAgent.indexOf('compatible') == -1) 
           && (myAgent.indexOf('opera')==-1)
           && (myAgent.indexOf('webtv') ==-1) 
           && (myAgent.indexOf('hotjava')==-1));

var is_win = ((myAgent.indexOf("win")!=-1) 
           || (myAgent.indexOf("16bit")!=-1));

var is_mac = (myAgent.indexOf("mac")!=-1);

//this function will dynamically determine if ieSpell is installed.
//If YES, a button with the text "Check Spelling" will be shown to the user.
//if NO, a button with the text "Get ieSpell" will be shown instead.
function spellcheckbutton() {
//  try {
//    var tmpis = new ActiveXObject("ieSpell.ieSpellExtension");
    document.write("<input type='button' name='btnSpell' value='Check Spelling' class='forminput' onclick='checkdocspelling()'>");
//  } catch(exception) {
//    if (is_ie&&is_win) {
//      document.write("<input type='button' name='btnSpell' value='Get ieSpell' class='forminput' onclick='checkdocspelling()'>");
//    }
//  }
}


//this function will invoke ieSpell on the ENTIRE document.
function checkdocspelling() {
  try {
    var tmpis = new ActiveXObject("ieSpell.ieSpellExtension");
    tmpis.CheckAllLinkedDocuments(document);
  } catch(exception) {
    if (is_ie&&is_win) {
				window.open("http://www.download.com/3000-2378-10208550.html?part=14291&subj=dlpage&tag=button","Download");
    }
  }
}

//this function will invoke ieSpell on the specified node. The rest of the document is 
//not touched
function checknodespelling(node) {
  try {
    var tmpis = new ActiveXObject("ieSpell.ieSpellExtension");
    tmpis.CheckDocumentNode(node);
  } catch(exception) {
    if (is_ie&&is_win) {
				window.open("http://www.download.com/3000-2378-10208550.html?part=14291&subj=dlpage&tag=button","Download");
    }
  }
}

//this function uses the more advanced document spell check method that does not
//prompts the user with the "Spell Check Completed" message as well as returning a
//FALSE if the user cancels the spell check.
function checkdocspelling2() {
  try {
    var tmpis = new ActiveXObject("ieSpell.ieSpellExtension");
    return tmpis.CheckAllLinkedDocuments2(document, true);
  } catch(exception) {
    if (is_ie&&is_win) {
				window.open("http://www.download.com/3000-2378-10208550.html?part=14291&subj=dlpage&tag=button","Download");
    }
  }
}

//this function uses the more advanced node spell check method that does not
//prompts the user with the "Spell Check Completed" message as well as returning a
//FALSE if the user cancels the spell check.
function checknodespelling2(node) {
  try {
    var tmpis = new ActiveXObject("ieSpell.ieSpellExtension");
    return tmpis.CheckDocumentNode2(node, true);
  } catch(exception) {
    if (is_ie&&is_win) {
//      window.open("http://www.iespell.com/download.php","Download");
				window.open("http://www.download.com/3000-2378-10208550.html?part=14291&subj=dlpage&tag=button","Download");
    }
  }
}
