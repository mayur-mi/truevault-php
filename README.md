# TrueVault PHP Client Library

This is unofficial TrueVault PHP client library that enables developers to implement a rich set of server-side functionality for accessing TrueVault's API.
- [TrueVault](https://www.truevault.com/)

## Requirements
- PHP >=5.3.0
- Curl PHP extension
- Json PHP extension

## Installation
### Manual installation
Just copy library and include `TrueVault.php` file in your project.

### Using composer
Create or update a `composer.json` file in your project root:

```
{
    "require": {
        "truevault/truevault": "@dev"
    }
}
```

- [Composer documentation](https://getcomposer.org/doc/)

### Examples
```php
define("TRUEVAULT_VAULT_ID", "12345678-1234-1234-1234-123456789012");
define("TRUEVAULT_API_KEY", "12345678-1234-1234-1234-123456789012");
define("TRUEVAULT_ACCOUNT_ID", "12345678-1234-1234-1234-123456789012");

$trueVault = new TrueVault(array(
    "apiKey" => TRUEVAULT_API_KEY,
    "accountId" => TRUEVAULT_ACCOUNT_ID
));

// get all vaults
$vaults = $trueVault->findAllVaults();

$documents = $trueVault->documents(TRUEVAULT_VAULT_ID);
$schemas = $trueVault->schemas(TRUEVAULT_VAULT_ID);
$blobs = $trueVault->blobs(TRUEVAULT_VAULT_ID);
```

### Document methods

#### Create
```php
array create ( mixed $data )
```
** Parameters **
`$data` - Data for document
** Return Values **
returns array with created document id in `document_id` key.
** Example **
```php
$response = $documents->create(array("name" => "Don Joe"));
$documentId = $response["document_id"];
```

#### Get
```php
array get ( string $documentId )
```
** Parameters **
`$documentId` - TrueVault document ID
** Return Values **
returns TrueVault document data on success
** Example **
```php
$data = $documents->get($documentId);
```

#### Update
```php
array update ( string $documentId, mixed $data )
```
** Parameters **
`$documentId` - TrueVault document ID
`$data` - New data for document
** Example **
```php
$documents->update($documentId, array("name" => "Don John"));
```

#### Delete
```php
array delete ( string $documentId )
```
** Parameters **
`$documentId` - TrueVault document ID
** Example **
```php
$documents->delete($documentId);
```

#### Search
** Example **
```php
$documents->search(array("page" => 1, "per_page"=> 3,"filter" => array("name" => array("type" => "not", "value" => "Susan"));
```

### Schema methods
```php
$schema = $schemas->create(array("name" => "name", "fields" => array(array("name" => "name", "index" => true, "type" => "string"))));
$schemaId = $schema["id"];

$schemas->get($schemaId);
$schemas->update($schemaId, array("name" => "user", "fields" => array(array("name" => "name", "index" => true, "type" => "string"))));
$schemas->get($schemaId);
$schemas->findAll();
$schemas->delete($schemaId);
```

### BLOB methods

#### Upload
```php
array upload ( string $path, $blobId = null )
```
** Parameters **
`$path` - local file path - this file will be uploaded on TrueVault server and blob will be created
`$blobId` - if specified existing blob data will be replaced
** Return Values **
Returns true on success
** Example **
```php
$response = $blobs->upload("input_file_1.bin");
$blobId = $response["blob_id"];
$blobs->upload("input_file_2.bin", $blobId); // replace existing
```


#### Download
```php
array download ( $blobId, string $path )
```
** Parameters **
`$blobId` - TrueVault Blob ID
`$path` - local file path - blob data will be downloaded to specified local file
** Return Values **
Returns true on success
** Example **
```php
$blobs->download($blobId, "output_file.bin");
```


#### Delete
```php
array delete ( $blobId )
```
** Parameters **
`$blobId` - TrueVault Blob ID
** Return Values **
Returns true on success
** Example **
```php
$blobs->delete($blobId);
```

## Resources
[TrueVault REST API Documentation](https://www.truevault.com/documentation/rest-api.html)

## Author
- [Marek Vavrecan](mailto:vavrecan@gmail.com)
- [Donate by PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=DX479UBWGSMUG&lc=US&item_name=Friend%20List%20Watcher&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted)

## License
- [GNU General Public License, version 3](http://www.gnu.org/licenses/gpl-3.0.html)