
function showElvFields(id)
{
    document.getElementById(id).style.display = 'block';
}

function hideElvFields(id)
{
    document.getElementById(id).style.display = 'none';
}

function switchAccountType(element) {
    if (isNaN(element.value)) {
        element.name = 'payment[ratepay_directdebit_iban]';
        document.getElementById('li_ratepay_directdebit_bank_code_number').style.display = 'none';
    } else {
        element.name = 'payment[ratepay_directdebit_account_number]';
        document.getElementById('li_ratepay_directdebit_bank_code_number').style.display = 'inline-block';
    }
}