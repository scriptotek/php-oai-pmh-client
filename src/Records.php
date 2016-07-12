<?php namespace Scriptotek\OaiPmh;

use Evenement\EventEmitter;
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
class Records extends EventEmitter implements \Iterator {

	// When we no longer need to support PHP 5.3:
	// - Upgrade to Evenement 2.0 and use trait instead of extending

	private $from;
	private $until;
	private $set;
	private $client;
	private $extraParams;
	private $lastResponse;

	private $position;
	private $initialPosition;
	private $resumptionToken;
	private $initialResumptionToken;

	/** @var int Total number of records in the result set. Only defined if the
	    server returns 'completeListSize', which is optional. Even if defined, the
	    value may still be only an estimate. */
	public $numberOfRecords = null;

	private $data = array();

    /**
     * Create a new records iterator
     *
     * @param string $from Start date
     * @param string $until End date
     * @param string $set Data set
     * @param Client $client OAI-PMH client reference
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

		// Store initial position, might not be 1 if we are resuming an operation
		$this->initialPosition = $this->position;
		$this->initialResumptionToken = $resumptionToken;
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
     * Return the last response object
     *
     * @return ListRecordsResponse|null
     */
	public function getLastResponse()
	{
		return $this->lastResponse;
	}

	/**
     * Fetch more records from the service
     */
	private function fetchMore()
	{
		if (is_null($this->resumptionToken)) {
			$args = array(
				'from' => $this->from,
				'until' => $this->until,
				'set' => $this->set,
			);
		} else {
			$args = array(
				'resumptionToken' => $this->resumptionToken,
				'metadataPrefix' => null,
			);
		}

		$attempt = 0;
		while (true) {
			$body = $this->client->request('ListRecords', $args);
			try {
				$this->lastResponse = new ListRecordsResponse($body);
				break;
			} catch (\Danmichaelo\QuiteSimpleXMLElement\InvalidXMLException $e) {
                $this->emit('request.error', array(
                    'message' => $e->getMessage(),
                ));
                $attempt++;
	            if ($attempt > $this->client->maxRetries) {
	            	// Give up
	            	throw new ConnectionError('Failed to get a valid XML response from the server.', null, $e);
	            }
                sleep($this->client->sleepTimeOnError);
            }
		}

		$this->data = $this->lastResponse->records;

		if (!is_null($this->lastResponse->numberOfRecords)) {
			$this->numberOfRecords = $this->lastResponse->numberOfRecords;
			if (!is_null($this->lastResponse->cursor)) {
				$this->position = $this->lastResponse->cursor + 1;
			}
		}

		if (isset($this->lastResponse->resumptionToken)) {
			$this->resumptionToken = $this->lastResponse->resumptionToken;
		} else {
			$this->resumptionToken = null;
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
		if ($this->position != $this->initialPosition) {
			$this->position = $this->initialPosition;
			$this->resumptionToken = $this->initialResumptionToken;
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

		if (count($this->data) == 0 && !is_null($this->resumptionToken)) {
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
