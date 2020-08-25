var req;

function navigate(month,year) {
        var url = "calendar.php?month="+month+"&year="+year;
        if(window.XMLHttpRequest) {
                req = new XMLHttpRequest();
        } else if(window.ActiveXObject) {
                req = new ActiveXObject("Microsoft.XMLHTTP");
        }
        req.open("GET", url, true);
        req.onreadystatechange = callback;
        req.send(null);
}

function callback() {        
        obj = document.getElementById("calendar");
        setFade(0);
        
		if(req.readyState == 4) {
                if(req.status == 200) {
                        response = req.responseText;
                        obj.innerHTML = response;
                        fade(0);
                } else {
                        alert("There was a problem retrieving the data:\n" + req.statusText);
                }
        }
}

function fade(amt) {
	if(amt <= 100) {
		setFade(amt);
		amt += 10;
		setTimeout("fade("+amt+")", 5);
    }
}

function setFade(amt) {
	obj = document.getElementById("calendar");
	
	amt = (amt == 100)?99.999:amt;
  
	// IE
	obj.style.filter = "alpha(opacity:"+amt+")";
  
	// Safari<1.2, Konqueror
	obj.style.KHTMLOpacity = amt/100;
  
	// Mozilla and Firefox
	obj.style.MozOpacity = amt/100;
  
	// Safari 1.2, newer Firefox and Mozilla, CSS3
	obj.style.opacity = amt/100;
}