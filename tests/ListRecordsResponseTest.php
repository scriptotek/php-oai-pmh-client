<?php namespace Scriptotek\OaiPmh;

class ListRecordsResponseTest extends TestCase
{

    public function testNormalResponse()
    {
        $res = new ListRecordsResponse('<?xml version="1.0" encoding="UTF-8" ?>
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
			  <responseDate>2005-06-18T16:01:47Z</responseDate>
			<request verb="ListRecords"  metadataPrefix="oai_dc">oai.bibsys.no/repository</request>
			<ListRecords>
			<record>
			<header>
			 <identifier>oai:bibsys.no:biblio:031852262</identifier>
			 <datestamp>2003-12-09</datestamp>
			<setSpec>sesam</setSpec>

			</header><metadata>
				Blablabla
			</metadata>
			</record>

			<resumptionToken completeListSize="459345"  cursor="1020">lr~sesam~~~oai_dc~1020~459345~9351</resumptionToken>
			</ListRecords>
			</OAI-PMH>');

        $this->assertCount(1, $res->records);
        $this->assertEquals('lr~sesam~~~oai_dc~1020~459345~9351', $res->resumptionToken);
        $this->assertEquals(1020, $res->cursor);
        $this->assertEquals(459345, $res->numberOfRecords);
    }
}
