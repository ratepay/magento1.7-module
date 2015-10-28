
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
        if (document.getElementById('ratepay_directdebit_element_bic')) {
            document.getElementById('ratepay_directdebit_element_bic').style.display = 'none';
        }
        document.getElementById('ratepay_directdebit_element_accountnumber').style.display = 'block';
        document.getElementById('ratepay_directdebit_element_bankcode').style.display = 'block';
    } else {
        document.getElementById('ratepay_directdebit_classic_switch').className = 'button';
        document.getElementById('ratepay_directdebit_sepa_switch').className = 'button2';
        document.getElementById('ratepay_directdebit_element_iban').style.display = 'block';
        if (document.getElementById('ratepay_directdebit_element_bic')) {
            document.getElementById('ratepay_directdebit_element_bic').style.display = 'block';
        }
        document.getElementById('ratepay_directdebit_element_accountnumber').style.display = 'none';
        document.getElementById('ratepay_directdebit_element_bankcode').style.display = 'none';
    }
}