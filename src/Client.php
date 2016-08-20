<?php namespace Scriptotek\OaiPmh;

use GuzzleHttp\Client as HttpClient;
use Evenement\EventEmitter;

/**
 * OAI client
 */
class Client extends EventEmitter
{

    // When we no longer need to support PHP 5.3:
    // - Upgrade to Evenement 2.0 and use trait instead of extending

    /** @var HttpClient */
    protected $httpClient;

    /** @var string OAI service base URL */
    public $url;

    /** @var string Requested schema for the returned records */
    public $schema;

    /** @var string Some user agent string to identify our client */
    public $userAgent;

    /**
     * @var string|string[] Proxy configuration details.
     *
     * Either a string 'host:port' or an 
     * array('host:port', 'username', 'password').
     */
    public $proxy;

    /**
     * @var string[] Array containing username and password
     */
    public $credentials;

    /**
     * @var integer Max number of retries before giving up
     */
    public $maxRetries;

    /**
     * @var integer Sleep time in seconds before retrying when getting an errorneous response
     */
    public $sleepTimeOnError;

    /**
     * @var integer Timeout for each request in seconds
     */
    public $timeout;

    public function array_get($arr, $key, $default = null)
    {
        return (isset($arr[$key])) ? $arr[$key] : $default;
    }

    /**
     * Create a new client
     *
     * @param string $url Base URL to the OAI-PMH service
     * @param array $options Associative array of options
     * @param HttpClient $httpClient
     */
    public function __construct($url, $options = array(), $httpClient = null)
    {
        $this->url = $url;
        $this->httpClient = $httpClient ?: new HttpClient;

        $this->schema = $this->array_get($options, 'schema', 'marcxchange');
        $this->userAgent = $this->array_get($options, 'user-agent', 'php-oaipmh-client');
        $this->credentials = $this->array_get($options, 'credentials');
        $this->proxy = $this->array_get($options, 'proxy');
        $this->maxRetries = $this->array_get($options, 'max-retries', 12);
        $this->sleepTimeOnError = $this->array_get($options, 'sleep-time-on-error', 20);
        $this->timeout = $this->array_get($options, 'timeout', 60.0);
    }

    /**
     * Get HTTP client configuration options (authentication, proxy)
     * 
     * @return array
     */
    public function getHttpOptions()
    {
        $options = array(
            'headers' => $this->getHttpHeaders(),
            'connect_timeout' => $this->timeout,
            'timeout' => $this->timeout,
        );
        if ($this->credentials) {
            $options['auth'] = $this->credentials;
        }
        if ($this->proxy) {
            $options['proxy'] = $this->proxy;
        }
        return $options;
    }

    /**
     * Get HTTP client headers
     * 
     * @return array
     */
    public function getHttpHeaders()
    {
        $headers = array(
            'Accept' => 'application/xml'
        );
        if ($this->userAgent) {
            $headers['User-Agent'] = $this->userAgent;
        }
        return $headers;
    }

    /**
     * Construct the URL for an OAI-PMH query
     *
     * @param string $verb The OAI-PMH verb
     * @param array $arguments OAI-PMH arguments
     * @return string
     */
    public function urlBuilder($verb, $arguments = array())
    {
        $qs = array(
            'verb' => $verb,
            'metadataPrefix' => $this->schema,
        );

        foreach ($arguments as $key => $value) {
            $qs[$key] = $value;
            if (is_null($value)) {
                // Allow removal of default arguments like 'metadataPrefix'
                unset($qs[$key]);
            }
        }

        return $this->url . '?' . http_build_query($qs);
    }

    /**
     * Perform a single OAI-PMH request
     *
     * @param string $verb The OAI-PMH verb
     * @param array $arguments OAI-PMH arguments
     * @return string
     */
    public function request($verb, $arguments)
    {
        $this->emit('request.start', array(
            'verb' => $verb,
            'arguments' => $arguments
        ));
        $url = $this->urlBuilder($verb, $arguments);
        $attempt = 0;
        while (true) {
            try {
                $res = $this->httpClient->get($url, $this->getHttpOptions());
                break;
            } catch (\Guzzle\Http\Exception\RequestException $e) {
                $this->emit('request.error', array(
                    'message' => $e->getMessage(),
                ));
                sleep($this->sleepTimeOnError);
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                $this->emit('request.error', array(
                    'message' => $e->getMessage(),
                ));
                sleep($this->sleepTimeOnError);
            }
            $attempt++;
            if ($attempt > $this->maxRetries) {
                throw new ConnectionError('Failed to get a response from the server. Max retries (' . $this->maxRetries . ') exceeded.');
            }
        }
        $body = (string) $res->getBody();
        $this->emit('request.complete', array(
            'verb' => $verb,
            'arguments' => $arguments,
            'response' => $body
        ));
        return $body;
    }

    /**
     * Perform a GetRecord request
     *
     * @param string $identifier
     * @return Record
     */
    public function record($identifier)
    {
        $data = $this->request('GetRecord', array('identifier' => $identifier));
        return new GetRecordResponse($data);
    }

    /**
     * Perform a ListRecords request and return an iterator over the records
     *
     * @param string $from Start date
     * @param string $until End date
     * @param string $set Data set
     * @param string $resumptionToken To resume a harvest
     * @param array $extraParams Extra GET parameters
     * @return Records
     */
    public function records($from, $until, $set, $resumptionToken = null, $extraParams = array())
    {
        return new Records($from, $until, $set, $this, $resumptionToken, $extraParams);
    }
}
