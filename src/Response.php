<?php namespace Scriptotek\Oai;
 
use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;

/**
 * Generic OAI response
 */
class Response {

    /** @var string Raw XML response */
    protected $rawResponse;

    /** @var QuiteSimpleXMLElement XML response */
    protected $response;

    /** @var Client Reference to OAI client object */
    protected $client;

    /** @var string Error message */
    public $error;

    /**
     * Create a new response
     *
     * @param string $text Raw XML response
     * @param Client $client OAI client reference (optional)
     */
    public function __construct($text, &$client = null)
    {
        $this->rawResponse = $text;

        // Throws Danmichaelo\QuiteSimpleXMLElement\InvalidXMLException on invalid xml
        $this->response = new QuiteSimpleXMLElement($text);

        $this->client = $client;

        $this->response->registerXPathNamespaces(array(
            'oai' => 'http://www.openarchives.org/OAI/2.0/',
        ));

        // See: http://www.openarchives.org/OAI/openarchivesprotocol.html#ErrorConditions
        $e = $this->response->first('/oai:error');
        if ($e) {
            $this->error = $e->text();
        }
    }

    /**
     * Get the raw xml response
     *
     * @return string
     */
    public function asXml()
    {
        return $this->rawResponse;
    }

}

