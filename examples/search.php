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

$schemas = $trueVault->schemas(TRUEVAULT_VAULT_ID);
$documents = $trueVault->documents(TRUEVAULT_VAULT_ID);
$blobs = $trueVault->blobs(TRUEVAULT_VAULT_ID);

$schemas = $schemas->findAll();
$schemaId = array_search("search_by_date", $schemas);

if (!$schemaId) {
    // create schema if it does not exists
    $schema = $schemas->create(
        array("name" => "search_by_date", "fields" =>
            array(
                array("name" => "uid", "index" => true, "type" => "integer"),
                array("name" => "cdate", "index" => true, "type" => "date")
            )
        )
    );
    $schemaId = $schema["id"];
    var_dump($schemas->get($schemaId));
}

// create document with schema
$response = $documents->create(
    array(
        "uid" => 0,
        "cdate" => '2010-10-10',
    ),
    array("schema_id" => $schemaId)
);

$response = $documents->create(
    array(
        "uid" => 23,
        "cdate" => '2014-08-20',
    ),
    array("schema_id" => $schemaId)
);

$documentId = $response["document_id"];
var_dump($documentId);

// search
$response = $documents->search(
    array("schema_id" => $schemaId, "filter" =>
        array("cdate" =>
            array("type" => "gte", "value" => "2012-01-02")
        )
    )
);

// search results
var_dump($response);