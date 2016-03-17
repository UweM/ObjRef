<?php
namespace ObjRef\Transport;

abstract class SerializeTransport implements Transport {
    public function send($cmd) {
        $data = serialize($cmd);
        $this->sendStream(strlen($data).chr(0).$data);
    }

    public function recv() {
        // read size
        $size = '';
        while(true) {
            $last = $this->recvStream(1);
            if($last == chr(0)) break;
            $size .= $last;
        }
        $size = intval($size);

        // read $size bytes of data
        $data = '';
        while(strlen($data) < $size) {
            $data .= $this->recvStream($size - strlen($data));
        }
        return unserialize($data);
    }


    protected abstract function sendStream($data);
    protected abstract function recvStream($size);

}
