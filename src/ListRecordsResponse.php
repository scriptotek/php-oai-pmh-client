<?php namespace Scriptotek\Oai;

/**
 * ListRecords response, containing a list of records or some error
 */
class ListRecordsResponse extends Response {

    /** @var string Error code 
     *  Possible error codes:
     *      badArgument
     *      badResumptionToken
     *      badVerb
     *      cannotDisseminateFormat
     *      idDoesNotExist
     *      noRecordsMatch
     *      noMetaDataFormats
     *      noSetHierarchy
     */
    public $errorCode;

    /** @var string Error message */
    public $error;
 
    /** @var Record[] Array of records */
    public $records;

    /** @var int Total number of records in the result set */
    public $numberOfRecords;

    /** @var int Position of next record in the result set, or null if no such record exist */
    public $cursor;

    /** @var int Token for retrieving more records */
    public $resumptionToken;

    /**
     * Create a new ListRecords response
     *
     * @param string $text Raw XML response
     * @param Client $client OAI client reference (optional)
     */
    public function __construct($text, &$client = null)
    {
        parent::__construct($text, $client);

        $err = $this->response->first('/oai:OAI-PMH/oai:error');
        $this->error = $err ? $err->text() : null;
        $this->errorCode = $err ? $err->attr('code') : null;

        $this->records = array();
        $main = $this->response->first('/oai:OAI-PMH/oai:ListRecords');
        if ($main) {
            foreach ($main->xpath('oai:record') as $record) {
                $this->records[] = new Record($record);
            }

            $r = $main->first('oai:resumptionToken') ?: null;
            if ($r) {
                $this->numberOfRecords = intval($r->attr('completeListSize'));
                $this->cursor = intval($r->attr('cursor'));
                $this->resumptionToken = $r->text();
            } else {
                $this->resumptionToken = null;            
            }
        }
    }

}

