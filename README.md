[![Build Status](https://img.shields.io/travis/scriptotek/php-oai-client.svg)](https://travis-ci.org/scriptotek/php-oai-client)
[![Coverage Status](https://img.shields.io/coveralls/scriptotek/php-oai-client.svg)](https://coveralls.io/r/scriptotek/php-oai-client?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/scriptotek/php-oai-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/scriptotek/php-oai-client/?branch=master)

## php-oai-client

Simple PHP client package for fetching data from an OAI-PMH server, using the 
[Guzzle HTTP client](http://guzzlephp.org/). The returned data is parsed by
[QuiteSimpleXMLElement](//github.com/danmichaelo/quitesimplexmlelement).
On network problems, the client will retry a configurable number of times,
with some sleep inbetween, before throwing a `ResponseException`.

### Install using Composer

Add the package to the `require` list of your `composer.json` file.

```json
{
    "require": {
        "scriptotek/oai-client": "dev-master"
    },
}
``` 

and run `composer install` to get the latest version of the package.

### Example

```php
require_once('vendor/autoload.php');
use Scriptotek\Oai\Client as OaiClient;

$url = 'http://oai.bibsys.no/repository';

$client = new OaiClient($url, array(
    'schema' => 'marcxchange',
    'user-agent' => 'MyTool/0.1',
    'max-retries' => 10,
    'sleep-time-on-error' => 30,
));
```

#### Fetching a single record

```php
try {
    $record = $client->record('oai:bibsys.no:biblio:113889372');
} catch (Scriptotek\Oai\ResponseException $e) {
    echo 'The OAI-PMH server returned an empty or invalid response.'
    die;
    
}
if ($record->error) {
    echo $record->errorCode . ' : ' . $record->error . "\n";
    die;
}

echo $record->identifier . "\n";
echo $record->datestamp . "\n";
echo $record->data->asXML() . "\n";
```

#### Iterating over a record set

```php
foreach ($client->records('') as $record) {
	echo $record->identifier . "\n";
	echo $record->datestamp . "\n";
}
```

### Events

```php
$client->on('request.start', function($verb) {
    print "Starting " . $verb . " request\n";
});
$client->on('request.error', function($err) {
    print "Non-fatal error: " . $err . "\n";
});
$client->on('request.complete', function($verb) {
    print "Completed " . $verb . " request\n";
});
```

### API documentation 

API documentation can be generated using e.g. [Sami](https://github.com/fabpot/sami),
which is included in the dev requirements of `composer.json`.

    php vendor/bin/sami.php update sami.config.php -v

You can view it at [scriptotek.github.io/php-oai-client](//scriptotek.github.io/php-oai-client/)
