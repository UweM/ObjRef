<?php

/*
 * This file is part of the ObjRef package.
 *
 * (c) Uwe Mueller <uwe@namez.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ObjRef\Transport;

abstract class BufferedTransport extends SerializeTransport {

    private $buffer;

    protected function recvStream($size) {
        $buflen = strlen($this->buffer);
        if($buflen == 0) {
            $this->buffer = $this->readChunk();
            $buflen = strlen($this->buffer);
        }
        if($buflen > $size) {
            // cut a peace out of the buffer
            $r = substr($this->buffer, 0, $size);
            $this->buffer = substr($this->buffer, $size);
        } else {
            // return whole buffer
            $r = $this->buffer;
            $this->buffer = '';
        }
        return $r;
    }

    protected abstract function readChunk();
}
