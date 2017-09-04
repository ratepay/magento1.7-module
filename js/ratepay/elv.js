function preProcessingBankForm(element) {
    element.value = element.value.replace(/\s/g, "");
}

function isIban(element, method){
    if(isNaN(element.value) || !element.value){
        document.getElementById('ratepay_rate_element_bankcode_' + method).style.display = 'none';
        document.getElementById('ratepay_rate_iban_'  + method).name = 'payment[ratepay_rate_iban]';
    } else {
        document.getElementById('ratepay_rate_element_bankcode_'  + method).style.display = 'block';
        document.getElementById('ratepay_rate_iban_'  + method).name = 'payment[ratepay_rate_account_number]';
    }
    preProcessingBankForm(element);
}

function switchAccountType(method, element) {
    if (element.id == method + '_classic_switch') {
        document.getElementById(method + '_classic_switch').className = 'btn btn-primary';
        document.getElementById(method + '_sepa_switch').className = 'btn btn-default';
        document.getElementById(method + '_element_iban').style.display = 'none';
        document.getElementById(method + '_iban').className = 'ratepay_rate_sepa_form';
        if (document.getElementById(method + '_element_bic')) {
            document.getElementById(method + '_element_bic').style.display = 'none';
            document.getElementById(method + '_bic').className = 'ratepay_rate_sepa_form';
        }
        document.getElementById(method + '_element_accountnumber').style.display = 'block';
        document.getElementById(method + '_element_bankcode').style.display = 'block';
        document.getElementById(method + '_account_number').className = 'ratepay_rate_sepa_form required-entry';
        document.getElementById(method + '_bank_code_number').className = 'ratepay_rate_sepa_form required-entry';
    } else {
        document.getElementById(method + '_classic_switch').className = 'btn btn-default';
        document.getElementById(method + '_sepa_switch').className = 'btn btn-primary';
        document.getElementById(method + '_element_iban').style.display = 'block';
        document.getElementById(method + '_iban').className = 'ratepay_rate_sepa_form required-entry';
        if (document.getElementById(method + '_element_bic')) {
            document.getElementById(method + '_element_bic').style.display = 'block';
            document.getElementById(method + '_bic').className = 'ratepay_rate_sepa_form required-entry';
        }
        document.getElementById(method + '_element_accountnumber').style.display = 'none';
        document.getElementById(method + '_element_bankcode').style.display = 'none';
        document.getElementById(method + '_account_number').className = 'ratepay_rate_sepa_form';
        document.getElementById(method + '_bank_code_number').className = 'ratepay_rate_sepa_form';
    }
}

function showAgreement(method) {
    document.getElementById('ratepay_' + method + '_sepa_agreement').style.display = 'inline-block';
    document.getElementById('ratepay_' + method + '_sepa_agreement_link').style.display = 'none';
}

function switchRatePaymentMethod(element, paymentMethod, form_key, reward) {
    var installment_method = null;
    var url = null;
    if(document.getElementById('ratepay_installment_rate_' + paymentMethod).value == 1){
        url = document.getElementById('ratepay_installment_url_rate_' + paymentMethod).value;
        installment_method = 'rate';
    } else {
        url = document.getElementById('ratepay_installment_url_runtime_' + paymentMethod).value;
        installment_method = 'runtime';
    }
    if (element.id == 'ratepay_rate_method_switch_invoice_' + paymentMethod && (paymentMethod !== 'ratepay_directdebit')) {
        document.getElementById('ratepay_rate_method_invoice_' + paymentMethod).value = 1;
        document.getElementById('ratepay_rate_method_switch_invoice_' + paymentMethod).style.display = 'none';
        document.getElementById('ratepay_rate_method_switch_directdebit_' + paymentMethod).style.display = 'inline-block';
        document.getElementById('ratepay_payment_firstday_' + paymentMethod).value = 28;
        batchDisplay('ratepay_rate_sepa_element', 'none');
        batchClassName('ratepay_rate_sepa_form', 'ratepay_rate_sepa_form_');
        ratepayRateCalculatorAction(installment_method, paymentMethod, url, form_key, reward)
    } else {
        document.getElementById('ratepay_rate_method_invoice_' + paymentMethod).value = 0;
        document.getElementById('ratepay_rate_method_switch_directdebit_' + paymentMethod).style.display = 'none';
        document.getElementById('ratepay_rate_method_switch_invoice_' + paymentMethod).style.display = 'inline-block';
        document.getElementById('ratepay_payment_firstday_' + paymentMethod).value = 2;
        batchDisplay('ratepay_rate_sepa_element', 'block');
        batchClassName('ratepay_rate_sepa_form', 'ratepay_rate_sepa_form required-entry');
        isIban(document.getElementById('ratepay_rate_iban_' + paymentMethod), paymentMethod);
        ratepayRateCalculatorAction(installment_method, paymentMethod, url, form_key, reward)
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
