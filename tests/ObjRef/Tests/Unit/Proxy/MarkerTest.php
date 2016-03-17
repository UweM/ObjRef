<?php
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
 