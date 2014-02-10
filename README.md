Welcome to Stripe Invoice Template
==================================
This a super simple PHP invoice template for a Stripe invoice object built using Twitter's Bootstrap. It will list all Invoice items, allow users to pay for the invoice using Stripe's Checkout.js, and see paid invoices.


* Author:    Danny Summerlin
* Date:      February 10, 2014
* Last mod.: February 10, 2014
* Version:   0.6.0
* Website:   <https://craftedbycreo.com>
* GitHub:    <https://github.com/craftedbycreo/Bootstrap-Stripe-Invoice-Template>


Configure 
=========

Change the following variables to match your settings.

`$companyName = "Your Company Name";  
$address = <<<EOD  
Street 1<br>  
Street 2<br>  
City, State Zip<br>  
EOD;  
$phone = "888-888-8888";  
$baseURL = "http://yourwebsite.com";  
$baseEmail = "hello@yourwebsite.com";  
$icon = $baseURL."/assets/img/favicon.png";  
$stripeKey = "sk_test_";  
$publicKey = "pk_test_";`  

Use by getting the invoice ID from Stripe and send your client a link like https://yourwebsite.com/invoice.php?i=in_lkj34234nkj32alkj324