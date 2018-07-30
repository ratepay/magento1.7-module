function switchRateOrRuntime(mode, paymentMethod, url)
{
    if (mode == 'rate') {
        document.getElementById('ratepay_installment_rate_' + paymentMethod).value = 1;
        document.getElementById(paymentMethod + '_SwitchToTerm').className = 'ratepay-Active';
        document.getElementById(paymentMethod + '_SwitchToRuntime').className = '';
        document.getElementById(paymentMethod + '_ChooseInputRate').style.backgroundImage = "url(" + url + "arrow_dark.png)";
        document.getElementById(paymentMethod + '_ChooseInputRuntime').style.backgroundImage = "url(" + url + "arrow.png)";
        document.getElementById(paymentMethod + '_ContentTerm').style.display = 'block';
        document.getElementById(paymentMethod + '_ContentRuntime').style.display = 'none';
    } else if (mode == 'runtime') {
        document.getElementById('ratepay_installment_rate_' + paymentMethod).value = 0;
        document.getElementById(paymentMethod + '_SwitchToRuntime').className = 'ratepay-Active';
        document.getElementById(paymentMethod + '_SwitchToTerm').className = '';
        document.getElementById(paymentMethod + '_ChooseInputRate').style.backgroundImage = "url(" + url + "arrow.png)";
        document.getElementById(paymentMethod + '_ChooseInputRuntime').style.backgroundImage = "url(" + url + "arrow_dark.png)";
        document.getElementById(paymentMethod + '_ContentRuntime').style.display = 'block';
        document.getElementById(paymentMethod + '_ContentTerm').style.display = 'none';
    }
}

function changeDetails(paymentMethod) {
    var hide = document.getElementById("rp-hide-installment-plan-details_" + paymentMethod);
    var show = document.getElementById("rp-show-installment-plan-details_" + paymentMethod);
    var details = document.getElementById("rp-installment-plan-details_" + paymentMethod);
    var nodetails = document.getElementById("rp-installment-plan-no-details_" + paymentMethod);

    if (hide.style.display == "block") {
        hide.style.display = "none";
        nodetails.style.display = "block";
        show.style.display = "block";
        details.style.display = "none";
    } else {
        hide.style.display = "block";
        nodetails.style.display = "none";
        show.style.display = "none";
        details.style.display = "block";
    }
}

function makeQueryString (data) {
    var params = Object.keys(data).reduce(function(query, key) {
        var value = data[key];
        if (value) {
            var prefix = (query.length < 1) ? '' : (query + '&');
            return prefix + key + '=' + data[key];
        }

        return query;
    }, '');

    console.log(params);
    return params;
}

function ratepayRateCalculatorAction(mode, paymentMethod, url, form_key, reward, month)
{
    var calcValue;
    var calcMethod;
    var notification;
    var firstDay = document.getElementById('ratepay_payment_firstday_' + paymentMethod).value;

    var html;

    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if(document.getElementById('use_reward_points') && !document.getElementById('use_reward_points').checked) {
        reward = 0;
    }

    if (mode == 'rate') {
        calcValue = document.getElementById(paymentMethod + '-rate').value;
        calcMethod = 'calculation-by-rate';
    } else if (mode == 'runtime') {
        calcValue = month || document.getElementById(paymentMethod + '-runtime').value;
        calcMethod = 'calculation-by-time';
        notification = (document.getElementById(paymentMethod + '_Notification') == null) ? 0 : 1;
    }
    xmlhttp.open("POST", url, false);

    xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xmlhttp.send(makeQueryString({
        form_key: form_key,
        paymentMethod: paymentMethod,
        calcValue: calcValue,
        calcMethod: calcMethod,
        dueDate: firstDay,
        notification: notification,
        rewardPoints: reward
    }));

    if (xmlhttp.responseText != null) {
        html = xmlhttp.responseText;
        document.getElementById(paymentMethod + '_ResultContainer').innerHTML = html;
        document.getElementById(paymentMethod + '_ResultContainer').style.display = 'block';
        document.getElementById(paymentMethod + '_ResultContainer').style.padding = '3px 0 0 0';

        if(document.getElementById('ratepay_rate_sepa_block_' + paymentMethod).style.display == 'none'){
            document.getElementById('ratepay_rate_sepa_block_' + paymentMethod).style.display = 'block';
        }
    }
}
