<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDbTest\Http;

use ArangoDb\Http\VpackStream;
use PHPUnit\Framework\TestCase;

class VpackStreamTest extends TestCase
{
    public function providerStream(): array
    {
        return [
            'json' => [
                '{"test":1,"stream":true,"name":"vpack"}',
            ],
            'array' => [
                [
                    'test' => 1,
                    'stream' => true,
                    'name' => 'vpack',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerStream
     * @test
     */
    public function it_handles_php_data($data): void
    {
        $json = $data;

        if (is_array($data)) {
            $json = json_encode($data);
        }

        $cut = new VpackStream($json);

        $this->assertEquals($json, $cut->getContents());
        $this->assertEquals($json, (string)$cut);
        $this->assertEquals(strlen($json), $cut->getSize());

        $this->assertEquals('{"test":1,"stream":t', $cut->read(20));
        $this->assertEquals('rue,"name":"vpack"}', $cut->read(20));
        $this->assertEquals('', $cut->read(22));
    }

    /**
     * @test
     */
    public function it_handles_vpack_data(): void
    {
        $json = '{"test":1,"stream":true,"name":"vpack"}';
        $vpack = \Velocypack\Vpack::fromJson($json);
        // Vpack sorts json data
        $json = '{"name":"vpack","stream":true,"test":1}';

        $cut = new VpackStream($vpack->toBinary(), true);

        $this->assertEquals($json, $cut->getContents());
        $this->assertEquals($json, (string)$cut);
        $this->assertEquals(strlen($json), $cut->getSize());

        $this->assertEquals('{"name":"vpack","str', $cut->read(20));
        $this->assertEquals('eam":true,"test":1}', $cut->read(20));
        $this->assertEquals('', $cut->read(22));
    }
}