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


class ClientTransport extends BufferedTransport {
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client) {
        $this->client = $client;
    }

    protected function readChunk() {
        $data = $this->client->read();
        if(is_bool($data)) {
            throw new StreamClosedException();
        }
        return $data;
    }

    protected function sendStream($data) {
        $this->client->write($data);
    }
}
