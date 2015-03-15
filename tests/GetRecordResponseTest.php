<?php namespace Scriptotek\OaiPmh;

class GetRecordResponseTest extends TestCase {

	public function testNormalResponse()
	{
		$res = new GetRecordResponse('<?xml version="1.0" encoding="UTF-8" ?>
			<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/">
				<responseDate>2014-07-05T17:28:10Z</responseDate>
				<request verb="GetRecord"  metadataPrefix="marcxchange">http://oai.bibsys.no/oai/repository</request>
				<GetRecord>
					<record>
						<header>
							<identifier>oai:bibsys.no:biblio:113889372</identifier>
							<datestamp>2013-02-04T13:54:53Z</datestamp>
						</header>
						<metadata>
							The record
						</metadata>
					</record>
				</GetRecord>
			</OAI-PMH>');

		$this->assertInstanceOf('Scriptotek\OaiPmh\Record', $res->record);
	}

}