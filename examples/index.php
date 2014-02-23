<?php

require_once("../lib/truevault.php");

define("TRUEVAULT_VAULT_ID", "12345678-1234-1234-1234-123456789012");
define("TRUEVAULT_API_KEY", "12345678-1234-1234-1234-123456789012");
define("TRUEVAULT_ACCOUNT_ID", "12345678-1234-1234-1234-123456789012");

$trueVault = new TrueVault(array(
    "apiKey" => TRUEVAULT_API_KEY,
    "accountId" => TRUEVAULT_ACCOUNT_ID
));

$schemas = $trueVault->schemas(TRUEVAULT_VAULT_ID);
$documents = $trueVault->documents(TRUEVAULT_VAULT_ID);

var_dump($trueVault->findAllVaults());

// delete all previous schemas
// foreach ($truevault->schemas($vaultId)->findAll() as $schemaId => $value)
//    $truevault->schemas($vaultId)->delete($schemaId);

// schemas
var_dump($schemas->create(array("name" => "name", "fields" => array(array("name" => "name", "index" => true, "type" => "string")))));
$schemaId = $schemas->lastInsertId();
var_dump($schemas->get($schemaId));
var_dump($schemas->update($schemaId, array("name" => "user", "fields" => array(array("name" => "name", "index" => true, "type" => "string")))));
var_dump($schemas->get($schemaId));
var_dump($schemas->findAll());

// documents
var_dump($documents->create(array("name" => "Don Joe")));
$documentId = $documents->lastInsertId();
var_dump($documents->get($documentId));
var_dump($documents->update($documentId, array("name" => "Don Joe Two")));
var_dump($documents->get($documentId));

// search
var_dump($documents->search(array("page" => 1, "per_page"=> 3,"filter" => array("name" => array("type" => "not", "value" => "Bad Name")))));

// clean up
var_dump($schemas->delete($schemaId));
var_dump($documents->delete($documentId));