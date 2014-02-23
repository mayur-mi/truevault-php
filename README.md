# TrueVault PHP Client Library

This is unofficial TrueVault PHP client library that enables developers to implement a rich set of server-side functionality for accessing TrueVault's API.
[TrueVault](https://www.truevault.com/)

## Requirements
- PHP 5
- Curl PHP extension
- Json PHP extension

## Examples
```php
require_once("lib/truevault.php");

define("TRUEVAULT_VAULT_ID", "12345678-1234-1234-1234-123456789012");
define("TRUEVAULT_API_KEY", "12345678-1234-1234-1234-123456789012");
define("TRUEVAULT_ACCOUNT_ID", "12345678-1234-1234-1234-123456789012");

$trueVault = new TrueVault(array(
    "apiKey" => TRUEVAULT_API_KEY,
    "accountId" => TRUEVAULT_ACCOUNT_ID
));

// get all vaults
// $trueVault->findAllVaults();

$schemas = $trueVault->schemas(TRUEVAULT_VAULT_ID);
$documents = $trueVault->documents(TRUEVAULT_VAULT_ID);
```

Documents methods
```php
$documents->create(array("name" => "Don Joe"));
$documentId = $documents->getLastCreatedId();

$documents->get($documentId);
$documents->update($documentId, array("name" => "Don John"));
$documents->delete($documentId);
$documents->search(array("page" => 1, "per_page"=> 3,"filter" => array("name" => array("type" => "not", "value" => "Susan"))
```

Schemas methods
```php
$schemas->create(array("name" => "name", "fields" => array(array("name" => "name", "index" => true, "type" => "string"))));
$schemaId = $schemas->getLastCreatedId();

$schemas->get($schemaId);
$schemas->update($schemaId, array("name" => "user", "fields" => array(array("name" => "name", "index" => true, "type" => "string"))));
$schemas->get($schemaId);
$schemas->findAll();
$schemas->delete($schemaId);
```

## Resources
[TrueVault REST API Documentation](https://www.truevault.com/documentation/rest-api.html)

## TODO
- Add BLOB support

## Author
- [Marek Vavrecan](mailto:vavrecan@gmail.com)
- [Donate by PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=DX479UBWGSMUG&lc=US&item_name=Friend%20List%20Watcher&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted)

## License
- [GNU General Public License, version 3](http://www.gnu.org/licenses/gpl-3.0.html)