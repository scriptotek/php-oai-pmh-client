<?php namespace Scriptotek\OaiPmh;

use GuzzleHttp\Psr7\Response as HttpResponse;
use Mockery as m;

class TestCase extends \PHPUnit_Framework_TestCase
{

    protected $recordTpl = '
      <record>
        <header>
           <identifier>{{identifier}}</identifier>
           <datestamp>{{datestamp}}</datestamp>
        </header>
        <metadata>
          {{data}}
        </metadata>
      </record>';

    protected $baseTpl = '<?xml version="1.0" encoding="UTF-8" ?>
        <OAI-PMH  xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
         xmlns:dc="http://purl.org/dc/terms/" 
         xmlns:dcterms="http://purl.org/dc/dc/terms/" 
         xmlns:agls="http://www.aa.gov.au/recordkeeping/gov_online/agls/1.2" 
         xmlns:ags="http://purl.org/agmes/1.1/" 
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ 
                               http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd 
                               http://www.openarchives.org/OAI/2.0/oai_dc/ 
                               http://www.openarchives.org/OAI/2.0/oai_dc.xsd"> 
          <responseDate>2005-06-18T15:42:44Z</responseDate>
          {{main}}
        </OAI-PMH>';

    protected $getRecordTpl = '
        <request verb="GetRecord">oai.bibsys.no/repository</request>
        <GetRecord>
            {{record}}
        </GetRecord>';

    /**
     * Get an item from an array using "dot" notation.
     * Source: http://laravel.com/api/source-function-array_get.html#226-251
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function array_get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) or ! array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    public function randStr($length)
    {
        $randstr = "";
        for ($i=0; $i<$length; $i++) {
            $randnum = mt_rand(0, 61);
            if ($randnum < 10) {
                $randstr .= chr($randnum+48);
            } elseif ($randnum < 36) {
                $randstr .= chr($randnum+55);
            } else {
                $randstr .= chr($randnum+61);
            }
        }
        return $randstr;
    }

    /**
     * Return a single response (no matter what request)
     */
    protected function httpMockSingleResponse($response)
    {
        $http = m::mock();
        $http->shouldReceive('get')
            ->once()
            ->andReturn(new HttpResponse(200, array(), $response));

        return $http;
    }

    /**
     * Returns a series of responses (no matter what request)
     */
    protected function httpMockListResponse($responses)
    {
        $http = m::mock();
        $http->shouldReceive('get')
            ->times(count($responses))
            ->andReturnValues(array_map(function ($r) {
                return new HttpResponse(200, array(), $r);
            }, $responses));

        return $http;
    }
}
