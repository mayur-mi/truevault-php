<?php

// please note that this script will execute very slowly

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

$documents = $trueVault->documents(TRUEVAULT_VAULT_ID);

// create plenty of documents
$documentIds = [];
for ($i = 0; $i < 500; $i++) {
    $response = $documents->create(array("text" => "test{$i}test", "i" => $i));
    $documentId = $response["document_id"];
    $documentIds[] = $documentId;
}

// request them
$documentsData = $documents->get($documentIds);

for ($i = 0; $i < 500; $i++) {
    $documentId = $documentIds[$i];
    $documentData = $documentsData[$documentId];

    if ($documentData["i"] !== $i)
        die("Something went wrong");

    if ($documentData["text"] !== "test{$i}test")
        die("Something went wrong");
}

echo "All data stored and retrieved successfully";