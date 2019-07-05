# Version 4.33
* Fixed a bug where invoices couldn't be found on case sensitive database engines.
* Fixed a bug where invoice emails weren't sent in some cases since WHMCS 7.x.
* Display error messages before batch generation instead of at the time of a batch download.
* Improved the speed of localAPI()-calls when opening the Incasso module.

# Version 4.32
* Fixed a bug where the company was displayed instead of the customer.
* Fixed a bug where the payment method could not be found.
* Fixed a bug where the direct debit menu was always displayed even when a customer wasn't logged in.
* SEPA guidelines have changed. We now allow batches to be create a day in advance instead of 3.

# Version 4.31
* Fixed a bug where language strings couldn't be loaded
* Fixed a bug where the primary navigation couldn't be loaded
* Fixed a bug where the signature wasn't saved
* Fixed a bug where the redirect didn't work when saving the mandate
* Support added for language overrides

# Version 4.3
* Fixed strict coding errors
* Fixed a bug where city validation always failed
* Fixed a bug where the country file couldn't be loaded
* Fixed a bug handling customfields in the clientarea
* Fixed a bug where localAPI() doesn't work due to a bug in WHMCS
* Support added for BIC tooltips
* Support added for clients to set their default payment method

# Version 4.2
* Fixed a bug that caused downloads to fail
* Fixed a bug linking to our knowledgebase
* Fixed a bug where original invoice calculation was off
* Fixed a bug so payment processing doesn't time out
* Fixed a bug where negative invoice totals were accepted
* Fixed an undefined index error
* Support added for print stylesheets
* Support added for a customer widget
* Support added for our own payment gateway
* Reversed sorting for batches

# Version 4.1
* We only support WHMCS version 6 and higher from now on.
* Support for the new database manager in WHMCS 6.
* Support added for a payment description prefix, eg. 'Sensson - Invoice: 1' instead of just 'Invoice: 1'. We will add a divider automatically.
* Support added for individual invoice selections, e.g. only invoices that have the direct debit gateway selected.
* Removed support for Clieop03. We only support SEPA from version 4.1.
* Removed support for the conversion tool. It's not a free service.
* Rewrite and removal of old code and old internal functions.
* Improved error reporting on IBAN and BIC errors making it clearer where the error is coming from.
* Improved error checking in the frontend when managing direct debit batches.
* Improved layout conforming to the bootstrap theme.
* Improved support for normalization of characters.

# Version 3.25
* Support for WHMCS 6
* Fixed a bug where html_options couldn't load.
* Cleaned up the admin interface so it fits in the new WHMCS templates.
* Increased security.

# Version 3.24
* Added support for global overrides to download a complete batch as FRST or RCUR.
* Added support for custom customer mandates with backwards compatibility to the default (CID-CLIENTID): we recommend adding a custom
  client field for this instead of using the default. This feature adds support for changing customer bank accounts. You can change the
  mandate to CID-CLIENTID-2 for example when this is necessary. Please note that this is not done automatically for you and you are
  required to change both the bank account as the mandate manually. You would also need to make sure that when creating the new batch
  the invoices are set to FRST.
* Fixed a bug that stopped the module from working when the a hidden dependency on bcmod() was not available.
* Added an empty index.php file so crawlers can't index this directory.
* Added support for unique downloads and no caching
* Added support for specifying when to execute a batch
* Fixed a bug that gave the impression that new batches could be created when no matching invoices were found

# Version 3.23
* Resolved a bug where BIC codes were wrongly marked invalid
* Added more information on test mode in the templates
* Changed the installation for WHMCS version 5.3.x, function.html_options.php needs to be uploaded in includes/classes/Smarty/plugins
* Added support for overriding first and recurring batches for certain invoices when creating a new batch.
* Fixed some spelling mistakes.

# Version 3.22
* Added a function to verify the mandate reference.
* We are now normalizing characters in the XML file to ensure they can be processed properly.

# Version 3.21
* Add the Direct Debit payment gateway as a payment module
	- Setup > Payment > Payment Gateways;
	- Select Direct Debit from the drop down and click Activate;
	- Scroll down and make sure the box that says 'Show on Order Form' is unticked;

	We won't be using this module to decide if a customer should be a direct debit customer. For the moment it
	will only be used to track payments made using Direct Debit.

	* When applying payments: make sure to select the Direct Debit gateway or process them from within this module to
 	  ensure they will be applied properly and future direct debits are built as required by SEPA.

# Version 3.2
* Test mode and the conversion tool both use the API of openiban.nl. This free service is not 100% accurate and we
  recommend verifying customer details manually.
