<?php


namespace ObjRef\Transport;


interface ClientInterface {

    public function read();
    public function write($data);
}
