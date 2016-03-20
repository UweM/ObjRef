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

class FDTransport extends SerializeTransport {
    private $infd, $outfd;
    public function __construct($infd, $outfd=null) {
        if(!$outfd) $outfd = $infd;

        $this->infd = $infd;
        $this->outfd = $outfd;
    }

    protected function sendStream($data) {
        if(
            !is_resource($this->outfd) ||
            fwrite($this->outfd, $data) === false
        ) {
            throw new StreamClosedException();
        }
    }

    protected function recvStream($size) {
        if(!is_resource($this->infd)) {
            throw new StreamClosedException();
        }
        $data = fread($this->infd, $size);
        if($data === false || $data == '') {
            throw new StreamClosedException();
        }
        return $data;
    }
}
