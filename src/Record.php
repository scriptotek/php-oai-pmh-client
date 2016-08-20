<?php namespace Scriptotek\OaiPmh;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;

/**
 * Single record from a OAI-PMH response
 */
class Record
{

    /** @var string */
    public $identifier;

    /** @var string Date/timestamp of the record */
    public $datestamp;

    /** @var QuiteSimpleXMLElement */
    public $data;

    /**
     * Create a new record
     *
     * @param Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement $doc
     */
    public function __construct($doc)
    {
        $this->identifier = $doc->text('oai:header/oai:identifier');
        $this->datestamp = $doc->text('oai:header/oai:datestamp');
        $this->data = $doc->first('oai:metadata');
    }
}
