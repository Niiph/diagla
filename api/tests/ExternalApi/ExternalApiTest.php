<?php

namespace App\Tests\ExternalApi;

use App\DataFixtures\Factory\DeviceFactory;
use App\DataFixtures\Factory\ReadingFactory;
use App\DataFixtures\Factory\SensorFactory;
use App\Tests\TestCase;

class ExternalApiTest extends TestCase
{
    public function test_time_endpoint(): void
    {
        $device = DeviceFactory::createOne();

        static::createClient()->request(
            'POST',
            '/api/time',
            [
                'headers' => ['accept' => ['application/json']],
                'json' => ['shortId' => $device->getShortId()],
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'date' => '2023-07-01T12:34:56+00:00',
        ]);
    }

    public function test_sensors_list_endpoint(): void
    {
        $sensor = SensorFactory::createOne();
        $device = $sensor->getDevice();

        $token = hash(
            'sha256',
            sprintf(
                '%s_%s_%s',
                $device->getDevicePassword(),
                $device->getShortId(),
                '2023-07-01T12:34:56+00:00'
            )
        );

        $client = static::createClient();
        $client->request(
            'POST',
            '/api/time',
            [
                'headers' => ['accept' => ['application/json']],
                'json' => ['shortId' => $device->getShortId()],
            ]
        );
        $client->request(
            'GET',
            sprintf('/api/devices/%s/list_sensors', $device->getShortId()),
            [
                'headers' => [
                    'accept' => ['application/json'],
                    'X-Authentication-Token' => $token,
                ],
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            ['id' => 'b944449e-123e-353f-b1b7-b6b39f2eab64']
        ]);
    }

    public function test_sensor_data_endpoint(): void
    {
        $sensor = SensorFactory::createOne();
        $device = $sensor->getDevice();

        $token = hash(
            'sha256',
            sprintf(
                '%s_%s_%s',
                $device->getDevicePassword(),
                $device->getShortId(),
                '2023-07-01T12:34:56+00:00'
            )
        );

        $client = static::createClient();
        $client->request(
            'POST',
            '/api/time',
            [
                'headers' => ['accept' => ['application/json']],
                'json' => ['shortId' => $device->getShortId()],
            ]
        );
        $response = $client->request(
            'GET',
            sprintf('/api/devices/%s/list_sensors', $device->getShortId()),
            [
                'headers' => [
                    'accept' => ['application/json'],
                    'X-Authentication-Token' => $token,
                ],
            ]
        );
        $client->request(
            'GET',
            sprintf('/api/sensors/%s', $response->toArray()[0]['id']),
            [
                'headers' => [
                    'accept' => ['application/json'],
                    'X-Authentication-Token' => $token,
                ],
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'pin' => 19,
            'address' => '8, d6, bc, 47, 5b, 3a, 8a, 3a',
        ]);
    }

    public function test_send_reading_endpoint(): void
    {
        $sensor = SensorFactory::createOne();
        $device = $sensor->getDevice();

        $token = hash(
            'sha256',
            sprintf(
                '%s_%s_%s',
                $device->getDevicePassword(),
                $device->getShortId(),
                '2023-07-01T12:34:56+00:00'
            )
        );

        $client = static::createClient();
        $client->request(
            'POST',
            '/api/time',
            [
                'headers' => ['accept' => ['application/json']],
                'json' => ['shortId' => $device->getShortId()],
            ]
        );
        $response = $client->request(
            'GET',
            sprintf('/api/devices/%s/list_sensors', $device->getShortId()),
            [
                'headers' => [
                    'accept' => ['application/json'],
                    'X-Authentication-Token' => $token,
                ],
            ]
        );

        $this->assertCount(0, ReadingFactory::all());
        $client->request(
            'PUT',
            sprintf('/api/sensors/%s/add_reading', $response->toArray()[0]['id']),
            [
                'headers' => [
                    'accept' => ['application/json'],
                    'X-Authentication-Token' => $token,
                    ],
                'json' => [
                    'type' => 'T',
                    'value' => 25,
                ],
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, ReadingFactory::all());
        $this->assertEquals(25, ReadingFactory::first()->getValue());
        $this->assertEquals('T', ReadingFactory::first()->getType());
    }
}
