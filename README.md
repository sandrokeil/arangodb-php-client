# arangodb-php-client

[![Build Status](https://travis-ci.org/sandrokeil/arangodb-php-client.svg?branch=master)](https://travis-ci.org/sandrokeil/arangodb-php-client)
[![Coverage Status](https://coveralls.io/repos/sandrokeil/arangodb-php-client/badge.svg?branch=master&service=github)](https://coveralls.io/github/sandrokeil/arangodb-php-client?branch=master)

[ArangoDB](https://arangodb.com/ "native multi-model database") PHP PSR 7/18 client implementation with
[Velcoypack](https://github.com/arangodb/velocypack "a fast and compact format for serialization and storage") support
via [martin-schilling/php-velocypack](https://github.com/martin-schilling/php-velocypack/).

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
$ docker-compose run --rm vpack vendor/bin/phpunit
```

## Zephir
To convert a PHP file to Zephir run:

```
docker-compose run --rm php vendor/bin/php2zephir convert src/TransactionalClient.php zephir/arangodb/TransactionalClient.zep
```

To compile and build the PHP extension run:

```
docker-compose run --rm --workdir "/app/zephir" zephir generate
docker-compose run --rm --workdir "/app/zephir" zephir compile
docker-compose build php-ext
```

To test it run:

```
docker-compose run --rm php-ext php ext.php
```