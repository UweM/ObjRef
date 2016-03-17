<?php
namespace ObjRef\Tests\Transport;

use ObjRef\Transport\BufferedTransport;
use ObjRef\Transport\FDTransport;

class TestBufferTransport extends BufferedTransport {
    private $chunk, $ptr=0;
    protected function readChunk() {
        $r = substr($this->chunk, $this->ptr, 200);
        $this->ptr += 200;
        return $r;
    }

    protected function sendStream($data) {
        // ignore in test
    }

    public function setChunk($chunk) {
        $this->chunk = $chunk;
    }
}

class BufferedTransportTest extends \PHPUnit_Framework_TestCase {

    public function testBufferedTransport() {

        //testdaten generieren
        $fd = fopen('php://memory', 'w+');
        $fdt = new FDTransport($fd, $fd);
        $data = [
            'a',
            str_repeat('b', 20),
            str_repeat('c', 200),
            str_repeat('d', 2000),
            str_repeat('e', 20000),
        ];
        foreach($data as $d) {
            $fdt->send($d);
        }
        fseek($fd, 0);
        $chunk = stream_get_contents($fd);

        // gegentest auf die testdaten
        $obj = new TestBufferTransport();
        $obj->setChunk($chunk);

        foreach($data as $d) {
            $this->assertEquals($obj->recv(), $d);
        }

    }


}
 