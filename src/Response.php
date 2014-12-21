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

        /* Possible error codes:
            badArgument
            badResumptionToken
            badVerb
            cannotDisseminateFormat
            idDoesNotExist
            noRecordsMatch
            noMetaDataFormats
            noSetHierarchy
           http://www.openarchives.org/OAI/openarchivesprotocol.html#ErrorConditions
        */
        $err = $this->response->first('/oai:OAI-PMH/oai:error');
        if ($err) {
            throw new BadRequestError($err->attr('code') . ' : ' . $err->text());
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

