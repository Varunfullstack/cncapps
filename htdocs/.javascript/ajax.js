var request;
var ignore_xmlhttprequest_response = false;
function loadXMLDoc(url) 
{
		url = url + "&is_ajax_request=1";
    // branch for native XMLHttpRequest object
    if (window.XMLHttpRequest) {
        request = new XMLHttpRequest();
        request.onreadystatechange = processRequestChange;
        request.open("GET", url , true);
        request.send(null);
    // branch for IE/Windows ActiveX version
    } else if (window.ActiveXObject) {
        request = new ActiveXObject("Microsoft.XMLHTTP");
        if (request) {
            request.onreadystatechange = processRequestChange;
            request.open("GET", url, true);
            request.send();
        }
    }
}
function processRequestChange() 
{
	// only if req shows "complete"
	if ( ignore_xmlhttprequest_response ){
		ignore_xmlhttprequest_response = false;		// discard
	}
	else{
		if (request.readyState == 4) {								// ready to handle response?
			// only if "OK"
			if (request.status == 200) {
				try{
					eval(request.responseText);
				}
				catch(e){
					alert(e + ': ' + request.responseText);	// display javascript error in alert dialogue
				}
			}
		}
	}
}
function encodeHtml(value) {
 encodedHtml = escape(value);
 encodedHtml = encodedHtml.replace(/\//g,"%2F");
 encodedHtml = encodedHtml.replace(/\?/g,"%3F");
 encodedHtml = encodedHtml.replace(/=/g,"%3D");
 encodedHtml = encodedHtml.replace(/&/g,"%26");
 encodedHtml = encodedHtml.replace(/@/g,"%40");
 return encodedHtml;
} 
/*
* Call if we do not want to respond to the next response
*/
function ignoreXMLHTTPRequestResponse(){
	ignore_xmlhttprequest_response = true;
	pause(30);

}