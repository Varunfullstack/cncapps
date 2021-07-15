function getFormDataAsJSON(form) {

}

function UpdateQueryString(key, value, url) {
    if (!url) url = window.location.href;
    var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
        hash;

    if (re.test(url)) {
        if (typeof value !== 'undefined' && value !== null) {
            return url.replace(re, '$1' + key + "=" + value + '$2$3');
        }
        else {
            hash = url.split('#');
            url = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');
            if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                url += '#' + hash[1];
            }
            return url;
        }
    }
    else {
        if (typeof value !== 'undefined' && value !== null) {
            var separator = url.indexOf('?') !== -1 ? '&' : '?';
            hash = url.split('#');
            url = hash[0] + separator + key + '=' + value;
            if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                url += '#' + hash[1];
            }
            return url;
        }
        else {
            return url;
        }
    }
}

function addInputs(array, form) {
    array.forEach((element) => {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = element.name;
        input.value = element.value;
        form.appendChild(input);
    })
}

/*
 Common Javascript functions

 ==================================================================
 LTrim(string) : Returns a copy of a string without leading spaces.
 ==================================================================
 */
function LTrim(str)
/*
 PURPOSE: Remove leading blanks from our string.
 IN: str - the string we want to LTrim
 */ {
    var whitespace = String(" \t\n\r");

    var s = String(str);

    if (whitespace.indexOf(s.charAt(0)) != -1) {
        // We have a string with leading blank(s)...

        var j = 0, i = s.length;

        // Iterate from the far left of string until we
        // don't have any more whitespace...
        while (j < i && whitespace.indexOf(s.charAt(j)) != -1)
            j++;

        // Get the substring from the first non-whitespace
        // character to the end of the string...
        s = s.substring(j, i);
    }
    return s;
}

/*
 ==================================================================
 RTrim(string) : Returns a copy of a string without trailing spaces.
 ==================================================================
 */
function RTrim(str)
/*
 PURPOSE: Remove trailing blanks from our string.
 IN: str - the string we want to RTrim

 */ {
    // We don't want to trip JUST spaces, but also tabs,
    // line feeds, etc.  Add anything else you want to
    // "trim" here in Whitespace
    var whitespace = String(" \t\n\r");

    var s = String(str);

    if (whitespace.indexOf(s.charAt(s.length - 1)) != -1) {
        // We have a string with trailing blank(s)...

        var i = s.length - 1;       // Get length of string

        // Iterate from the far right of string until we
        // don't have any more whitespace...
        while (i >= 0 && whitespace.indexOf(s.charAt(i)) != -1)
            i--;


        // Get the substring from the front of the string to
        // where the last non-whitespace character is...
        s = s.substring(0, i + 1);
    }

    return s;
}

/*
 =============================================================
 Trim(string) : Returns a copy of a string without leading or trailing spaces
 =============================================================
 */
function Trim(str)
/*
 PURPOSE: Remove trailing and leading blanks from our string.
 IN: str - the string we want to Trim

 RETVAL: A Trimmed string!
 */ {
    return RTrim(LTrim(str));
}


/*
 Workaround for problem javascript seems to have with one array element length!
 */
function getElementCount(ID) {
    var numberOfElements = 0;
    if (!document.all(ID).length) {
        numberOfElements = 1;
    } else {
        numberOfElements = document.all(ID).length;
    }
    return numberOfElements;
}

function IsNumeric(sText) {
    var ValidChars = "0123456789.";
    var IsNumber = true;
    var Char;


    for (i = 0; i < sText.length && IsNumber == true; i++) {
        Char = sText.charAt(i);
        if (ValidChars.indexOf(Char) == -1) {
            IsNumber = false;
        }
    }
    return IsNumber;

}

/*
 This one swaps images
 */
function MM_swapImage() { //v3.0
    var i, j = 0, x, a = MM_swapImage.arguments;
    document.MM_sr = [];
    for (i = 0; i < (a.length - 2); i += 3) {
        if ((x = MM_findObj(a[i])) != null) {
            document.MM_sr[j++] = x;
            if (!x.oSrc) {
                x.oSrc = x.src;
            }
            x.src = a[i + 2];
        }
    }
}

function SC_toggleDisplay(currMenu) {
    if (document.getElementById) {
        thisMenu = document.getElementById('displayText' + currMenu).style
        if (thisMenu.display == "block") {
            thisMenu.display = "none"
            //MM_swapImage('displayIcon'+currMenu,'','/images/icon_plus.gif',0)
        } else {
            thisMenu.display = "block"
            //MM_swapImage('displayIcon'+currMenu,'','/images/icon_minus.gif',0)
        }
    }
}

/*
 This one sets the display properties of ALL elements starting ID=displayText on the current page
 State = blocl
 */
function SC_toggleDisplayAll() {
    if (state == 'block') {
        state = 'none';
    } else {
        state = 'block';
    }
    len_all = document.all.length
    for (i = 0; i < len_all; i++) {
        id = document.all[i].id.substr(0, 11);
        if (id == "displayText") {
            document.all[i].style.display = state;
        }
    }
}

/**
 * This array is used to remember mark status of rows in browse mode
 */
var marked_row = [];

/**
 * Sets/unsets the pointer and marker in browse mode
 *
 * @param   object    the table row
 * @param   integer  the row number
 * @param   string    the action calling this script (over, out or click)
 * @param   string    the default background color
 * @param   string    the color to use for mouseover
 * @param   string    the color to use for marking a row
 *
 * @return  boolean  whether pointer is set or not
 */
function setPointer(theRow, theRowNum, theAction, theDefaultColor, thePointerColor, theMarkColor) {
    var theCells = null;

    // 1. Pointer and mark feature are disabled or the browser can't get the
    //    row -> exits
    if ((thePointerColor == '' && theMarkColor == '')
        || typeof (theRow.style) == 'undefined') {
        return false;
    }

    // 2. Gets the current row and exits if the browser can't get it
    if (typeof (document.getElementsByTagName) != 'undefined') {
        theCells = theRow.getElementsByTagName('td');
    } else if (typeof (theRow.cells) != 'undefined') {
        theCells = theRow.cells;
    } else {
        return false;
    }

    // 3. Gets the current color...
    var rowCellsCnt = theCells.length;
    var domDetect = null;
    var currentColor = null;
    var newColor = null;
    // 3.1 ... with DOM compatible browsers except Opera that does not return
    //         valid values with "getAttribute"
    if (typeof (window.opera) == 'undefined'
        && typeof (theCells[0].getAttribute) != 'undefined') {
        currentColor = theCells[0].getAttribute('bgcolor');
        domDetect = true;
    }
    // 3.2 ... with other browsers
    else {
        currentColor = theCells[0].style.backgroundColor;
        domDetect = false;
    } // end 3

    currentColor = currentColor || '';

    // 3.3 ... Opera changes colors set via HTML to rgb(r,g,b) format so fix it
    if (currentColor && currentColor.indexOf("rgb") >= 0) {
        var rgbStr = currentColor.slice(currentColor.indexOf('(') + 1,
            currentColor.indexOf(')'));
        var rgbValues = rgbStr.split(",");
        currentColor = "#";
        var hexChars = "0123456789ABCDEF";
        for (var i = 0; i < 3; i++) {
            var v = rgbValues[i].valueOf();
            currentColor += hexChars.charAt(v / 16) + hexChars.charAt(v % 16);
        }
    }

    // 4. Defines the new color
    // 4.1 Current color is the default one
    if (currentColor == ''
        || currentColor.toLowerCase() == theDefaultColor.toLowerCase()) {
        if (theAction == 'over' && thePointerColor != '') {
            newColor = thePointerColor;
        } else if (theAction == 'click' && theMarkColor != '') {
            newColor = theMarkColor;
            marked_row[theRowNum] = true;
            // Garvin: deactivated onclick marking of the checkbox because it's also executed
            // when an action (like edit/delete) on a single item is performed. Then the checkbox
            // would get deactived, even though we need it activated. Maybe there is a way
            // to detect if the row was clicked, and not an item therein...
            // document.getElementById('id_rows_to_delete' + theRowNum).checked = true;
        }
    }
    // 4.1.2 Current color is the pointer one
    else if (currentColor.toLowerCase() == thePointerColor.toLowerCase()
        && (typeof (marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])) {
        if (theAction == 'out') {
            newColor = theDefaultColor;
        } else if (theAction == 'click' && theMarkColor != '') {
            newColor = theMarkColor;
            marked_row[theRowNum] = true;
            // document.getElementById('id_rows_to_delete' + theRowNum).checked = true;
        }
    }
    // 4.1.3 Current color is the marker one
    else if (currentColor.toLowerCase() == theMarkColor.toLowerCase()) {
        if (theAction == 'click') {
            newColor = (thePointerColor != '')
                ? thePointerColor
                : theDefaultColor;
            marked_row[theRowNum] = (typeof (marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])
                ? true
                : null;
            // document.getElementById('id_rows_to_delete' + theRowNum).checked = false;
        }
    } // end 4

    // 5. Sets the new color...
    if (newColor) {
        var c = null;
        // 5.1 ... with DOM compatible browsers except Opera
        if (domDetect) {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].setAttribute('bgcolor', newColor, 0);
            } // end for
        }
        // 5.2 ... with other browsers
        else {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].style.backgroundColor = newColor;
            }
        }
    } // end 5

    return true;
} // end of the 'setPointer()' function
function strpad(val) {
    return (!isNaN(val) && val.toString().length == 1) ? "0" + val : val;
}