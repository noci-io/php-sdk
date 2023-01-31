<?php

namespace Digitalize\SDK;

use Digitalize\SDK\Exceptions\ApplicationException;
use Digitalize\SDK\Exceptions\ConnectionUriException;
use Digitalize\SDK\Exceptions\QueryException;
use Digitalize\SDK\Namespaces\Orders;
use Digitalize\SDK\Namespaces\Providers;
use Exception;

/**
 * SDK Client.
 * 
 * Handles communication with API.
 */
class Client
{
    /**
     * URL of the Digital'ize API.
     *
     * @var string
     */
    private $apiUrl = null;

    /**
     * API key, used for API authorization.
     * 
     * @var string
     */
    private $apiKey = null;

    /**
     * API secret, used for API authorization.
     * 
     * @var string
     */
    private $apiSecret = null;

    /**
     * API scheme, should be 'http' or 'https'.
     * 
     * @var string
     */
    private $apiScheme = 'http';

    /**
     * API port number.
     * 
     * @var integer
     */
    private $apiPort = null;

    /**
     * API path.
     * 
     * @var string
     */
    private $apiPath = '/';

    /**
     * Contains the client parameters.
     *
     * @var array
     */
    public $params = [];

    /**
     * Tells if cURL must verify SSL validity
     *
     * @var boolean
     */
    private $verifySsl = true;

    /**
     * List of namespaces provided by the SDK
     *
     * @var array
     */
    private $namespaces = [
        'Orders' => Orders::class,
        'Providers' => Providers::class
    ];

    /**
     * Orders namespace
     *
     * @var Orders
     */
    public $Orders;

    /**
     * Providers namespace
     *
     * @var Providers
     */
    public $Providers;

    /**
     * Creates a SDK Client.
     * 
     * Parses the connection URI and save the login credentials.
     *
     * @param string $uri           Connection URI, should looks like "scheme://apikey:apisecret@host"
     * @param boolean $verifySsl    Tells if cURL must verify SSL validity
     * @throws Exceptions\ConnectionUriException
     */
    public function __construct($uri, $verifySsl = true)
    {
        $this->verifySsl = $verifySsl;
        $parsed = parse_url($uri);
        $map = [
            'scheme' => 'apiScheme',
            'host' => 'apiUrl',
            'user' => 'apiKey',
            'pass' => 'apiSecret',
            'port' => 'apiPort',
            'path' => 'apiPath',
            'params' => 'params'
        ];
        $missing = [];

        foreach ($map as $from => $to) {
            if (isset($parsed[$from])) {
                $this->$to = $parsed[$from];
            }
        }

        if (isset($parsed['query']))
            parse_str($parsed['query'], $this->params);

        if ($this->apiUrl === null)
            $missing[] = 'host';

        if ($this->apiKey === null)
            $missing[] = 'api_key';

        if ($this->apiSecret === null)
            $missing[] = 'api_secret';

        if (!isset($this->params['customerId']))
            $missing[] = 'customer_id';

        if (sizeof($missing)) {
            throw new ConnectionUriException("Some mandatory fields in the connection URI are missing: " . implode(', ', $missing) . ".");
        }

        if (!preg_match('/^[a-f0-9]{40}$/', $this->apiKey)) {
            throw new ConnectionUriException("The API key is not well formated.");
        }

        if (!preg_match('/^[a-f0-9]{40}$/', $this->apiSecret)) {
            throw new ConnectionUriException("The API secret is not well formated.");
        }

        if (!in_array($this->apiScheme, ['http', 'https'])) {
            throw new ConnectionUriException("The API scheme should be 'http' or 'https'.");
        }

        $this->setupNamespaces();
    }

    /**
     * Instanciate every namespace provided by this SDK
     *
     * @return void
     */
    private function setupNamespaces()
    {
        foreach ($this->namespaces as $alias => $cls) {
            $this->$alias = new $cls($this);
        }
    }

    /**
     * Calls the API server with specified parameters and returns the JSON-parsed result.
     *
     * @param string $method        HTTP method, should be 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
     * @param string $endpoint      API endpoint, should start with leading slash ('/').
     * @param array $params         Optional parameters
     * @return array
     * @throws Exceptions\QueryException
     * @throws Exceptions\ApplicationException
     */
    public function call($method, $endpoint, $params = [])
    {
        try {
            $method = strtoupper($method);
            $query = null;
            $body = '';
            switch ($method) {
                case 'GET':
                case 'DELETE':
                    $query = http_build_query($params);
                    break;
                case 'POST':
                case 'PUT':
                case 'PATCH':
                    $body = json_encode($params);
                    break;
                default:
                    throw new ApplicationException("Unsupported method: $method.");
                    break;
            }
            if (substr($endpoint, 0, 1) !== '/')
                $endpoint = '/' . $endpoint;
            if (substr($this->apiPath, -1) === '/')
                $this->apiPath = substr($this->apiPath, 0, -1);
            $port = $this->apiPort === null ? '' : ':' . $this->apiPort;
            $url = "{$this->apiScheme}://{$this->apiUrl}{$port}{$this->apiPath}{$endpoint}";
            if ($query)
                $url .= '?' . $query;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (!$this->verifySsl) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json',
                    'Authorization: Basic ' . base64_encode(implode(':', [
                        $this->apiKey,
                        $this->apiSecret
                    ])),
                    'Content-Length: ' . strlen($body)
                )
            );
            $result = curl_exec($ch);
            $result = json_decode($result, true);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($http_code >= 400) {
                throw new QueryException("The Digital'Ize API returned an error: [$http_code] {$result['error']}");
            }
            return $result;
        } catch (QueryException $e) {
            throw $e;
        } catch (ApplicationException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ApplicationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Executes a GET request and returns the result.
     *
     * @param string $endpoint
     * @param array $query
     * @return array
     */
    public function get($endpoint, $query = [])
    {
        return $this->call('GET', $endpoint, $query);
    }

    /**
     * Executes a POST request and returns the result.
     *
     * @param string $endpoint
     * @param array $query
     * @return array
     */
    public function post($endpoint, $params = [])
    {
        return $this->call('POST', $endpoint, $params);
    }

    /**
     * Executes a PUT request and returns the result.
     *
     * @param string $endpoint
     * @param array $query
     * @return array
     */
    public function put($endpoint, $params = [])
    {
        return $this->call('PUT', $endpoint, $params);
    }

    /**
     * Executes a PATCH request and returns the result.
     *
     * @param string $endpoint
     * @param array $query
     * @return array
     */
    public function patch($endpoint, $params = [])
    {
        return $this->call('PATCH', $endpoint, $params);
    }

    /**
     * Executes a DELETE request and returns the result.
     *
     * @param string $endpoint
     * @param array $query
     * @return array
     */
    public function delete($endpoint, $query = [])
    {
        return $this->call('DELETE', $endpoint, $query);
    }
}
