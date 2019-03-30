# arangodb-php-client

[![Build Status](https://travis-ci.org/sandrokeil/arangodb-php-client.svg?branch=master)](https://travis-ci.org/sandrokeil/arangodb-php-client)
[![Coverage Status](https://coveralls.io/repos/github/sandrokeil/arangodb-php-client/badge.svg?branch=master)](https://coveralls.io/github/sandrokeil/arangodb-php-client?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sandrokeil/arangodb-php-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sandrokeil/arangodb-php-client/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/sandrokeil/arangodb-php-client/v/stable.png)](https://packagist.org/packages/sandrokeil/arangodb-php-client)
[![Total Downloads](https://poser.pugx.org/sandrokeil/arangodb-php-client/downloads.png)](https://packagist.org/packages/sandrokeil/arangodb-php-client)
[![License](https://poser.pugx.org/sandrokeil/arangodb-php-client/license.png)](https://packagist.org/packages/sandrokeil/arangodb-php-client)


[ArangoDB](https://arangodb.com/ "native multi-model database") PHP PSR 7/17/18 client implementation.

## Requirements

- PHP >= 7.1
- ArangoDB server version >= 3.3

## Setup

TBD

## Tests
If you want to run the unit tests locally you need [Docker](https://docs.docker.com/engine/installation/ "Install Docker")
and [Docker Compose](https://docs.docker.com/compose/install/ "Install Docker Compose").

Install dependencies with:

```
$ docker run --rm -i -v $(pwd):/app prooph/composer:7.2 update -o
```

Start containers with
```
$ docker-compose up -d --no-recreate
```

Execute tests with

```
$ docker-compose run --rm php vendor/bin/phpunit
```

Execute Velocypack tests with

```
$ docker-compose run --rm vpack72 vendor/bin/phpunit
```

