<?php namespace Scriptotek\OaiPmh;

/**
 * GetRecord response, containing a single record or some error
 */
class GetRecordResponse extends Response
{

    /** @var Record Single record */
    public $record;

    /** @var string */
    public $schema;

    public function __get($name)
    {
        if (isset($this->record) && isset($this->record->{$name})) {
            return $this->record->{$name};
        }
    }

    /**
     * Create a new GetRecord response
     *
     * @param string $text Raw XML response
     */
    public function __construct($text)
    {
        parent::__construct($text);

        $rec = $this->response->first('/oai:OAI-PMH/oai:GetRecord/oai:record');
        $this->record = $rec ? new Record($rec) : null;
    }
}
