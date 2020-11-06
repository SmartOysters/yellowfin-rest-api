<?php

/*
 * This file is part of the Yellowfin REST API PHP Package
 *
 * (c) James Rickard <james.rickard@smartoysters.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartOysters\Yellowfin\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use SmartOysters\Yellowfin\Helpers\ArrayHelpers;

class ArrayHelpersTest extends TestCase
{
    /**
     * @dataProvider arrayFlattenProvider
     */
    public function testArrayFlatten($result, $string)
    {
        $mockTrait = $this->getMockForTrait(ArrayHelpers::class);

        $this->assertEquals($result, $mockTrait->arrayFlatten($string));
    }

    public function arrayFlattenProvider()
    {
        return [
            [ ['foo'], ['foo'] ],
            [ ['foo', 'bar'], ['foo', 'bar'] ],
            [ ['Joe', 'PHP', 'Ruby'], ['name' => 'Joe', 'languages' => ['PHP', 'Ruby']] ],
            [ ['Joe', 'PHP', 'Ruby', 'one', 'two', 'three'], ['name' => 'Joe', 'languages' => ['PHP', 'Ruby', 'versions' => ['one', 'two', 'three']]] ]
        ];
    }

    /**
     * @dataProvider arrayExcludeProvider
     */
    public function testArrayExclude($result, $array, $exclude)
    {
        $mockTrait = $this->getMockForTrait(ArrayHelpers::class);

        $this->assertEquals($result, $mockTrait->arrayExclude($array, $exclude));
    }

    public function arrayExcludeProvider()
    {
        return [
            [ [], ['foo' => 'bar'], ['foo'] ],
            [ ['bar' => 'foo'], ['foo' => 'bar', 'bar' => 'foo'], ['foo'] ],
            [ ['name' => 'Joe', 0 => 'PHP'], ['name' => 'Joe', 0 => 'PHP', 'Ruby' => 'Ruby'], ['PHP', 'Ruby'] ],
            [ ['name' => 'Joe', 0 => 'Ruby'], ['name' => 'Joe', 'language' => 'PHP', 'Ruby'], ['language', 'Ruby'] ]
        ];
    }

    public function testArraySetNullReplaceValues()
    {
        $mockTrait = $this->getMockForTrait(ArrayHelpers::class);

        $array = ['foo' => 'bar'];
        $expected = ['one', 'two'];
        $this->assertEquals($expected, $mockTrait->arraySet($array, null, $expected), '->arraySet() replaces array when null passed as key');
    }

    public function testArraySetSingleKey()
    {
        $mockTrait = $this->getMockForTrait(ArrayHelpers::class);

        $array = ['foo' => 'bar'];
        $expected = ['one', 'two'];

        $this->assertEquals(['foo' => $expected], $mockTrait->arraySet($array, 'foo', $expected), '->arraySet() replaces array when null passed as key');
    }

    public function testArraySetTwoKeys()
    {
        $mockTrait = $this->getMockForTrait(ArrayHelpers::class);

        $array = ['foo' => 'bar', 'bar' => 'foo', 'baz' => 'catz'];
        $expected = ['one', 'two'];

        $this->assertEquals(['baz' => $expected], $mockTrait->arraySet($array, 'foo.baz', $expected), '->arraySet() last object of the dot notation is key');
    }

    public function testArraySetTwoKeysNested()
    {
        $mockTrait = $this->getMockForTrait(ArrayHelpers::class);

        $array = ['foo' => ['bar' => 'foo', 'baz' => 'catz']];
        $expected = ['one', 'two', 'three'];

        $this->assertEquals(['bar' => 'foo', 'baz' => $expected], $mockTrait->arraySet($array, 'foo.baz', $expected), '->arraySet() nested array set in correct key');
    }


    public function arrayMapArrayKeysProvider()
    {
        return [
            [ ['fooBarFoo' => 'Joe', 'barFooBar' => 'PHP'], ['foo_bar_foo' => 'Joe', 'bar_foo_bar' => 'PHP'] ],
            [ ['foo' => 'bar'], ['foo' => 'bar'] ],
            [ ['barFoo' => 'foo'], ['bar_foo' => 'foo'] ],
            [ [0 => 'Joe', 0 => 'PHP'], [0 => 'Joe', 0 => 'PHP'] ],
            [ [ 'fooBar' => [ 'fooFoo' => 'bar', 'barBar' => 'foo', 'bar' => [ 'fooBarFoo' => 'foo', 'fooBarBar' => 'foo' ] ]], [ 'foo_bar' => [
                'foo_foo' => 'bar',
                'bar_bar' => 'foo',
                'bar' => [
                    'foo_bar_foo' => 'foo',
                    'foo_bar_bar' => 'foo'
                ]
            ]] ]
        ];
    }

    /**
     * @dataProvider arrayMapArrayKeysProvider
     */
    public function testMapArrayKeys($result, $trial)
    {
        $mockTrait = $this->getMockForTrait(ArrayHelpers::class);

        $this->assertEquals($result, $mockTrait->mapArrayKeys(function ($value) {
            return lcfirst(implode('', array_map('ucfirst', explode('_', $value))));
        }, $trial));
    }

}
