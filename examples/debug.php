<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../TrueVault/TrueVault.php");

define("TRUEVAULT_API_KEY", "12345678-1234-1234-1234-123456789012");
define("TRUEVAULT_ACCOUNT_ID", "12345678-1234-1234-1234-123456789012");
define("TRUEVAULT_VAULT_ID", "12345678-1234-1234-1234-123456789012");

$trueVault = new TrueVault(array(
    "apiKey" => TRUEVAULT_API_KEY,
    "accountId" => TRUEVAULT_ACCOUNT_ID
));

$trueVault->setDebug(true);

$schemas = $trueVault->schemas(TRUEVAULT_VAULT_ID);
$documents = $trueVault->documents(TRUEVAULT_VAULT_ID);
$blobs = $trueVault->blobs(TRUEVAULT_VAULT_ID);

// Download file from truevault
$blobId = "12345678-1234-1234-1234-123456789012";
$response = $blobs->download($blobId, "output_file.bin");

echo "Response from server: ";
var_dump($response);