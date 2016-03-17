<?php
namespace ObjRef\Transport;

interface Transport {
    public function send($cmd);
    public function recv();
}
