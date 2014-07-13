<?php namespace Scriptotek\Oai;
 
use \Guzzle\Http\Client as HttpClient;
use \Evenement\EventEmitter;

/**
 * OAI client
 */
class Client extends EventEmitter {

    // When we no longer need to support PHP 5.3:
    // - Upgrade to Evenement 2.0 and use trait instead of extending

    /** @var HttpClient */
    protected $httpClient;

    /** @var string OAI service base URL */
    protected $url;

    /** @var string Requested schema for the returned records */
    protected $schema;

    /** @var string Some user agent string to identify our client */
    protected $userAgent;

    /**
     * @var string|string[] Proxy configuration details.
     *
     * Either a string 'host:port' or an 
     * array('host:port', 'username', 'password').
     */
    protected $proxy;

    /**
     * @var string[] Array containing username and password
     */
    protected $credentials;

    /**
     * Create a new client
     *
     * @param string $url Base URL to the OAI service
     * @param array $options Associative array of options
     * @param HttpClient $httpClient
     */
    public function __construct($url, $options = null, $httpClient = null)
    {
        $this->url = $url;
        $options = $options ?: array();
        $this->httpClient = $httpClient ?: new HttpClient;

        $this->schema = isset($options['schema'])
            ? $options['schema']
            : 'marcxml';

        $this->userAgent = isset($options['user-agent'])
            ? $options['user-agent']
            : null;

        $this->credentials = isset($options['credentials'])
            ? $options['credentials']
            : null;

        $this->proxy = isset($options['proxy'])
            ? $options['proxy']
            : null;
    }

    /**
     * Get HTTP client configuration options (authentication, proxy, headers)
     * 
     * @return array
     */
    public function getHttpOptions()
    {
        $headers = array(
            'Accept' => 'application/xml'
        );
        if ($this->userAgent) {
            $headers['User-Agent'] = $this->userAgent;
        }
        $options = array(
            'headers' => $headers
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
     * Construct the URL for an OAI query
     *
     * @param string $verb The OAI verb
     * @param array $arguments OAI arguments
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
        }

        return $this->url . '?' . http_build_query($qs);
    }

    /**
     * Perform a single OAI request
     *
     * @param string $verb The OAI verb
     * @param array $arguments OAI arguments
     * @return string
     */
    public function request($verb, $arguments)
    {
        $this->emit('request.start', array(
            'verb' => $verb,
            'arguments' => $arguments
        ));
        $url = $this->urlBuilder($verb, $arguments);
        $options = $this->getHttpOptions();
        $res = $this->httpClient->get($url, $options)->send();
        $body = $res->getBody(true);
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
        return new Records($from, $until, $set, $this, $resumptionToken, $extraParams, $this->httpClient);
    }

}

