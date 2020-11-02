<?php

/*
 * This file is part of the Yellowfin REST API PHP Package
 *
 * (c) James Rickard <james.rickard@smartoysters.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartOysters\Yellowfin\Tests;

use PHPUnit\Framework\TestCase;
use SmartOysters\Yellowfin\Exception\YellowfinException;
use SmartOysters\Yellowfin\Http\Request;
use SmartOysters\Yellowfin\Resources\Base\Resource;


class ResourceTest extends TestCase
{
    public function testConstructor()
    {
        $resource = $this->getMockBuilder(Resource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertInstanceOf('SmartOysters\Yellowfin\Resources\Base\Resource', $resource);
    }

    public function testAllFunctionsEnabled()
    {
        $resource = $this->getMockBuilder(Resource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assertEquals(['*'], $resource->getEnabled(), '->getEnabled() returns `*` when first constructed');

        $this->assertTrue($resource->isEnabled('list'), 'List endpoint is available');
        $this->assertTrue($resource->isEnabled('fetch'), 'Fetch endpoint is available');
        $this->assertTrue($resource->isEnabled('create'), 'Create endpoint is available');
        $this->assertTrue($resource->isEnabled('update'), 'Update endpoint is available');
        $this->assertTrue($resource->isEnabled('delete'), 'Delete endpoint is available');
    }

    public function testEnabledFunctions()
    {
        $mockRequest = $this->createMock(Request::class);
        $resource = $this->getMockBuilder(Resource::class)
            ->setConstructorArgs([$mockRequest])
            ->setMockClassName('testResource')
            ->getMockForAbstractClass();

        $resource->setEnabled(['list', 'fetch']);

        $this->assertTrue($resource->isEnabled('list'), 'List endpoint is available');
        $this->assertTrue($resource->isEnabled('fetch'), 'Fetch endpoint is available');
        $this->assertFalse($resource->isEnabled('update'), 'Update endpoint is disabled');
        $this->assertFalse($resource->isEnabled('create'), 'Create endpoint is disabled');
        $this->assertFalse($resource->isEnabled('delete'), 'Delete endpoint is disabled');

        $this->expectException(YellowfinException::class);
        $this->expectExceptionMessage('The method update() is not available for the resource test_resource');
        $resource->__call('update', ['id', []]);

        $this->expectException(YellowfinException::class);
        $this->expectExceptionMessage('The method create() is not available for the resource test_resource');
        $resource->__call('create', [[]]);
    }

    public function testDisabledFunctions()
    {
        $mockRequest = $this->createMock(Request::class);
        $resource = $this->getMockBuilder(Resource::class)
            ->setConstructorArgs([$mockRequest])
            ->setMockClassName('testResource')
            ->getMockForAbstractClass();

        $resource->setDisabled(['list', 'fetch']);

        $this->assertFalse($resource->isEnabled('list'), 'List endpoint is available');
        $this->assertFalse($resource->isEnabled('fetch'), 'Fetch endpoint is available');
        $this->assertTrue($resource->isEnabled('update'), 'Update endpoint is disabled');
        $this->assertTrue($resource->isEnabled('create'), 'Create endpoint is disabled');
        $this->assertTrue($resource->isEnabled('delete'), 'Delete endpoint is disabled');

        $this->expectException(YellowfinException::class);
        $this->expectExceptionMessage('The method list() is not available for the resource test_resource');
        $resource->__call('list', [[]]);

        $this->expectException(YellowfinException::class);
        $this->expectExceptionMessage('The method fetch() is not available for the resource test_resource');
        $resource->__call('fetch', ['id']);
    }
}
