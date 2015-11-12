# uk.co.nfpservice.onlineworldpay
CiviCRM Online Worldpay Payment Processor

--
Extension Name	: 	uk.co.nfpservice.module.worldpay
Create By		:	Ramesh
Email Id		:	Ramesh@millertech.co.uk
Website			:	https://www.nfpservices.co.uk/

--

# REQUIREMENT 


This module was developed based on CiviCRM 4.6 and Drupal 7.x. - hasn't been tested in any other version - 
Please give a test and if you want to use it in lower or higher version. 
Please feel free to contact me if you have any issues. you can find the 
contact details at the footer of this document.

As This Extension is based on Online.Worldpay.com 

Where Worldpay has two gateway 
	http://www.worldpay.com/uk
	https://online.worldpay.com/

So, This extension is based on new gateway https://online.worldpay.com/
Please make sure you have an account with https://online.worldpay.com/

--
# INSTALLATION INSTRUCTIONS


To install the uk.co.nfpservice.module.worldpay, move the 
`uk.co.nfpservice.module.worldpay` directory to your civicrm Custom extension folder directory.

and Install the extension.

--
# HOW TO USE


After installing the extension. please create an payment processor for on World pay 

In the create payment processor you will be made available of the new payment processor name "online worldpay using API"

set the financial type to "payment processor Account"

Processor Details for Live/Test Payments :

Please get the service key and client key from your online.worldpay.com account setting
(In order to get service key / client key you need to create an account with them first)

this extension uses only https://api.worldpay.com/v1/ api for accessing the website so this link might change time to time 
so please make sure you are using the correct URL given on their site 

for more documentation regarding the API please refer to https://online.worldpay.com/docs 

--
# FUTURE REQUIREMENT

	Recurring Payment 
	Webhook Integration
	
--
# CURRENT RELEASE
	Compatible with CiviCRM 4.6.x and Drupal 7.x. 
	
	Currently tested in CiviCRM 4.6 / Drupal 7.x with Webform CiviCRM Integration 7.x - 7.14
		
--
# CONTACT INFORMATION

                                                 
All feedback and comments of a technical nature (including support questions)
and for all other comments you can reach me at the following e-mail address. Please
include "uk.co.nfpservice.module.worldpay" somewhere in the subject.

RAMESH AT MILLERTECH DOT CO DOT UK

License Information
---------------------------------------

Copyright (C) Miller Technology 2015









