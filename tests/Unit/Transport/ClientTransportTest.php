<?php

/*
 * This file is part of the ObjRef package.
 *
 * (c) Uwe Mueller <uwe@namez.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ObjRef\Tests\Transport;

use ObjRef\Transport\ClientInterface;
use ObjRef\Transport\ClientTransport;
use ObjRef\Transport\StreamClosedException;

class TestClient implements ClientInterface {
    private $buffer;
    public function read() {
        return $this->buffer;
    }
    public function write($data) {
        $this->buffer = $data;
    }
    public function getBuffer() {
        return $this->buffer;
    }
    public function setBuffer($buffer) {
        $this->buffer = $buffer;
    }
}

class ClientTransportTest extends \PHPUnit_Framework_TestCase {

    public function testBufferedTransport() {
        $plain = ['testtest', 123, new \stdClass];
        $encoded = '58'.chr(0).'a:3:{i:0;s:8:"testtest";i:1;i:123;i:2;O:8:"stdClass":0:{}}';
        $c = new TestClient();
        $t = new ClientTransport($c);

        $c->setBuffer($encoded);
        $this->assertEquals($plain, $t->recv());

        $t->send($plain);
        $this->assertEquals($encoded, $c->getBuffer());


        $this->expectException(StreamClosedException::class);
        $c->setBuffer(false);
        $t->recv();

    }


}
 