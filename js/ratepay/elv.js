/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function preProcessingBankForm(element) {
    element.value = element.value.replace(/\s/g, "");
}

function isIban(element, method){
    var bankCodeField = document.getElementById('ratepay_rate_element_bankcode_' + method);
    if(isNaN(element.value) || !element.value){
        if (bankCodeField) { bankCodeField.style.display = 'none'; };
        //removeDuplicatedNames('payment[ratepay_rate_iban]');
        //document.getElementById('ratepay_rate_iban_'  + method).name = 'payment[ratepay_rate_iban]';
    } else {
        bankCodeField.style.display = 'block';
        //removeDuplicatedNames('payment[ratepay_rate_account_number]');
        //document.getElementById('ratepay_rate_iban_'  + method).name = 'payment[ratepay_rate_account_number]';
    }
    preProcessingBankForm(element);
}

function showAgreement(method) {
    document.getElementById('ratepay_' + method + '_sepa_agreement').style.display = 'inline-block';
    document.getElementById('ratepay_' + method + '_sepa_agreement_link').style.display = 'none';
}

function removeDuplicatedNames(name) {
    var elements = document.getElementsByName(name);
    elements.forEach(function (entry) {
        entry.name = '';
    });
}

function switchRatePaymentMethod(element, paymentMethod, form_key, reward) {
    var installment_method = null;
    var url = null;
    if(document.getElementById('ratepay_installment_rate_' + paymentMethod).value == 1 && paymentMethod !== 'ratepay_rate0') {
        url = document.getElementById('ratepay_installment_url_rate_' + paymentMethod).value;
        installment_method = 'rate';
    } else {
        url = document.getElementById('ratepay_installment_url_runtime_' + paymentMethod).value;
        installment_method = 'runtime';
    }
    if (element.id === 'ratepay_rate_method_switch_invoice_' + paymentMethod && (paymentMethod !== 'ratepay_directdebit')) {
        document.getElementById('ratepay_rate_method_invoice_' + paymentMethod).value = 1;
        document.getElementById('ratepay_rate_method_switch_invoice_' + paymentMethod).style.display = 'none';
        document.getElementById('ratepay_rate_method_switch_directdebit_' + paymentMethod).style.display = 'inline-block';
        document.getElementById('ratepay_payment_firstday_' + paymentMethod).value = 28;
        batchDisplay(paymentMethod + '_sepa_element', 'none');
        batchClassName('ratepay_rate_sepa_form', 'ratepay_rate_sepa_form_');
    } else {
        document.getElementById('ratepay_rate_method_invoice_' + paymentMethod).value = 0;
        document.getElementById('ratepay_rate_method_switch_directdebit_' + paymentMethod).style.display = 'none';
        document.getElementById('ratepay_rate_method_switch_invoice_' + paymentMethod).style.display = 'inline-block';
        document.getElementById('ratepay_payment_firstday_' + paymentMethod).value = 2;
        batchDisplay(paymentMethod + '_sepa_element', 'block');
        batchClassName('ratepay_rate_sepa_form', 'ratepay_rate_sepa_form required-entry');
        isIban(document.getElementById('ratepay_rate_iban_' + paymentMethod), paymentMethod);
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
