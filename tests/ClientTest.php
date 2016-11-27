<?php namespace Scriptotek\OaiPmh;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as HttpResponse;
use Mockery as m;

class ClientTest extends TestCase
{

    protected $url = 'http://oai.my_fictive_host.net';

    protected $simple_response = '<?xml version="1.0" encoding="UTF-8" ?>
              <srw:searchRetrieveResponse xmlns:srw="http://www.loc.gov/zing/srw/" xmlns:xcql="http://www.loc.gov/zing/cql/xcql/">
              </srw:searchRetrieveResponse>';

    protected $simple_explain_response = '<?xml version="1.0" encoding="UTF-8"?>
            <sru:explainResponse xmlns:sru="http://www.loc.gov/zing/srw/">
            </sru:explainResponse>';

    protected function tearDown()
    {
        m::close();
    }

    public function testDefaults()
    {
        $cli1 = new Client($this->url);

        $this->assertEquals('marcxchange', $cli1->schema);
        $this->assertEquals('php-oaipmh-client', $cli1->userAgent);
        $this->assertNull($cli1->credentials);
        $this->assertNull($cli1->proxy);
        $this->assertEquals(30, $cli1->maxRetries);
        $this->assertEquals(60.0, $cli1->timeout);
    }

    public function testHeaders()
    {
        $cli = new Client($this->url, array('user-agent' => 'MyAwesomeHarvester/0.1'));
        $headers = $cli->getHttpHeaders();

        $this->assertEquals('application/xml', $headers['Accept']);
        $this->assertEquals('MyAwesomeHarvester/0.1', $headers['User-Agent']);
    }

    public function testHttpOptions()
    {
        $cli = new Client($this->url, array(
            'timeout' => 13,
            'credentials' => array('Galileo', 'Galilei'),
            'proxy' => array('myproxy.nu', 9870),
            ));
        $opts = $cli->getHttpOptions();

        $this->assertEquals(13, $opts['connect_timeout']);
        $this->assertEquals(13, $opts['timeout']);
        $this->assertEquals(array('Galileo', 'Galilei'), $opts['auth']);
        $this->assertEquals(array('myproxy.nu', 9870), $opts['proxy']);
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
        $body = str_replace('{{main}}', '<request verb="GetRecord">oai.bibsys.no/repository</request>', $this->baseTpl);

        $handlers = new MockHandler([
            new HttpResponse(200, [], $body),
        ]);
        $stack = HandlerStack::create($handlers);
        $transactions = [];
        $history = Middleware::history($transactions);
        $stack->push($history);
        $http = new HttpClient(['handler' => $stack]);

        $resumptionToken = 'dasdsa93123jkldasjkl23';

        $cli = new Client('http://test', null, $http);
        $cli->records('2012-01-01', '2012-01-02', 'set', $resumptionToken);

        $this->assertEquals(1, count($transactions));
        $this->assertContains("resumptionToken=$resumptionToken", $transactions[0]['request']->getUri()->getQuery());
    }

    public function testRequestEventsAreSent()
    {
        $body = str_replace('{{main}}', '<request verb="GetRecord">oai.bibsys.no/repository</request>', $this->baseTpl);
        $http = $this->httpMockSingleResponse($body);
        $mock = m::mock('Scriptotek\OaiPmh\Client[emit]', array('nowhere', null, $http));

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
    //         ->andReturn(new HttpResponse(200, array(), $body));

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
