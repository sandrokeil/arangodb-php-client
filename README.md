# arangodb-php-client

[![Build Status](https://travis-ci.org/sandrokeil/arangodb-php-client.svg?branch=master)](https://travis-ci.org/sandrokeil/arangodb-php-client)
[![Coverage Status](https://coveralls.io/repos/github/sandrokeil/arangodb-php-client/badge.svg?branch=master)](https://coveralls.io/github/sandrokeil/arangodb-php-client?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sandrokeil/arangodb-php-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sandrokeil/arangodb-php-client/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/sandrokeil/arangodb-php-client/v/stable.png)](https://packagist.org/packages/sandrokeil/arangodb-php-client)
[![Total Downloads](https://poser.pugx.org/sandrokeil/arangodb-php-client/downloads.png)](https://packagist.org/packages/sandrokeil/arangodb-php-client)
[![License](https://poser.pugx.org/sandrokeil/arangodb-php-client/license.png)](https://packagist.org/packages/sandrokeil/arangodb-php-client)

[ArangoDB](https://arangodb.com/ "native multi-model database") HTTP client implementation with PHP 
PSR [7](https://www.php-fig.org/psr/psr-7/) / [17](https://www.php-fig.org/psr/psr-17/) / [18](https://www.php-fig.org/psr/psr-18/) support.

 * **Well tested.** Besides unit test and continuous integration/inspection this solution is also ready for production use.
 * **Framework agnostic** This PHP library does not depends on any framework but you can use it with your favourite framework.
 * **Every change is tracked**. Want to know whats new? Take a look at [CHANGELOG.md](https://github.com/sandrokeil/interop-config/blob/master/CHANGELOG.md)
 * **Listen to your ideas.** Have a great idea? Bring your tested pull request or open a new issue.

## Requirements

- PHP >= 7.2
- ArangoDB server version >= 3.4

## Examples

Examples of how to create collections or documents and more are provided in the [`example`](example) directory.

## Tests
If you want to run the unit tests locally you need [Docker](https://docs.docker.com/engine/installation/ "Install Docker")
and [Docker Compose](https://docs.docker.com/compose/install/ "Install Docker Compose").

Install dependencies with:

```
$ docker run --rm -i -v $(pwd):/app prooph/composer:7.4 update -o
```

Copy `docker-compose.yml.dist` to `docker-compose.yml` and modify to your needs.

Start containers with
```
$ docker-compose up -d --no-recreate
```

Execute tests with

```
$ docker-compose run --rm php vendor/bin/phpunit
```
