function Trim(str)
/*
 PURPOSE: Remove trailing and leading blanks from our string.
 IN: str - the string we want to Trim

 RETVAL: A Trimmed string!
 */ {
    return RTrim(LTrim(str));
}

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

$(function () {

    $('[customer-picker]').each(function () {
        this.addEventListener('focus', saveCustomerString);
        this.addEventListener('blur', validateCustomerString);
        this.addEventListener('keypress', validateCustomerStringOnReturn);
    });

    let savedCustomerString;

    function saveCustomerString() {
        savedCustomerString = document.getElementById("customerString").value
    }

    function validateCustomerString() {
        if (Trim(document.getElementById("customerString").value) != "") {
            if (document.getElementById("customerString").value != savedCustomerString) {
                window.open('Customer.php?action=dispCustPopup&htmlFmt=popup&customerString=' +
                    escape(document.getElementById("customerString").value) +
                    '&parentIDField=customerID' +
                    '&parentDescField=customerString',
                    'customers', 'scrollbars=yes,resizable=no,width=300,height=300,copyhistory=no, menubar=0')
            }
        } else {
            $(this).value = "";
        }
    }

    function validateCustomerStringOnReturn() {
        if (event.keyCode == 13) {
            validateCustomerString();
        }
    }
});


