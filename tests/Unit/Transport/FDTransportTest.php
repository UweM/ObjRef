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

use ObjRef\Transport\FDTransport;
use ObjRef\Transport\StreamClosedException;


class FDTransportTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var FDTransport
     */
    private $transport;

    private $fd;

    public function setUp() {
        $this->fd = fopen('php://memory', 'w+');
        $this->transport = new FDTransport($this->fd, $this->fd);
    }

    public function testSerializeTransport() {
        $this->t(['aa', 1, new \stdClass()]);
        $this->t('a');
        $this->t('1');
        $this->t([]);
    }

    public function testSendClosed() {
        fclose($this->fd);
        $this->expectException(StreamClosedException::class);
        $this->transport->send([]);
    }

    public function testRecvClosed() {
        fclose($this->fd);
        $this->expectException(StreamClosedException::class);
        $this->transport->recv();
    }

    public function testRecvEmpty() {
        $this->expectException(StreamClosedException::class);
        $this->transport->recv();
    }

    private function t($plain) {
        $this->transport->send($plain);
        fseek($this->fd, 0);
        $this->assertEquals($plain, $this->transport->recv());
        fseek($this->fd, 0);
    }

}
 