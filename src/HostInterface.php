<?php
namespace ObjRef;

use ObjRef\Transport\StreamClosedException;
use ObjRef\Transport\Transport;

interface HostInterface {
    /**
     * Main loop. Do commands received from the other side and run
     * until the stream is closed or we get a "return" command
     * to return data to the callee
     *
     * @return mixed
     * @throws StreamClosedException
     * @throws \Exception
     */
    public function run();
    public function getRemoteInitialObjectName();
    public function getRemoteInitialObject();

    /**
     * @return Transport
     */
    public function getTransport();

} 