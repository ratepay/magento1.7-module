
function showElvFields(id)
{
    document.getElementById(id).style.display = 'block';
}

function hideElvFields(id)
{
    document.getElementById(id).style.display = 'none';
}

function switchAccountType(element) {
    element.value = element.value.replace(/\s/g, "");
    if (isNaN(element.value)) {
        document.getElementById('li_ratepay_directdebit_bank_code_number').style.display = 'none';
    } else {
        document.getElementById('li_ratepay_directdebit_bank_code_number').style.display = 'inline-block';
    }
}