<?php namespace Scriptotek\Oai;

/**
 * ListRecords response, containing a list of records or some error
 */
class ListRecordsResponse extends Response {
 
    /** @var Record[] Array of records */
    public $records;

    /** @var int Total number of records in the result set */
    public $numberOfRecords = null;

    /** @var int Position of the first record in the response relative to the result set (starts at 0) */
    public $cursor = null;

    /** @var int Token for retrieving more records */
    public $resumptionToken = null;

    /**
     * Create a new ListRecords response
     *
     * @param string $text Raw XML response
     * @param Client $client OAI client reference (optional)
     */
    public function __construct($text, &$client = null)
    {
        parent::__construct($text, $client);

        $this->records = array();

        $records = $this->response->first('/oai:OAI-PMH/oai:ListRecords');
        if (!$records) return;

        foreach ($records->xpath('oai:record') as $record) {
            $this->records[] = new Record($record);
        }

        $resumptionToken = $records->first('oai:resumptionToken');
        if (!$resumptionToken) return;

        $this->resumptionToken = $resumptionToken->text();
        
        // These are optional:
        if ($resumptionToken->attr('completeListSize') !== '') {
            $this->numberOfRecords = intval($resumptionToken->attr('completeListSize'));
        }
        if ($resumptionToken->attr('cursor') !== '') {
            $this->cursor = intval($resumptionToken->attr('cursor'));
        }
    }

}

