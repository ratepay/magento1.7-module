function preProcessingBankForm(element) {
    element.value = element.value.replace(/\s/g, "");
}

function switchAccountType(method, element) {
    if (element.id == 'ratepay_' + method + '_classic_switch') {
        document.getElementById('ratepay_' + method + '_classic_switch').className = 'btn btn-primary';
        document.getElementById('ratepay_' + method + '_sepa_switch').className = 'btn btn-default';
        document.getElementById('ratepay_' + method + '_element_iban').style.display = 'none';
        document.getElementById('ratepay_' + method + '_iban').className = 'ratepay_rate_sepa_form';
        if (document.getElementById('ratepay_' + method + '_element_bic')) {
            document.getElementById('ratepay_' + method + '_element_bic').style.display = 'none';
            document.getElementById('ratepay_' + method + '_bic').className = 'ratepay_rate_sepa_form';
        }
        document.getElementById('ratepay_' + method + '_element_accountnumber').style.display = 'block';
        document.getElementById('ratepay_' + method + '_element_bankcode').style.display = 'block';
        document.getElementById('ratepay_' + method + '_account_number').className = 'ratepay_rate_sepa_form required-entry';
        document.getElementById('ratepay_' + method + '_bank_code_number').className = 'ratepay_rate_sepa_form required-entry';
    } else {
        document.getElementById('ratepay_' + method + '_classic_switch').className = 'btn btn-default';
        document.getElementById('ratepay_' + method + '_sepa_switch').className = 'btn btn-primary';
        document.getElementById('ratepay_' + method + '_element_iban').style.display = 'block';
        document.getElementById('ratepay_' + method + '_iban').className = 'ratepay_rate_sepa_form required-entry';
        if (document.getElementById('ratepay_' + method + '_element_bic')) {
            document.getElementById('ratepay_' + method + '_element_bic').style.display = 'block';
            document.getElementById('ratepay_' + method + '_bic').className = 'ratepay_rate_sepa_form required-entry';
        }
        document.getElementById('ratepay_' + method + '_element_accountnumber').style.display = 'none';
        document.getElementById('ratepay_' + method + '_element_bankcode').style.display = 'none';
        document.getElementById('ratepay_' + method + '_account_number').className = 'ratepay_rate_sepa_form';
        document.getElementById('ratepay_' + method + '_bank_code_number').className = 'ratepay_rate_sepa_form';
    }
}

function showAgreement(method) {
    document.getElementById('ratepay_' + method + '_sepa_agreement').style.display = 'inline-block';
    document.getElementById('ratepay_' + method + '_sepa_agreement_link').style.display = 'none';
}

function switchRatePaymentMethod(element) {
    if (element.id == 'ratepay_rate_method_switch_invoice') {
        document.getElementById('ratepay_rate_method_invoice').value = 1;
        document.getElementById('ratepay_rate_method_switch_invoice').style.display = 'none';
        document.getElementById('ratepay_rate_method_switch_directdebit').style.display = 'inline-block';
        batchDisplay('ratepay_rate_sepa_element', 'none');
        batchClassName('ratepay_rate_sepa_form', 'ratepay_rate_sepa_form');
    } else {
        document.getElementById('ratepay_rate_method_invoice').value = 0;
        document.getElementById('ratepay_rate_method_switch_directdebit').style.display = 'none';
        document.getElementById('ratepay_rate_method_switch_invoice').style.display = 'inline-block';
        batchDisplay('ratepay_rate_sepa_element', 'block');
        batchClassName('ratepay_rate_sepa_form', 'ratepay_rate_sepa_form required-entry');
    }
}

function batchDisplay(element, display) {
    var x = document.getElementsByClassName(element);
    var i;
    for (i = 0; i < x.length; i++) {
        x[i].style.display = display;
    }
}

function batchClassName(element, className) {
    var x = document.getElementsByClassName(element);
    var i;
    for (i = 0; i < x.length; i++) {
        x[i].className = className;
    }
}
