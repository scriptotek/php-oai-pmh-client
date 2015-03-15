<?php namespace Scriptotek\OaiPmh;

class RecordsTest extends TestCase {

    protected $listRecordsTpl = '
        <request verb="ListRecords">oai.bibsys.no/repository</request>
        <ListRecords>
            {{records}}
            {{resumptionToken}}
        </ListRecords>';

    protected $errorResponse = '<?xml version="1.0" encoding="UTF-8" ?>
		 <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"> 
		   <responseDate>2014-07-08T14:47:07Z</responseDate> 
		  <request>oai.bibsys.no/oai/repository</request> 
		  <error code="badArgument">unknown set name: norgessoks</error> 
		</OAI-PMH>
		';

	/**
     *  numberOfRecords : Total number of records in response
     */
    public function makeDummyResponse($numberOfRecords = 10, $startIndex = 1, $includeResumptionToken = false, $options = array())
    {
        $from = $this->array_get( $options, 'from', '2010-01-01' );
        $until = $this->array_get( $options, 'until', '2010-01-02' );

        $recordTpl = $this->recordTpl;

        $that = $this; // for PHP 5.3 compability we need to pass $this to the anonymous function :(
        $records = implode('', array_map(function($n) use ($recordTpl, $options, $that) {
            $identifier = $that->array_get( $options, 'identifier', $that->randStr(16) );
            return str_replace(
                array('{{identifier}}', '{{datestamp}}', '{{data}}'),
                array($identifier, '2012-01-01', 'RecordData #' . $n),
                $recordTpl
            );
        }, range($startIndex, $startIndex + $numberOfRecords - 1)));

        $resumptionToken = $includeResumptionToken ? 'TODO' : '';

        $ls = str_replace(
            array('{{records}}', '{{resumptionToken}}'),
            array($records, $resumptionToken),
            $this->listRecordsTpl
        );
        return str_replace('{{main}}', $ls, $this->baseTpl);
    }

	public function testBasicIteration()
	{
		$uri = 'http://localhost';
		$args = array(
			'from' => '2012-01-01',
			'until' => '2012-01-02',
			'set' => 'Dummy',
		);
		$n = 8;

		$responses = array(
			$response = $this->makeDummyResponse($n, 1, false, $args),
		);
        $http = $this->httpMockListResponse($responses);

		
        // $http = $this->httpMockSingleResponse($response);
		$client = new Client($uri, null, $http);
		$records = new Records($args['from'], $args['until'], $args['set'], $client);

		$this->assertEquals(8, $records->numberOfRecords);
		$records->rewind();

		$this->assertEquals(1, $records->key());
		$this->assertTrue($records->valid());
		$records->next();
		$records->next();
		$this->assertEquals(3, $records->key());
		$this->assertTrue($records->valid());
	}

	public function testRewindCausesANewRequest()
	{
		$uri = 'http://localhost';
		$args = array(
			'from' => '2012-01-01',
			'until' => '2012-01-02',
			'set' => 'Dummy',
		);
		$n = 8;

		$responses = array(
			$response = $this->makeDummyResponse($n, 1, false, $args),
			$response = $this->makeDummyResponse($n, 1, false, $args),
			$response = $this->makeDummyResponse($n, 1, false, $args),
		);
        $http = $this->httpMockListResponse($responses);

		
        // $http = $this->httpMockSingleResponse($response);
		$client = new Client($uri, null, $http);
		$records = new Records($args['from'], $args['until'], $args['set'], $client);

		$records->next();
		$records->rewind();
		$records->next();

		$i = 0;
		foreach ($records as $rec) {
			$i++;
		}
		$this->assertEquals($n, $i);
	}

	/**
     * @expectedException Scriptotek\OaiPmh\BadRequestError
     * @expectedExceptionMessage badArgument : unknown set name: norgessoks
     */
	public function testBadRequest()
	{
		$uri = 'http://localhost';
		$args = array(
			'from' => '2012-01-01',
			'until' => '2012-01-02',
			'set' => 'Dummy',
		);
        $http = $this->httpMockSingleResponse($this->errorResponse);

		$client = new Client($uri, null, $http);
		$records = new Records($args['from'], $args['until'], $args['set'], $client);
	}

	/*public function testMultipleRequests()
	{
		$nrecs = 5;

		$responses = array(
			$this->makeDummyResponse($nrecs, array('startRecord' => 1, 'maxRecords' => 2)),
			$this->makeDummyResponse($nrecs, array('startRecord' => 3, 'maxRecords' => 2)),
			$this->makeDummyResponse($nrecs, array('startRecord' => 5, 'maxRecords' => 2))
		);

        $http = $this->httpMockListResponse($responses);
        $uri = 'http://localhost';
        $cql = 'dummy';

		$client = new Client($uri);
		$records = new Records($cql, $client, 10, array(), $http);

		$records->rewind();
		foreach (range(1, $nrecs) as $n) {
			$this->assertEquals($n, $records->key());
			$this->assertTrue($records->valid());
			$this->assertEquals($n, $records->current()->position);
			$records->next();
		}
		$this->assertFalse($records->valid());
	}*/

}