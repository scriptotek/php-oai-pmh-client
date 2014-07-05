<?php namespace Scriptotek\Oai;

use \Guzzle\Http\Message\Response as HttpResponse;
use \Mockery as m;
use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;

class RecordTest extends TestCase {

	public function testBasicCase()
	{
		$r = new QuiteSimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?>
			<record xmlns="http://www.openarchives.org/OAI/2.0/">
				<header>
					<identifier>oai:bibsys.no:biblio:113889372</identifier>
					<datestamp>2013-02-04T13:54:53Z</datestamp>
				</header>
				<metadata>
					The record
				</metadata>
			</record>');
		$r->registerXPathNamespaces(array(
            'oai' => 'http://www.openarchives.org/OAI/2.0/',
        ));
		$res = new Record($r);

		$this->assertEquals('oai:bibsys.no:biblio:113889372', $res->identifier);
		$this->assertEquals('2013-02-04T13:54:53Z', $res->datestamp);
		$this->assertEquals('The record', $res->data->text());
	}

}