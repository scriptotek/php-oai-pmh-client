<?php namespace Scriptotek\Oai;

use \Guzzle\Http\Message\Response as HttpResponse;
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

    public function testUrlBuilder()
    {
        $cli1 = new Client($this->url);

        $this->assertEquals(
            $this->url . '?verb=GetRecord&metadataPrefix=marcxml&identifier=TEST',
            $cli1->urlBuilder('GetRecord', array('identifier' => 'TEST'))
        );
    }

    public function testResumptionTokenIsSentToHttpClient()
    {
        $resumptionToken = 'dasdsa93123jkldasjkl23';
        $http = m::mock();

        $http->shouldReceive('get')
            ->once()
            ->with("/$resumptionToken/", m::any())
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

}

