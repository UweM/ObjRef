<?php


namespace ObjRef\Proxy;


interface GeneratorInterface {

    /**
     * @param $classFQCN
     */
    public function generate($classFQCN);

    /**
     * @param $classFQCN
     * @return string
     */
    public function getFullProxyPath($classFQCN);

    /**
     * @param $classFQCN
     * @return mixed
     */
    public function getProxyName($classFQCN);

    /**
     * @param $classFQCN
     * @return string
     */
    public function getProxyNameFQCN($classFQCN);
} 