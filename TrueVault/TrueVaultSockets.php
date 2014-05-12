<?php
/**
 * TrueVault PHP client library
 * Curl-Free Version
 * More information at https://www.truevault.com/
 *
 * @author Marek Vavrecan <vavrecan@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @version 1.0.3
 */

class TrueVaultSockets extends TrueVault {
    /**
     * Invoke the API.
     *
     * @param string $path The path (required)
     * @param string $method The http method (default 'GET')
     * @param array $params The query/post data
     * @param array $transfer Containing source file to upload (array key upload) or download (array key download)
     *
     * @return mixed The decoded response object
     * @throws TrueVaultException
     * @throws Exception
     */
    public function api($path, $method = "GET", $params = array(), $transfer = array())
    {
        $url = $this->getUrl($path);

        // set upload mode
        if (array_key_exists("upload", $transfer)) {
            $file = $transfer["upload"];
            throw new Exception("Not available in Curl-free version");
        }

        // set download mode
        if (array_key_exists("download", $transfer)) {
            $file = $transfer["download"];
            throw new Exception("Not available in Curl-free version");
        }

        $opts = array(
            "http" => array(
                "timeout" => self::$options["timeout"],
                "ignore_errors" => true,
                "method" => $method,
                "header" => "Accept-language: en\r\n" .
                    "User-Agent: " . self::$options["useragent"] . "\r\n" .
                    "Content-type: application/x-www-form-urlencoded\r\n" .
                    "Authorization: Basic " . base64_encode($this->getApiKey() . ":") . "\r\n",

                "content" => http_build_query($params, null, '&')
            )
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        $headers = $http_response_header;

        $contentType = "";
        $httpCode = 500;

        if (is_array($headers) && count($http_response_header) > 0) {
            if (preg_match('#HTTP/[0-9\.]+ (\d+)#', $http_response_header[0], $matches)) {
                $httpCode = $matches[1];
            }

            foreach ($headers as $header) {
                if (preg_match("#^Content-type: (.+)$#i", $header, $matches))
                    $contentType = $matches[1];
            }
        }

        // decode response if returned as json
        if ($contentType == "application/json")
            $result = json_decode($result, true);

        // handle error of no result
        if ($result === false) {
            $e = new TrueVaultException("Unable to retrieve post data", 0, "Exception");
            throw $e;
        }

        // handle error within response
        if (is_array($result) && array_key_exists("error", $result)) {
            // make sure all required keys are present
            if (!array_key_exists("message", $result["error"]))
                $result["error"]["message"] = "";

            if (!array_key_exists("code", $result["error"]))
                $result["error"]["code"] = "";

            if (!array_key_exists("type", $result["error"]))
                $result["error"]["type"] = "";

            $e = new TrueVaultException($result["error"]["message"], $result["error"]["code"], $result["error"]["type"]);
            throw $e;
        }

        // handle error 500
        if ($httpCode == 500) {
            $e = new TrueVaultException("Remote server returned internal error", 0, "RemoteException");
            throw $e;
        }

        return $result;
    }
}