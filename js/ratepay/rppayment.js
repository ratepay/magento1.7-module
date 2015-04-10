function alterVatId(method)
{
    document.getElementById('vatIdLabel_' + method).style.display = 'none';
    document.getElementById('vatIdForm_' + method).style.display = 'inline-block';
}