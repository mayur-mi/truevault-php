<?php
/**
 * TrueVault PHP client library
 * More information at https://www.truevault.com/
 *
 * @author Marek Vavrecan <vavrecan@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @version 1.0.0
 */

if (!function_exists('curl_init')) {
    throw new Exception('TrueVault needs the CURL PHP extension.');
}

if (!function_exists('json_decode')) {
    throw new Exception('TrueVault needs the JSON PHP extension.');
}

class TrueVaultException extends Exception
{
    /**
     * The result from the API server that represents the exception information.
     * @var mixed
     */
    protected $result;

    /**
     * Type for the error
     * @var string
     */
    protected $type;

    /**
     * Make a new Exception with the given result
     * @param string $message
     * @param int $code
     * @param string $type
     * @internal param array $result The result from the API server
     */
    public function __construct($message, $code, $type = "Exception") {
        $this->message = $message;
        $this->code = $code;
        $this->type = $type;
    }

    /**
     * Return the associated result object
     * @return array The result from the API server
     */
    public function getResult() {
        return $this->result;
    }

    /**
     * Returns the associated type for the error
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Pretty output
     * @return string
     */
    public function __toString() {
        return $this->type . ": " . $this->message;
    }
}

class TrueVault {
    const VERSION = "1.0.0";
    const API_VERSION = "v1";

    /**
     * Default options for curl.
     * @var array
     */
    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_USERAGENT      => "truevault-php"
    );

    /**
     * @var string
     */
    public static $API_ENDPOINT = "https://api.truevault.com";

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getApiKey() {
        return $this->apiKey;
    }

    /**
     * @var string
     */
    protected $accountId;

    /**
     * @param mixed $accountId
     */
    public function setAccountId($accountId) {
        $this->accountId = $accountId;
    }

    /**
     * @return mixed
     */
    public function getAccountId() {
        return $this->accountId;
    }

    /**
     * Initialize a TrueVault.
     *
     * The configuration:
     * - apiKey: TrueVault application key
     * - accountId: TrueVault account ID
     *
     * @param array $config The application configuration
     */
    public function __construct($config) {
        $this->setApiKey($config["apiKey"]);
        $this->setAccountId($config["accountId"]);
    }

    /**
     * Build the URL for given path and parameters.
     *
     * @param string $path   Optional path (without a leading slash)
     * @param array  $params Optional query parameters
     *
     * @return string The URL for the given parameters
     */
    protected function getUrl($path = "", $params = array()) {
        $url = self::$API_ENDPOINT . "/" . self::API_VERSION . "/";

        if ($path) {
            if ($path{0} === "/") $path = substr($path, 1);
            $url .= $path;
        }

        if ($params) {
            $url .= "?" . http_build_query($params, null, "&");
        }

        return $url;
    }

    /**
     * Invoke the API.
     *
     * @param string $path The path (required)
     * @param string $method The http method (default 'GET')
     * @param array $params The query/post data
     *
     * @return mixed The decoded response object
     * @throws TrueVaultException
     */
    public function api($path, $method = "GET", $params = array()) {
        $url = $this->getUrl($path);

        $ch = curl_init();
        $opts = self::$CURL_OPTS;

        // pass post fields as string instead of array so curl file uploads are not supported here
        $opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_CUSTOMREQUEST] = $method;
        $opts[CURLOPT_USERPWD] = $this->getApiKey() . ":";

        curl_setopt_array($ch, $opts);

        $result = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $errno = curl_errno($ch);

        if ($errno != 0) {
            self::errorLog(curl_error($ch));
        }

        // decode response if returned as json
        if ($contentType == "application/json")
            $result = json_decode($result, true);

        // handle error of no result
        if ($result === false) {
            $e = new TrueVaultException(curl_error($ch), curl_errno($ch), "CurlException");
            curl_close($ch);
            throw $e;
        }

        // handle error within response
        if (is_array($result) && array_key_exists("error", $result)) {
            $e = new TrueVaultException($result["error"]["message"], $result["error"]["code"], $result["error"]["type"]);
            curl_close($ch);
            throw $e;
        }

        curl_close($ch);
        return $result;
    }

    /**
     * Obtain list of vaults for given account Id
     * @return mixed
     * @throws TrueVaultException
     */
    public function findAllVaults() {
        $vaults = $this->api("accounts/{$this->getAccountId()}/vaults");

        if (isset($vaults["vaults"]))
            return $vaults["vaults"];

        throw new TrueVaultException("Unable to obtain list of vaults", 0);
    }

    /**
     * Return True vault document handler
     * @param $vaultId
     * @return TrueVaultDocuments
     */
    public function documents($vaultId) {
        $trueVaultDocuments = new TrueVaultDocuments($this, $vaultId);
        return $trueVaultDocuments;
    }


    /**
     * Return True vault schema handler
     * @param $vaultId
     * @return TrueVaultSchemas
     */
    public function schemas($vaultId) {
        $trueVaultSchemas = new TrueVaultSchemas($this, $vaultId);
        return $trueVaultSchemas;
    }


    /**
     * Prints to the error log if you are not in command line mode.
     * @param string $msg Log message
     */
    protected static function errorLog($msg) {
        if (php_sapi_name() != 'cli') {
            error_log($msg);
        }
    }

    /**
     * Encode given data
     * @param mixed $data
     * @return string
     */
    public function encodeData($data) {
        return base64_encode(json_encode($data));
    }

    /**
     * Decode given data
     * @param string $data
     * @return mixed
     */
    public function decodeData($data) {
        return json_decode(base64_decode($data), true);
    }
}

class TrueVaultDocuments extends TrueVault
{
    /**
     * @var TrueVault API connection class
     */
    private $truevault;

    /**
     * @var string ID of the vault
     */
    private $vaultId;

    /**
     * @var string last created document ID
     */
    private $lastId;

    public function lastInsertId() {
        return $this->lastId;
    }

    public function setVaultId($vaultId) {
        $this->vaultId = $vaultId;
    }

    public function getVaultId() {
        return $this->vaultId;
    }

    public function __construct($truevault, $vaultId) {
        $this->truevault = $truevault;
        $this->vaultId = $vaultId;
    }

    /**
     * Create new document
     * @param mixed $data
     * @param array $params
     * @return mixed
     */
    public function create($data, $params = array()) {
        $this->lastId = null;

        $params["document"] = $this->truevault->encodeData($data);
        $return = $this->truevault->api("vaults/{$this->vaultId}/documents", "POST", $params);

        if (array_key_exists("document_id", $return))
            $this->lastId = $return["document_id"];

        return $return;
    }

    /**
     * Get document data
     * @param string|array $documentId single document or multiple return
     * @param array $params
     * @throws TrueVaultException
     * @return string
     */
    public function get($documentId, $params = array()) {
        // join multiple
        if (is_array($documentId))
            $documentId = join(",", $documentId);

        $response = $this->truevault->api("vaults/{$this->vaultId}/documents/{$documentId}", "GET", $params);

        // return single document
        if (is_string($response)) {
            return $this->truevault->decodeData($response);
        }

        // return multiple documents
        if (is_array($response) && array_key_exists("documents", $response)) {
            $list = array();

            foreach ($response["documents"] as $document) {
                $list[$document["id"]] = $this->truevault->decodeData($document["document"]);
            }

            return $list;
        }

        throw new TrueVaultException("Unable to obtain document", 0);
    }

    /**
     * Delete given document
     * @param string $documentId
     * @param array $params
     * @return mixed
     */
    public function delete($documentId, $params = array()) {
        $response = $this->truevault->api("vaults/{$this->vaultId}/documents/{$documentId}", "DELETE", $params);
        return $response;
    }

    /**
     * Update document data
     * @param string $documentId
     * @param mixed $data
     * @param array $params
     * @return mixed
     */
    public function update($documentId, $data, $params = array()) {
        $params["document"] = $this->truevault->encodeData($data);
        return $this->truevault->api("vaults/{$this->vaultId}/documents/{$documentId}", "PUT", $params);
    }

    public function search($searchOptions, $params = array()) {
        $search = $this->truevault->encodeData($searchOptions);
        $return = $this->truevault->api("vaults/{$this->vaultId}/?search_option={$search}", "GET", $params);

        if (array_key_exists("data", $return))
            return $return["data"];

        return $return;
    }
}

class TrueVaultSchemas extends TrueVault
{
    /**
     * @var TrueVault API connection class
     */
    private $truevault;

    /**
     * @var string ID of the vault
     */
    private $vaultId;

    /**
     * @var string last created document ID
     */
    private $lastId;

    public function lastInsertId() {
        return $this->lastId;
    }

    public function setVaultId($vaultId) {
        $this->vaultId = $vaultId;
    }

    public function getVaultId() {
        return $this->vaultId;
    }

    public function __construct($truevault, $vaultId) {
        $this->truevault = $truevault;
        $this->vaultId = $vaultId;
    }

    /**
     * Create new schema
     * @param mixed $data
     * @param array $params
     * @return mixed
     */
    public function create($data, $params = array()) {
        $this->lastId = null;

        $params["schema"] = $this->truevault->encodeData($data);
        $return = $this->truevault->api("vaults/{$this->vaultId}/schemas", "POST", $params);

        if (array_key_exists("schema", $return)) {
            $this->lastId = $return["schema"]["id"];
            return $return["schema"];
        }

        return $return;
    }

    /**
     * Get schema data
     * @param string $schemaId
     * @param array $params
     * @throws TrueVaultException
     * @return string
     */
    public function get($schemaId, $params = array()) {
        $response = $this->truevault->api("vaults/{$this->vaultId}/schemas/{$schemaId}", "GET", $params);

        if (is_array($response) && array_key_exists("schema", $response))
            return $response["schema"];

        throw new TrueVaultException("Unable to obtain schema", 0);
    }

    /**
     * Delete given schema
     * @param string $schemaId
     * @param array $params
     * @return mixed
     */
    public function delete($schemaId, $params = array()) {
        $response = $this->truevault->api("vaults/{$this->vaultId}/schemas/{$schemaId}", "DELETE", $params);
        return $response;
    }

    /**
     * Update schema data
     * @param string $schemaId
     * @param mixed $data
     * @param array $params
     * @return mixed
     */
    public function update($schemaId, $data, $params = array()) {
        $params["schema"] = $this->truevault->encodeData($data);
        return $this->truevault->api("vaults/{$this->vaultId}/schemas/{$schemaId}", "PUT", $params);
    }

    /**
     * List all schemas
     * @param array $params
     * @throws TrueVaultException
     * @return string
     */
    public function findAll($params = array()) {
        $response = $this->truevault->api("vaults/{$this->vaultId}/schemas", "GET", $params);

        // get all schemas
        if (is_array($response) && array_key_exists("schemas", $response)) {
            $list = array();

            foreach ($response["schemas"] as $schema) {
                $list[$schema["id"]] = $schema["name"];
            }

            return $list;
        }

        throw new TrueVaultException("Unable to obtain schema", 0);
    }
}
