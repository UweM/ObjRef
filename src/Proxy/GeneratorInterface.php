<?php

/*
 * This file is part of the ObjRef package.
 *
 * (c) Uwe Mueller <uwe@namez.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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