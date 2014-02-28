<?php

require_once("../lib/truevault.php");

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

var_dump($trueVault->findAllVaults());

// delete all previous schemas
// foreach ($truevault->schemas($vaultId)->findAll() as $schemaId => $value)
//    $truevault->schemas($vaultId)->delete($schemaId);

// schemas
var_dump($schema = $schemas->create(array("name" => "name", "fields" => array(array("name" => "name", "index" => true, "type" => "string")))));
$schemaId = $schema["id"];
var_dump($schemas->get($schemaId));
var_dump($schemas->update($schemaId, array("name" => "user", "fields" => array(array("name" => "name", "index" => true, "type" => "string")))));
var_dump($schemas->get($schemaId));
var_dump($schemas->findAll());

// documents
var_dump($response = $documents->create(array("name" => "Don Joe")));
$documentId = $response["document_id"];
var_dump($documents->get($documentId));
var_dump($documents->update($documentId, array("name" => "Don Joe Two")));
var_dump($documents->get($documentId));
var_dump($documents->delete($documentId));

// searchable document
var_dump($response = $documents->create(array("name" => "Jane Doe"), array("schema_id" => $schemaId)));
$documentId = $response["document_id"];

// search
var_dump($documents->search(array("filter" => array("name" => "Jane Doe"))));

// clean up
var_dump($documents->delete($documentId));
var_dump($schemas->delete($schemaId));

// blobs
var_dump($blob = $blobs->upload("input_file_1.bin"));
$blobId = $blob["blob_id"];

var_dump($blobs->upload("input_file_2.bin", $blobId)); // replace existing
var_dump($blobs->download($blobId, "output_file.bin"));
var_dump($blobs->delete($blobId));