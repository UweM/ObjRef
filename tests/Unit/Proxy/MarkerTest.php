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

use ObjRef\Proxy\Marker;

class MarkerTest extends \PHPUnit_Framework_TestCase {

    public function testMarker() {
        $ref = 123;
        $classname = 'And\\Classname\\Foobar';
        $marker = new Marker($ref, $classname);

        $this->assertEquals($ref, $marker->getRef());
        $this->assertEquals($classname, $marker->getClassname());
    }
}
 