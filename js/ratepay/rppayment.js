/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function alterVatId(method)
{
    document.getElementById('vatIdLabel_' + method).style.display = 'none';
    document.getElementById('vatIdForm_' + method).style.display = 'inline-block';
}