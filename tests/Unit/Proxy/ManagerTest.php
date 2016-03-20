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

use Doctrine\Common\Annotations\AnnotationReader;
use ObjRef\Proxy\Manager;
use ObjRef\Proxy\Generator;
use ObjRef\Proxy\Marker;
use ObjRef\Proxy\RefPool;
use Mockery as m;

/**
 * @\ObjRef\TransferObject
 */
class TransferableObject {
    private $foo, $bar;
    public function __construct($tree=3) {
        if($tree) {
            $this->foo = new \stdClass();
            $this->foo->tree = $tree;
            $this->bar = new TransferableObject($tree-1);
        }
    }
    public function getFoo() { return $this->foo; }
    public function getBar() { return $this->bar; }
}

class ManagerTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Manager
     */
    private $manager;

    public function setUp() {

        $reader = new AnnotationReader();
        $path = tempnam(sys_get_temp_dir(), 'GeneratorTest');
        unlink($path);
        $this->manager = new Manager($reader, new Generator($path, 'TESTSPACE'));

        parent::setUp();
    }

    public function tearDown() {
        m::close();
    }

    public function testIsTransferObject() {
        $this->assertFalse($this->manager->isTransferObject($this));
        $this->assertTrue($this->manager->isTransferObject(new TransferableObject()));
    }

    public function testInsertProxyMarker() {

        // bei einfacher klasse muss sofort ein marker kommen
        $this->assertTrue($this->manager->insertProxyMarker(new \stdClass()) instanceof Marker);

        // bei transferbaren objekten muss tief gesucht werden
        $obj = new TransferableObject();
        /** @var TransferableObject $n */
        $arr = $this->manager->insertProxyMarker([$obj, 'string']);

        $this->assertCount(2, $arr);
        $this->assertEquals('string', $arr[1]);
        $n = $arr[0];

        $this->assertTrue($n->getFoo() instanceof Marker);
        $this->assertTrue($n->getBar() instanceof TransferableObject);
        $this->assertTrue($n->getBar()->getFoo() instanceof Marker);
        $this->assertTrue($n->getBar()->getBar() instanceof TransferableObject);
        $this->assertTrue($n->getBar()->getBar()->getFoo() instanceof Marker);
        $this->assertTrue($n->getBar()->getBar()->getBar() instanceof TransferableObject);
    }

    public function testRestoreWithoutProxyMarker() {
        $obj = new TransferableObject();
        /** @var TransferableObject $n */
        $n = $this->manager->insertProxyMarker([$obj, 'string']);
        $arr = $this->manager->restoreWithoutProxyMarker($n);

        $this->assertCount(2, $arr);
        /** @var TransferableObject $orig */
        $orig = $arr[0];

        $this->assertInstanceOf('stdClass',                           $orig->getFoo());
        $this->assertInstanceOf(__NAMESPACE__.'\\TransferableObject', $orig->getBar());
        $this->assertInstanceOf('stdClass',                           $orig->getBar()->getFoo());
        $this->assertInstanceOf(__NAMESPACE__.'\\TransferableObject', $orig->getBar()->getBar());
        $this->assertInstanceOf('stdClass',                           $orig->getBar()->getBar()->getFoo());
        $this->assertInstanceOf(__NAMESPACE__.'\\TransferableObject', $orig->getBar()->getBar()->getBar());

        $this->assertEquals(3, $orig->getFoo()->tree);
        $this->assertEquals(2, $orig->getBar()->getFoo()->tree);
        $this->assertEquals(1, $orig->getBar()->getBar()->getFoo()->tree);
    }

    public function testFindProxyMarker() {
        require_once __DIR__ . '/Mock/stdClassProxyMock.php';
        $obj = new TransferableObject();
        /** @var TransferableObject $n */
        $n = $this->manager->insertProxyMarker([$obj, 'string']);
        /** @noinspection PhpParamsInspection */
        $arr = $this->manager->findProxyMarker(m::mock('ObjRef\HostInterface'), $n);

        $this->assertCount(2, $arr);
        /** @var TransferableObject $nobj */
        $nobj = $arr[0];

        $this->assertInstanceOf('TESTSPACE\stdClass',                 $nobj->getFoo());
        $this->assertInstanceOf(__NAMESPACE__.'\\TransferableObject', $nobj->getBar());
        $this->assertInstanceOf('TESTSPACE\stdClass',                 $nobj->getBar()->getFoo());
        $this->assertInstanceOf(__NAMESPACE__.'\\TransferableObject', $nobj->getBar()->getBar());
        $this->assertInstanceOf('TESTSPACE\stdClass',                 $nobj->getBar()->getBar()->getFoo());
        $this->assertInstanceOf(__NAMESPACE__.'\\TransferableObject', $nobj->getBar()->getBar()->getBar());
    }

    public function testGetRefPool() {
        $this->assertTrue($this->manager->getRefPool() instanceof RefPool);
    }
}
 