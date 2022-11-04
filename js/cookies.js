//

function setCookie(cname, cvalue, exdays) {
	const d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	let expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

// Disabled as of migration to WPE, 06/15/22 -- seems the basic get function for cookies is covered already by some core functionality supplied in WPE environment
function getCookie(cname) {
	let name = cname + "=";
	let ca = document.cookie.split(';');
	for(let i = 0; i < ca.length; i++) {
    	let c = ca[i];
    	while (c.charAt(0) == ' ') {
      		c = c.substring(1);
    	}
    	if (c.indexOf(name) == 0) {
      		return c.substring(name.length, c.length);
    	}
  	}
  return "";
}/**/

function checkCookie(cname) {
	let cvalue = getCookie(cname);
  	if (cvalue != "") {
  		alert("cookie '" + cname+"' = '" + cvalue + "'");
  	} else {
  		alert("cookie '" + cname+"' not set yet '");
    	/*if (user != "" && user != null) {
      		setCookie("username", user, 365);
    	}*/
  	}
}