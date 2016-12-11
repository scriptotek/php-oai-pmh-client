[![Build Status](http://img.shields.io/travis/scriptotek/php-oai-pmh-client/master.svg?style=flat-square)](https://travis-ci.org/scriptotek/php-oai-pmh-client)
[![Coverage](https://img.shields.io/codecov/c/github/scriptotek/php-oai-pmh-client/master.svg?style=flat-square)](https://codecov.io/gh/scriptotek/php-oai-pmh-client)
[![Code Quality](http://img.shields.io/scrutinizer/g/scriptotek/php-oai-pmh-client/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/scriptotek/php-oai-pmh-client/?branch=master)
[![Latest Stable Version](http://img.shields.io/packagist/v/scriptotek/oai-pmh-client.svg?style=flat-square)](https://packagist.org/packages/scriptotek/oai-pmh-client)
[![Total Downloads](http://img.shields.io/packagist/dt/scriptotek/oai-pmh-client.svg?style=flat-square)](https://packagist.org/packages/scriptotek/oai-pmh-client)

**Note**: This package is abandoned. I recommend using the [caseyamcl/phpoaipmh](https://github.com/caseyamcl/phpoaipmh) package instead. It has an almost identical interface, great code quality and more contributors, so I see no reason to continue maintaining this package.

## php-oai-pmh-client

Simple PHP client package for fetching data from an OAI-PMH server, using the 
[Guzzle HTTP client](http://guzzlephp.org/). The returned data is parsed by
[QuiteSimpleXMLElement](//github.com/danmichaelo/quitesimplexmlelement).

On network problems, the client will retry a configurable number of times,
emitting a `request.error` event each time, before finally throwing
a `ConnectionError`.

### Install using Composer

```
composer require scriptotek/oai-pmh-client
```

### Example

```php
require_once('vendor/autoload.php');
use Scriptotek\OaiPmh\Client as OaiPmhClient;

$url = 'http://oai.bibsys.no/repository';

$client = new OaiPmhClient($url, array(
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
} catch (Scriptotek\OaiPmh\ConnectionError $e) {
    echo $e->getMsg();
    die;
} catch (Scriptotek\OaiPmh\BadRequestError $e) {
    echo 'Bad request: ' . $e->getMsg() . "\n";
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

You can view it at [scriptotek.github.io/php-oai-pmh-client](//scriptotek.github.io/php-oai-pmh-client/)
