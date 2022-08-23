# Ratepay GmbH - Magento Payment Module
============================================

|Module | Ratepay Module for Magento
|------|----------
|Author | Fatchip GmbH
|Shop Version | `CE` `1.7.x` `1.8.x` `1.9.x` `EE` `1.12.x` `1.13.x` `1.14.x`
|Version | `4.3.0`
|Link | https://www.ratepay.com
|Mail | integration@ratepay.com
|Installation | see separate installation manual
|Terms of service / Nutzungsbedingungen | http://www.ratepay.com/nutzungsbedingungen
|Legal-Disclaimer|https://ratepay.gitbook.io/docs/#legal-disclaimer


## Installation
* clone recursive repository

````bash
git clone --recursive https://github.com/ratepay/magento1.7-module.git
````
* update submodule
````bash
git submodule update --remote
````

## Changelog

### Version 4.3.0 - Released 2022-08-23
* Fixed : Buggy code in admin order overview
* Fixed : Adjust style of rate calculator buttons
* Update : Docblock headers
* Update : Confirm check box for direct debit agreement
* Update : Remove DueDays config
* Update : Config option

### Version 4.2.0 - Released 2021-09-21
* Fix : module chaining issue
* Update : Add automatic VatID field filling from billing address VatID
* Update : Remove the option to allow AccountNr/BIC data for bank transfer

### Version 4.1.10 - Released 2020-10-19
* Fix : Use correct basket max limit for B2B checkout

### Version 4.1.9 - Released 2020-09-28
* Update : New SEPA mandate text for Ratepay inhouse installment payment method
* Update : legal text and links updated
* Update : VatId field in checkout turned into optional
* Update : Default phone number transmitted if missing during checkout payment
* Update : Account holder for SEPA transaction adapted for B2B (choice given between Customer name and Company)

### Version 4.1.8 - Released 2020-07-02
* Fix/Clean : remove unnecessary config fields
* Update/Docs : add license file
* Update : rewrite the Ratepay brand in various places
* Update : add codes 720 and 721 to the list of 48h ban reasons

### Version 4.1.7 - Released 2020-03-12
* Fix re-order function from admin
* Transmits invoice number with delivery request
* Include method hiding from checkout for 48h after rejection
* Update legal text

### Version 4.1.6 - Released 2019-12-19
* Enabled negative credit memos for ratepay payment types
* Fixed specific group assignments to RatePay payment types for backend orders

### Version 4.1.5 - Released 2019-10-16
* Fixed birthday for backend orders
* Fixed Vat-Id handling for backend-orders
* Set the email address field mandatory for backend order

### Version 4.1.4 - Released 2019-10-02
* Fixed Javascript- and layout-problems in backend orders
* Fixed problems when installment and 0% installment was active at the same time

### Version 4.1.3 - Released 2019-06-20
* Add config field for device fingerprint snippet ID
* Add usage of a default "ratepay" value when snippet ID field is not set up

### Version 4.1.2 - Released 2019-01-28
* Updated RatePAY GmbH Legal Link

### Version 4.1.1 - Released 2018-12-11

* french translations
* cleaned languages files
* item bundles with price of 0
* fixed incorrect partial-return when setting a debit

### Version 4.1.0 - Released 2018-07-30

* implement switch between special items and basket items
* fix backend-orders with installment
* fix fatal errors with strict php configuration
* text revisions on rejection messages
* text complaining with GDPR
* complete translation into english
* adapt display of installment form
* remove VatID Field

### Version 4.0.3 - Released 2018-05-07
* add terms and conditions
* add compatibility for the old ratepay request structure

### Version 4.0.2.1 - Released 2018-03-15
* Change Company field to CompanyName with customer data

### Version 4.0.2 - Released 2017-11-30
* Change profile request library call

### Version 4.0.1.1 - Released 2017-11-30
* change RatePAY company address

### Version 4.0.1 - Released 2017-09-04
* New installment calculator design
* Added Belgium as supported Country

### Version 4.0.0 - Released 2017-07-10
* Implementation of new core library (API1.8)
* Changed DFP logic

### Version 3.2.5 - Released 2017-05-23
* Fix of missing block files

### Version 3.2.4 - Released 2017-05-16
* Implementation of installment + direct debit

### Version 3.2.3.2 - Released 2017-03-15
* Specific category feature shorted
* Added some translations and expanded EN translation by en_GB


### Version 3.2.3.1 - Released 2017-03-14
* Fix of various notice & warning bugs

### Version 3.2.3 - Released 2017-03-01
* SEPA - BIC field removed
* IBAN country prefix validation removed

### Version 3.2.2 - Released 2017-02-08
* Relocated device fingerprinting
* Fixed return of payment information within order details
* Fixed missing tax rate of discount items
* Fixed collisions with shop operations and other payment methods

### Version 3.2.1 - Released 2016-12-16
* Payment method activation status 'phased out' added
* Extended Responses implemented
* Installment calculator issue in backend order fixed
* Date of birth issue in backend order fixed
* Ability to create new customer within backend order
* Prevent missing taxPercent index

### Version 3.2.0 - Released 2016-11-02
* Compatibility with SUPEE-8788`
* Support backend orders
* Support reward points
* Support enterprise edition
* Device fingerprint without flash
* Rate calculator - hiding unavailable runtimes

### Version 3.1.5 - Released 2016-09-06
* Separate vat id validation on ids from DE, AT, NL and CH
* Fix of wrong dob validation on direct debit b2b orders
* Removed type suffix in payment transaction id
* Change of expression in language files

### Version 3.1.4 - Released 2016-08-18
* Hiding payment issue fixed
* Payment methods renamed
* 2.5.4 - 3.0.0 update issue fixed
* Removed customer and payment data from request-call
* Account-type buttons improved
* Adjustment-fee and refund renamed
* Debit implemented

### Version 3.1.3 - Released 2016-06-08
* Compatibility with SUPEE-6788 and APPSEC-1034
    * addressing bypassing custom admin URL
* Now refund and adjustment in one operation possible

### Version 3.1.2 - Released 2016-05-11
* Uncovered CH config
* Fixed currency by Profile Request
* Support of NL orders/customers and language

### Version 3.1.1 - Released 2015-11-25
* Fixed config.xml
* Added sandbox notification

### Version 3.1.0 - Released 2015-11-23
* Added new payment method 0%-Finanzierung
* Refactoring of installment forms and plan
* Few code refactorings

### Version 3.0.7 - Released 2015-10-28
* SEPA form redesign
* IBAN only configurable

### Version 3.0.6 - Released 2015-10-20
* bugfix of partial return logic

### Version 3.0.5 - Released 2015-10-16
* persistent credentials as additional payment information
* preloaded installment plan in case of just one month allowed
* installment plan on order review page
* textual changes in payment method forms
* fex changes to avoid php notices

### Version 3.0.4 - Released 2015-09-10
* DFP output moved from footer to checkout
* full DFP config via PROFILE REQUEST
* changed token generation

### Version 3.0.3 - Released 2015-07-07
* changes in backend config
* CH/CHF ready
* bugfixes

### Version 3.0.2 - Released 2015-05-13
* changed invoice event from save to register

### Version 3.0.1 - Released 2015-05-04
* fixed bug in unit-price-gross calculation
* deactivated installment plan on order review

### Version 3.0.0 - Released 2015-04-13
* changed namespace und module name
* new backend configuration
* configuration via PROFILE REQUEST; includes credential and country validation
* persistent installment config (months allowed, min rate)
* support of DE and AT credentials in same sub shop
* product categories excludable
* customer groups excludable
* shipping methods excludable
* module order processes (cancel, invoice/shipment, creditmemo) seperatly deactivatable
* changed basket struktur (api v1.6)
* installment plan on order summery page
* improved IBS/PAYMENT QUERY
* few smaller fixes and changes

### Version 2.5.1 - Released 2015-01-30
* permits shipping without invoicing
* customer block only on PR

### Version 2.5.0 - Released 2014-12-08
* fixed installment without PAYMENT CONFIRM
* no full-return anymore
* no negative basket amount anymore
* ZGB-DSE link by default
* full integration of AT support
* address normalization

### Version 2.4.5 - Released 2014-10-28
* cURL SSLVERSION to 1 (TLS1.x)

### Version 2.4.4 - Released 2014-10-22
* changed RatePAY gateway URL
* switch to interest rate in Ratenrechner

### Version 2.4.3 - Released 2014-09-05
* fixed payment method overlay
* changed PC - prevention of multiple PC
* removed pdf and email generation

### Version 2.4.2 - Released 2014-07-09
* dob bugfix and refactoring the method of input
* added validation of vat id and phone number
* added merchant cunsumer id
* changes in language files
* fixed PQ Payment Init bug
* disabled DOB in case of B2B
* fixed VatId bug and added changeable VatId form

### Version 2.4.1 - Released 2014-06-04
* bugfix in Payment Query configuration

### Version 2.4.0 - Released 2014-04-30
* added Payment Query
* extended Whitelabel mode
* fixed company-name xml tag in customer block
* removed deprecated http-header-list block in xml head
* removed agreement box (invoice/installment); not needed anymore

### Version 2.3.0 - Released 2014-04-01

* inplementation of Device Ident within main footer
* added separat upgrade file for each release version - solves troubles in case of modul updates
* added Whitelabel mode

### Version 2.2.3 - Released 2014-02-13
* minor changes in the checkout layout

### Version 2.2.2 - Released 2014-02-06
* IBAN validation without JS

### Version 2.2.1 - Released 2014-01-30
* improved IBAN validation
* changed sandbox mode - no decline of rp payment methods after negative response while sandbox mode

### Version 2.2.0 - Released 2014-01-29
* added SEPA functionality - includes IBAN and BIC fields and new text blocks
* deactivated saving of user bank account data

### Version 2.1.1 - Released 2014-01-07
* better bundle handling
* disable partial shipping refund

### Version 2.1.0 - Released 2013-11-21
* allow differing billing and shipping addresses
* send customer name and company (if necessary) within shipping address

### Version 2.0.9 - Released 2013-11-20
* remove bundle article from requests and send only the bundled items
* fix guest checkout elv data saving problem

### Version 2.0.8 - Released 2013-05-23
* fix wrong payment fee behavior after second selection of
   RatePAY

### Version 2.0.7 - Released 2012-09-04
* fix payment fee
* fix wrong email in creditmemo footer

### Version 2.0.6 - Released 2012-03-05
* fix different behavior for article bundles

### Version 2.0.5 - Released 2012-02-15
* fix wrong path for the rp footer image in the invoice
* set the _canAuthorize Payment property to "false"
* fix partial return bug

### Version 2.0.4 - Released 2012-11-07
* changed default Theme to base Theme
* fixed encryption constant Bug
* fixed a bug, where you couldn't hide payment methods

### Version 2.0.3 - Released 2012-10-23
* fix encryption UTF-8 Bug

### Version 2.0.2 - Released 2012-09-26
* fix discount tax

### Version 2.0.1 - Released xxxx-xx-xx
* add enterprise template support
* fix wrong transfer of item data after a retour
* fix missing translation for shipping error

### Version 2.0.0 * Released 2012-06-27
* use the native shop functions to invoice, refund and cancel
* add bulk cancel, refund, invoice support
* add RatePAY xml log for every order
* add config view for merchant
* add ELV support

### Version 1.4.4 * Released 2012-02-16
* fixed AGB Bug

### Version 1.4.3 * Released 2012-02-16
* added terms of use for RatePAY

### Version 1.4.2 * Released 2012-02-14
* changed AGB Check
* changed requirement for RatePAY Configs
* added default values for RatePAY Configs
* changed templates to work with Enterprise

### Version 1.4.1 * Released 2012-02-13
* fixed Mantis Bug 0000604

### Version 1.4.0 * Released 2012-02-07
* Added Payment Method RatePAY Rate

### Version 1.3.0 * Released 2012-01-23
* rewritten from scratch
