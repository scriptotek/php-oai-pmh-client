<?php namespace Scriptotek\OaiPmh;

class ResponseTest extends TestCase
{

    /**
     * @expectedException Scriptotek\OaiPmh\BadRequestError
     * @expectedExceptionMessage badArgument : Illegal ientifier.
     */
    public function testError()
    {
        $res = new Response('<?xml version="1.0" encoding="UTF-8" ?>
			<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"> 
				<responseDate>2014-07-05T17:33:54Z</responseDate> 
				<request>oai.bibsys.no/oai/repository</request> 
				<error code="badArgument">Illegal ientifier.</error> 
			</OAI-PMH>');
    }
}
