<?php

/*
 * This file is part of the ObjRef package.
 *
 * (c) Uwe Mueller <uwe@namez.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ObjRef\Tests\Proxy;

use ObjRef\Proxy\RefPool;

class RefPoolTest extends \PHPUnit_Framework_TestCase {

    public function testRefPool() {
        $pool = new RefPool();
        $a = new \stdClass();
        $a->val = 'a';
        $b = new \stdClass();
        $b->val = 'b';

        $aref = $pool->pushRef($a);
        $bref = $pool->pushRef($b);
        $bref2 = $pool->pushRef($b);

        $this->assertEquals($bref, $bref2);
        $this->assertNotEquals($aref, $bref);
        $this->assertEquals($a, $pool->getObj($aref));
        $this->assertEquals($b, $pool->getObj($bref));
        $this->assertTrue($pool->exists($aref));
        $this->assertFalse($pool->exists(99999));
        $this->assertNotEquals($pool->pushRef(new \stdClass()), $pool->pushRef(new \stdClass()));
    }
}
 