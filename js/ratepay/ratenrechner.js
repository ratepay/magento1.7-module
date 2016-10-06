function switchRateOrRuntime(mode, paymentMethod, url)
{
    if (mode == 'rate') {
        document.getElementById(paymentMethod + '_SwitchToTerm').className = 'ratepay-Active';
        document.getElementById(paymentMethod + '_SwitchToRuntime').className = '';
        document.getElementById(paymentMethod + '_ChooseInputRate').style.backgroundImage = "url(" + url + "arrow_dark.png)";
        document.getElementById(paymentMethod + '_ChooseInputRuntime').style.backgroundImage = "url(" + url + "arrow.png)";
        document.getElementById(paymentMethod + '_ContentTerm').style.display = 'block';
        document.getElementById(paymentMethod + '_ContentRuntime').style.display = 'none';
    } else if (mode == 'runtime') {
        document.getElementById(paymentMethod + '_SwitchToRuntime').className = 'ratepay-Active';
        document.getElementById(paymentMethod + '_SwitchToTerm').className = '';
        document.getElementById(paymentMethod + '_ChooseInputRate').style.backgroundImage = "url(" + url + "arrow.png)";
        document.getElementById(paymentMethod + '_ChooseInputRuntime').style.backgroundImage = "url(" + url + "arrow_dark.png)";
        document.getElementById(paymentMethod + '_ContentRuntime').style.display = 'block';
        document.getElementById(paymentMethod + '_ContentTerm').style.display = 'none';
    }
}

function ratepayRateCalculatorAction(mode, paymentMethod, url, form_key)
{
    var calcValue;
    var calcMethod;
    var notification;

    var html;

    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }

    if (mode == 'rate') {
        calcValue = document.getElementById(paymentMethod + '-rate').value;
        calcMethod = 'calculation-by-rate';
         if (document.getElementById('debitSelect')) {
             dueDate = document.getElementById('debitSelect').value;
        } else {
            dueDate= '';
        }
    } else if (mode == 'runtime') {
        calcValue = document.getElementById(paymentMethod + '-runtime').value;
        calcMethod = 'calculation-by-time';
        notification = (document.getElementById(paymentMethod + '_Notification') == null) ? 0 : 1;
        if(document.getElementById('debitSelectRuntime')){
             dueDate = document.getElementById('debitSelectRuntime').value;
        } else {
            dueDate= '';
        }
    }
    xmlhttp.open("POST", url, false);

    xmlhttp.setRequestHeader("Content-Type",
        "application/x-www-form-urlencoded");

    xmlhttp.send("form_key=" + form_key + "&paymentMethod=" + paymentMethod + "&calcValue=" + calcValue + "&calcMethod=" + calcMethod + "&dueDate=" + dueDate + "&notification=" + notification);

    if (xmlhttp.responseText != null) {
        html = xmlhttp.responseText;
        document.getElementById(paymentMethod + '_ResultContainer').innerHTML = html;
        document.getElementById(paymentMethod + '_ResultContainer').style.display = 'block';
        document.getElementById(paymentMethod + '_ResultContainer').style.padding = '3px 0 0 0';
        document.getElementById(paymentMethod + '_SwitchToTerm').style.display = 'none';
        //setTimeout("ratepaySetLoaderBack()",300);
    }

}
