
/* Basic AJAX functions - Begin */
// Function to make asynchronous request to the server

var containerArea = '';
var predefinedMessageFlag = false;

function makePOSTRequest(url, parameters, functionToCall) {
	http_request = false;
    if (window.XMLHttpRequest) { // Mozilla, Safari,...
    	http_request = new XMLHttpRequest();
        if (http_request.overrideMimeType) {
            http_request.overrideMimeType('text/html');
        }
    }
	else if (window.ActiveXObject) { // IE
        try {
    	    http_request = new ActiveXObject("Msxml2.XMLHTTP");
        }
		catch(e){
        	try {
            	http_request = new ActiveXObject("Microsoft.XMLHTTP");
            }
			catch(e){}
        }
	}
    if (!http_request) {
    	alert('Cannot create XMLHTTP instance');
        return false;
    }	
    http_request.onreadystatechange = eval(functionToCall);
    http_request.open('POST', url, true); // SET method to GET or POST according to form method specified
    http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    http_request.setRequestHeader("Content-length", parameters.length);
    http_request.setRequestHeader("Connection", "close");
    http_request.send(parameters);	
}

// Function For Getting Message
function updateMessage(pageId)
{
	containerArea = 'status_msg';
	predefinedMessageFlag = true;
	var poststr = "messageid=" + encodeURI(document.forms['frmEditMessages'].uid.options[document.forms['frmEditMessages'].uid.selectedIndex].value);
   	makePOSTRequest("index.php?id="+pageId+"&mode=showmessage&no_cache=1", poststr, 'displayMessage');
}

// Function For Displaying Message
function displayMessage()
{
	if (http_request.readyState == 4) {
		returnVal = http_request.responseText;
		document.getElementById(containerArea).innerHTML = '';
		document.forms['frmEditMessages'].title.value = returnVal;
		predefinedMessageFlag = false;
	}
}

// Function For Validating Messages
function validatedPredefinedMessage(frmName)
{
	if(document.forms[frmName].messages.value == ''){
		return false;
	}
	return true;
}


