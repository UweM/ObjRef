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

use ObjRef\Transport\SerializeTransport;

class TestTransport extends SerializeTransport {
    private $buffer;
    protected function sendStream($data) {
        $this->buffer .= $data;
    }
    protected function recvStream($size) {
        if(strlen($this->buffer) == 0) {
            throw new \PHPUnit_Framework_ExpectationFailedException('Buffer underrun');
        }
        $size = $size <= strlen($this->buffer) ? $size : strlen($this->buffer);
        $r = substr($this->buffer, 0, $size);
        $this->buffer = substr($this->buffer, $size);
        return $r;
    }
    public function setBuffer($buffer) {
        $this->buffer = $buffer;
    }
    public function getBuffer() {
        return $this->buffer;
    }
}

class SerializeTransportTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var TestTransport
     */
    private $transport;

    public function setUp() {
        $this->transport = new TestTransport();
    }

    public function testSerializeTransport() {

        $plain = ['testtest', 123, new \stdClass];
        $encoded = '58'.chr(0).'a:3:{i:0;s:8:"testtest";i:1;i:123;i:2;O:8:"stdClass":0:{}}';

        $this->transport->send($plain);
        $this->assertEquals($encoded, $this->transport->getBuffer());

        $this->transport->setBuffer($encoded);
        $this->assertEquals($plain, $this->transport->recv());

        $this->t($plain);
        $this->t('a');
        $this->t('1');
        $this->t([]);
        $this->t($this->transport);

    }

    private function t($plain) {
        $this->transport->send($plain);
        $this->assertEquals($plain, $this->transport->recv());
    }

}
 