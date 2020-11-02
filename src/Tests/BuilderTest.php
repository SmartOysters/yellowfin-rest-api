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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SmartOysters\Yellowfin\Builder;


class BuilderTest extends TestCase
{
    public function testConstructor()
    {
        $builder = new Builder();
        $this->assertInstanceOf('SmartOysters\Yellowfin\Builder', $builder);
    }

    public function testRemoveOptionsInURI()
    {
        $builder = new Builder();
        $builder->setToken('foobar');

        $builder->setTarget('foo/:id');
        $this->assertEquals([], $builder->getQueryVars(['id' => 1]), 'Return empty array when only URI values passsed');
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $builder->getQueryVars(['id' => 1, 'foo' => 'bar', 'bar' => 'foo']), 'Returns array with values');


        $builder->setTarget('foo');
        $this->assertEquals(['id' => 1], $builder->getQueryVars(['id' => 1]), 'Returns array with values');
    }

    /**
     * @dataProvider parametersProvider
     */
    public function testGetParameters($result, $parameters, $message)
    {
        $builder = new Builder();
        $builder->setToken('foobar');

        $this->assertEquals($result, $builder->getParameters($parameters), $message);
    }

    public function parametersProvider()
    {
        return [
            [ ['id'], 'foo/:id', 'Return `id` as value.' ],
            [ ['id', 'name'], 'foo/:id/:name', 'Return `id` and `name`.' ],
            [ ['name', 'id'], ':name/foo/:id', 'Return `name` and `id`.' ],
            [ ['id', 'name'], 'foo/:id/bar/:name', 'Return `if` and `name`' ]
        ];
    }

    public function testBuildEndpoint()
    {
        $builder = new Builder();
        $builder->setToken('foobar');

        $builder->setTarget('foo/:id');
        $this->assertEquals('foo/1', $builder->buildEndpoint(['id' => 1]), 'Builds correct endpoint');

        $builder->setTarget(':id/:name');
        $this->assertEquals('1/foo', $builder->buildEndpoint(['id' => 1, 'name' => 'foo']), 'Builds correct endpoint');

        $this->expectException(InvalidArgumentException::class);
        $builder->setTarget(':id/foo');
        $builder->buildEndpoint(['bar' => 'baz']);
    }

}
