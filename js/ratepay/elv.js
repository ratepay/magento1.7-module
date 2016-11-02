
function showElvFields(id)
{
    document.getElementById(id).style.display = 'block';
}

function hideElvFields(id)
{
    document.getElementById(id).style.display = 'none';
}

function preProcessingBankForm(element) {
    element.value = element.value.replace(/\s/g, "");
}

function switchAccountType(element) {
    if (element.id == 'ratepay_directdebit_classic_switch') {
        document.getElementById('ratepay_directdebit_classic_switch').className = 'button2';
        document.getElementById('ratepay_directdebit_sepa_switch').className = 'button';
        document.getElementById('ratepay_directdebit_element_iban').style.display = 'none';
        document.getElementById('ratepay_directdebit_iban').className = '';
        if (document.getElementById('ratepay_directdebit_element_bic')) {
            document.getElementById('ratepay_directdebit_element_bic').style.display = 'none';
            document.getElementById('ratepay_directdebit_bic').className = '';
        }
        document.getElementById('ratepay_directdebit_element_accountnumber').style.display = 'block';
        document.getElementById('ratepay_directdebit_element_bankcode').style.display = 'block';
        document.getElementById('ratepay_directdebit_account_number').className = 'required-entry';
        document.getElementById('ratepay_directdebit_bank_code_number').className = 'required-entry';
    } else {
        document.getElementById('ratepay_directdebit_classic_switch').className = 'button';
        document.getElementById('ratepay_directdebit_sepa_switch').className = 'button2';
        document.getElementById('ratepay_directdebit_element_iban').style.display = 'block';
        document.getElementById('ratepay_directdebit_iban').className = 'required-entry';
        if (document.getElementById('ratepay_directdebit_element_bic')) {
            document.getElementById('ratepay_directdebit_element_bic').style.display = 'block';
            document.getElementById('ratepay_directdebit_bic').className = 'required-entry';
        }
        document.getElementById('ratepay_directdebit_element_accountnumber').style.display = 'none';
        document.getElementById('ratepay_directdebit_element_bankcode').style.display = 'none';
        document.getElementById('ratepay_directdebit_account_number').className = '';
        document.getElementById('ratepay_directdebit_bank_code_number').className = '';
    }
}

function showAgreement(){
    document.getElementById('ratepay_directdebit_sepa_agreement').style.display = 'inline-block';
    document.getElementById('ratepay_directdebit_sepa_agreement_link').style.display = 'none';
}
