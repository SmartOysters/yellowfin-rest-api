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
use SmartOysters\Yellowfin\Yellowfin;

class YellowfinTest extends TestCase
{
    public function testConstructor()
    {
        $yellowfin = new Yellowfin();
        $this->assertInstanceOf('SmartOysters\Yellowfin\Yellowfin', $yellowfin);
    }

}
