Import UCRM invoices to QuickBooks Online
=== 
With these basic scripts you can import your [UCRM](https://ucrm.ubnt.com/) invoices to 
[QuickBooks Online](https://quickbooks.intuit.com/). This is not full SDK but may show you the way.


Connection with QuickBooks
---
###1. QuickBook - Create App
- At [Intuit Developer](https://developer.intuit.com/) create developer account.
- After registration process create new app. Choose **Select APIs** and check **Accounting** and **Payments** option.

###2. QuickBook - App setting
- At App Dashboard use **Keys** tab and fill **Redirect URI**.
- At App Dashboard use **Keys** tab and copy **Client ID** and **Client Secret**.

###3. Setting of import scripts 
- Copy file ``/config.dist/quickbooks.php`` to ``/config/quickbooks.php``.
- In file ``/config/quickbooks.php`` fill:
  * ```QUICKBOOKS_OAUTH_REDIRECT_URI``` with **Redirect URI**,
  * ```QUICKBOOKS_OAUTH_CLIENTID``` with **Client ID**,
  * ```QUICKBOOKS_OAUTH_CLIENTSECRET``` with **Client Secret**.

###4. Setup and confirmation connection between yours App and QuickBook App
- In command line change directory to ``/scripts/quickbooksonline_import``.
- Install dependency with composer ``$ composer install``.
- Run ``$ php OAuth2UrlGenerator.php`` and follow returned Url with browser. For production environment secure link 
with CSRF token (parameter ```state```).
- Browser will show you page where confirm connection between your QuickBooks App and your script.
- After confirmation you will be redirected to your **Redirect URI** with two parameters ```code``` and ```realmId```. 
Parameter ```code``` is used in this script as constant ```QUICKBOOKS_OAUTH_CODE``` and parameter ```realmId``` 
is constant ```QUICKBOOKS_OAUTH_REALMID```. 

###5. Obtaining and refreshing OAuth tokens
- Run ``$php OAuth2TokenGenerator.php``  and QuickBooks returns two tokens: **AccessToken** and 
**RefreshToken**.
- **AccessToken** is valid for one hour. You can obtain new **AccessToken** with last returned **RefreshToken** which is valid for 100 days.
- Time to request a new **AccessTokn** is simply when a QuickBooks returns HTTP 401 error.
- To refresh **AccessToken** run ```$php OAuth2TokenRefresh.php``` which returns fresh **AccessToken** and **RefreshToken**.

Import from UCRM to QuickBooks
---
###1. Import Customers
- Customers imports runing command ```$php customer.php``` imports with UCRM ID added after clients name.

###2. Import Invoices
- Edit in file ```invoices.php``` account number to which items from invoices belongs.
- Run ```$php invoices.php``` to import invoices.

###3. Import Payments
- Run ```$php payments.php``` to import payments.
