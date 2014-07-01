function switchRateOrRuntime(mode, url) 
{
    if (mode == 'rate') {
        document.getElementById('piRpSwitchToTerm').className = 'piRpActive';
        document.getElementById('piRpSwitchToRuntime').className = '';
        document.getElementById('piRpChooseInputRate').style.backgroundImage = "url(" + url + "images/ratepay/arrow_dark.png)";  
        document.getElementById('piRpChooseInputRuntime').style.backgroundImage = "url(" + url + "images/ratepay/arrow.png)";  
        document.getElementById('piRpContentTerm').style.display = 'block';
        document.getElementById('piRpContentRuntime').style.display = 'none';
    } else if (mode == 'runtime') {
        document.getElementById('piRpSwitchToRuntime').className = 'piRpActive';
        document.getElementById('piRpSwitchToTerm').className = '';
        document.getElementById('piRpChooseInputRate').style.backgroundImage = "url(" + url + "images/ratepay/arrow.png)";  
        document.getElementById('piRpChooseInputRuntime').style.backgroundImage = "url(" + url + "images/ratepay/arrow_dark.png)";  
        document.getElementById('piRpContentRuntime').style.display = 'block';
        document.getElementById('piRpContentTerm').style.display = 'none';
    }

}

function piRatepayRateCalculatorAction(mode, url) 
{
    var calcValue;
    var calcMethod;

    var html;

    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }

    if (mode == 'rate') {
        calcValue = document.getElementById('rate').value;
        calcMethod = 'calculation-by-rate';
         if (document.getElementById('debitSelect')) {
             dueDate = document.getElementById('debitSelect').value;
        } else {
            dueDate= '';
        }
    } else if (mode == 'runtime') {
        calcValue = document.getElementById('runtime').value;
        calcMethod = 'calculation-by-time';
        if(document.getElementById('debitSelectRuntime')){
             dueDate = document.getElementById('debitSelectRuntime').value;
        } else {
            dueDate= '';
        }
    }
    xmlhttp.open("POST", url, false);

    xmlhttp.setRequestHeader("Content-Type",
        "application/x-www-form-urlencoded");

    xmlhttp.send("calcValue=" + calcValue + "&calcMethod=" + calcMethod + "&dueDate=" + dueDate);

    if (xmlhttp.responseText != null) {
        html = xmlhttp.responseText;
        document.getElementById('piRpResultContainer').innerHTML = html;
        document.getElementById('piRpResultContainer').style.display = 'block';
        document.getElementById('piRpResultContainer').style.padding = '3px 0 0 0';
        document.getElementById('piRpSwitchToTerm').style.display = 'none';
        setTimeout("piSetLoaderBack()",300);

    }

}

function piMouseOver(mouseoverString) 
{
   document.getElementById(mouseoverString).style.display = 'block';
}

function piMouseOut(mouseoverString) 
{
   document.getElementById(mouseoverString).style.display = 'none';
}