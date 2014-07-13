<?php namespace Scriptotek\Oai;

/**
 * When iterating, methods are called in the following order:
 * 
 * rewind()
 * valid()
 * current()
 *
 * next()
 * valid()
 * current()
 *
 * ...
 *
 * next()
 * valid()
 */
class Records implements \Iterator {

	private $from;
	private $until;
	private $set;
	private $client;
	private $extraParams;
	private $lastResponse;

	private $position;
	private $resumptionToken = null;

    /** @var int Total number of records in the result set */
    public $numberOfRecords;

	private $data = array();

    /**
     * Create a new records iterator
     *
     * @param string $from Start date
     * @param string $until End date
     * @param string $set Data set
     * @param Client $client OAI client reference
     * @param string $resumptionToken
     * @param array $extraParams Extra GET parameters (optional)
     */
	public function __construct($from, $until, $set, Client $client, $resumptionToken = null, $extraParams = array()) {
		$this->from = $from;
		$this->until = $until;
		$this->set = $set;
		$this->client = $client;
		$this->resumptionToken = $resumptionToken;
		$this->extraParams = $extraParams;
		$this->position = 1;
		$this->fetchMore();
	}

	/**
     * Return error message from last reponse, if any
     *
     * @return string|null
     */
	function __get($prop) {
		if (in_array($prop, array('error', 'errorCode'))) { 
			return $this->lastResponse->{$prop};
		}
	}

	/**
     * Return the current resumption token
     *
     * @return string|null
     */
	public function getResumptionToken()
	{
		return $this->resumptionToken;
	}

	/**
     * Fetch more records from the service
     */
	private function fetchMore()
	{
		$args = array(
			'from' => $this->from,
			'until' => $this->until,
			'set' => $this->set,
		);
		if (!is_null($this->resumptionToken)) {
			$args['resumptionToken'] = $this->resumptionToken;
		}

		$body = $this->client->request('ListRecords', $args);
		$this->lastResponse = new ListRecordsResponse($body);
		$this->data = $this->lastResponse->records;

		if (isset($this->lastResponse->numberOfRecords) && !is_null($this->lastResponse->numberOfRecords)) {
			$this->numberOfRecords = $this->lastResponse->numberOfRecords;

		} else if (!isset($this->numberOfRecords)) {
			$this->numberOfRecords = count($this->lastResponse->records);
		}

		if (isset($this->lastResponse->resumptionToken)) {
			$this->resumptionToken = $this->lastResponse->resumptionToken;
		}

	}

	/**
     * Return the current element
     *
     * @return mixed
     */
	function current() {
		return $this->data[0];
	}

	/**
     * Return the key of the current element
	 *
     * @return int
     */
	function key() {
		return $this->position;
	}

	/**
     * Rewind the Iterator to the first element
     */
	function rewind() {
		if ($this->position != 1) {
			$this->position = 1;
			$this->resumptionToken = null;
			$this->data = array();
			$this->fetchMore();
		}
	}

	/**
     * Move forward to next element
     */
	function next() {

		if (count($this->data) > 0) {
			array_shift($this->data);
		}
		++$this->position;

		if ($this->position > $this->numberOfRecords) {
			return null;
		}

		if (count($this->data) == 0) {
			$this->fetchMore();
		}

		if (count($this->data) == 0) {
			return null;
		}


	}

	/**
     * Check if current position is valid
	 *
     * @return bool
     */
	function valid() {
		return count($this->data) != 0;
	}

}
