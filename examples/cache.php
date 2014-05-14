<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../TrueVault/TrueVault.php");

define("TRUEVAULT_API_KEY", "12345678-1234-1234-1234-123456789012");
define("TRUEVAULT_ACCOUNT_ID", "12345678-1234-1234-1234-123456789012");
define("TRUEVAULT_VAULT_ID", "12345678-1234-1234-1234-123456789012");

class MyCustomCache extends TrueVaultCache
{
    private $memcache;
    private $expiration;

    public function __construct() {
        $this->memcache = new Memcached();
        $this->expiration = 300;
    }

    public function get($vaultId, $documentId)
    {
        $data =  $this->memcache->get("{$vaultId}:{$documentId}");
        if ($data)
            return unserialize($data);

        return null;
    }

    public function set($vaultId, $documentId, $data)
    {
        $this->memcache->set("{$vaultId}:{$documentId}", serialize($data), $this->expiration);
    }
}

$trueVault = new TrueVault(array(
    "apiKey" => TRUEVAULT_API_KEY,
    "accountId" => TRUEVAULT_ACCOUNT_ID
));

// set caching
$trueVault->setCache(new MyCustomCache());

$schemas = $trueVault->schemas(TRUEVAULT_VAULT_ID);
$documents = $trueVault->documents(TRUEVAULT_VAULT_ID);
$blobs = $trueVault->blobs(TRUEVAULT_VAULT_ID);

// documents
var_dump($response = $documents->create(array("name" => "Don Joe", "email" => "some nice email")));
$documentId = $response["document_id"];

// this will be much faster with the cache
for ($i = 0; $i < 10; $i++)
    var_dump($documents->get($documentId));

var_dump($documents->update($documentId, array("name" => "Don Joe Two")));
var_dump($documents->get($documentId));
var_dump($documents->delete($documentId));
var_dump($documents->get($documentId));