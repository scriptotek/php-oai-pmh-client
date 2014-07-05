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

    public function testUrlBuilder()
    {
        $cli1 = new Client($this->url);

        $this->assertEquals(
            $this->url . '?verb=GetRecord&metadataPrefix=marcxml&identifier=TEST',
            $cli1->urlBuilder('GetRecord', array('identifier' => 'TEST'))
        );
    }

}

