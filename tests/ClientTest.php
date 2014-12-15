<?php namespace Scriptotek\Oai;

use \Guzzle\Http\Message\Response as HttpResponse;
use \Guzzle\Http\Exception\BadResponseException as BadResponseException;
use \Mockery as m;

class ClientTest extends TestCase {

    protected $url = 'http://oai.my_fictive_host.net';

    protected $simple_response = '<?xml version="1.0" encoding="UTF-8" ?>
              <srw:searchRetrieveResponse xmlns:srw="http://www.loc.gov/zing/srw/" xmlns:xcql="http://www.loc.gov/zing/cql/xcql/">
              </srw:searchRetrieveResponse>';

    protected $simple_explain_response = '<?xml version="1.0" encoding="UTF-8"?>
            <sru:explainResponse xmlns:sru="http://www.loc.gov/zing/srw/">
            </sru:explainResponse>';

    protected function tearDown() {
        m::close();
    }

    public function testDefaults()
    {
        $cli1 = new Client($this->url);

        $this->assertEquals('marcxchange', $cli1->schema);
        $this->assertEquals('php-oai-client', $cli1->userAgent);
        $this->assertNull($cli1->credentials);
        $this->assertNull($cli1->proxy);
        $this->assertEquals(12, $cli1->maxRetries);
        $this->assertEquals(60.0, $cli1->timeout);
    }

    public function testUrlBuilder()
    {
        $cli1 = new Client($this->url);

        $this->assertEquals(
            $this->url . '?verb=GetRecord&metadataPrefix=marcxchange&identifier=TEST',
            $cli1->urlBuilder('GetRecord', array('identifier' => 'TEST'))
        );
    }

    public function testResumptionTokenIsSentToHttpClient()
    {
        $resumptionToken = 'dasdsa93123jkldasjkl23';
        $http = m::mock();

        $http->shouldReceive('get')
            ->once()
            ->with("/$resumptionToken/", m::any(), m::any())
            ->andReturn($http);

        $body = str_replace('{{main}}', '<request verb="GetRecord">oai.bibsys.no/repository</request>', $this->baseTpl);

        $http->shouldReceive('send')
            ->once()
            ->andReturn(new HttpResponse(200, null, $body));

        $cli = new Client('nowhere', null, $http);
        $cli->records('2012-01-01', '2012-01-02', 'set', $resumptionToken);

    }

    public function testRequestEventsAreSent()
    {
        $body = str_replace('{{main}}', '<request verb="GetRecord">oai.bibsys.no/repository</request>', $this->baseTpl);
        $http = $this->httpMockSingleResponse($body);
        $mock = m::mock('Scriptotek\Oai\Client[emit]', array('nowhere', null, $http));

        $mock->shouldReceive('emit')
            ->with('request.start', array('verb' => 'GetRecord', 'arguments' => array('identifier' => 'test')))
            ->once();

        $mock->shouldReceive('emit')
            ->with('request.complete', m::any())
            ->once();

        $mock->record('test');
    }

    // Works, but slow due to sleep:
    //
    // public function testRequestErrorEvent()
    // {

    //     $body = str_replace('{{main}}', '<request verb="GetRecord">oai.bibsys.no/repository</request>', $this->baseTpl);

    //     $request = m::mock();
    //     $request->shouldReceive('send')
    //         ->once()
    //         ->andReturn(new HttpResponse(200, null, $body));

    //     $this->n = 0;

    //     $http = m::mock();
    //     $http->shouldReceive('get')
    //         ->once()
    //         ->with(\Mockery::on(function($arg) use ($request) {
    //             $this->n++;
    //             if ($this->n > 2) {
    //                 return true;
    //             }
    //             throw new BadResponseException("OI");
    //         }), m::any(), m::any())
    //         ->andReturn($request);


    //     $mock = m::mock('Scriptotek\Oai\Client[emit]', array('nowhere', null, $http));
    //     $mock->shouldReceive('emit')
    //         ->with('request.start', \Mockery::any())
    //         ->once();

    //     $mock->shouldReceive('emit')
    //         ->with('request.error', \Mockery::any())
    //         ->times(2);

    //     $mock->shouldReceive('emit')
    //         ->with('request.complete', \Mockery::any())
    //         ->once();

    //     $mock->record('test');
    // }

}

