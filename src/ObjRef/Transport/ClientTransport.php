<?php
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
