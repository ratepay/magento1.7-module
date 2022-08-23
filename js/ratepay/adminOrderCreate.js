/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This function is triggered to handle the email address requirement status
 * Email address is mandatory for Ratepay methods
 * For other methods, it should follow the standard behaviour (most probably : not required)
 */
function initPaymentMethodListeners() {
    var emailField = $('email');
    if ('undefined' !== typeof emailField && emailField !== null) {

        // The field initialEmailRequirement is used to store the standard rule
        // for email address requirement
        var initialEmailRequirement = $('email_default_requirement');
        if ('undefined' === typeof initialEmailRequirement || initialEmailRequirement === null) {
            // If the field doesn't exist, it has to be created and appended
            // next to the email address field
            initialEmailRequirement = document.createElement('input');
            Element.extend(initialEmailRequirement);
            initialEmailRequirement.type = 'hidden';
            initialEmailRequirement.id = 'email_default_requirement';
            emailField.parentElement.appendChild(initialEmailRequirement);
        }

        // If empty, the value of the field depends on the initial requiment status of email address field
        // The presence/absence of "require-entry", is considered as the standard behaviour
        if (initialEmailRequirement.value === '') {
            initialEmailRequirement.value = emailField.hasClassName('required-entry') ? '1' : '0';
        }

        // The payment method selection radio buttons are attached to a function
        // that will update the requirement status of the email address field
        // base on the selected payment method
        var methodSwitches = $$("[id^=p_method_]");
        methodSwitches.forEach(function(method) {
            method.on('change', function(event) {
                var target = event.target;
                var methodId = target.id;
                var search = methodId.search('ratepay');

                if (search > -1) {
                    setEmailRequired(true);
                }
                else {
                    if (initialEmailRequirement.value === '1') {
                        setEmailRequired(true);
                    } else {
                        setEmailRequired(false);
                    }
                }
            });
        });
    }
}

/**
 * This function (un)sets the email address field as required, based on the parameter
 *
 * @param required boolean
 */
function setEmailRequired(required)
{
    var emailField = $('email');
    if ('undefined' !== typeof emailField && emailField !== null) {
        if (required) {
            emailField.addClassName('required-entry');
        } else {
            emailField.removeClassName('required-entry');
        }
    }
}
