<?php

/*
 * This file is part of the Yellowfin REST API PHP Package
 *
 * (c) James Rickard <james.rickard@smartoysters.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartOysters\Yellowfin\Tests\Http;

use PHPUnit\Framework\TestCase;
use SmartOysters\Yellowfin\Http\Request;
use SmartOysters\Yellowfin\Http\Client;
use SmartOysters\Yellowfin\Http\Response;


class RequestTest extends TestCase
{
    public function testConstructor()
    {
        $mockClient = $this->createMock(Client::class);

        $request = new Request($mockClient);
        $this->assertInstanceOf('SmartOysters\Yellowfin\Http\Request', $request);
    }

    public function testGetMethod()
    {
        $content = ['success' => true, 'data' => ['foo' => 'bar']];

        $mockClient = $this->createMock(Client::class);
        $mockResponse = $this->createMock(Response::class);

        $mockResponse->expects($this->exactly(1))
            ->method('isSuccess')
            ->willReturn(true);
        $mockResponse->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(200);
        $mockResponse->expects($this->exactly(2))
            ->method('getContent')
            ->willReturn((object)$content);

        $mockClient->expects($this->once())
            ->method('get')
            ->with('foo/1')
            ->willReturn($mockResponse);

        $request = new Request($mockClient);

        $response = $request->get('foo/:id', ['id' => 1]);

        $this->assertTrue($response->isSuccess());
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(['foo' => 'bar'], $response->getContent()->data);
    }

    public function testPutMethod()
    {
        $content = ['success' => true, 'data' => ['foo' => 'bar']];

        $mockClient = $this->createMock(Client::class);
        $mockResponse = $this->createMock(Response::class);

        $mockResponse->expects($this->exactly(1))
            ->method('isSuccess')
            ->willReturn(true);
        $mockResponse->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(200);
        $mockResponse->expects($this->exactly(2))
            ->method('getContent')
            ->willReturn((object)$content);

        $mockClient->expects($this->once())
            ->method('put')
            ->with('foo/1', ['name' => 'bar'])
            ->willReturn($mockResponse);

        $request = new Request($mockClient);

        $response = $request->put('foo/:id', ['id' => 1, 'name' => 'bar']);

        $this->assertTrue($response->isSuccess());
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(['foo' => 'bar'], $response->getContent()->data);
    }

}
