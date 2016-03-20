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


use ObjRef\HostInterface;

interface ManagerInterface {


    /**
     * Replace sub-objects of an object with the special "ProxyMarker"
     * object - this will be converted in an "Proxy" on the other side
     *
     * @param $a
     * @return Marker|object
     */
    public function insertProxyMarker($a);

    /**
     * Restore the original object modified by insertProxyMarker() after
     * it got serialized
     *
     * @param $a
     * @return mixed
     */
    public function restoreWithoutProxyMarker($a);

    /**
     * Check if the given object has the TransferObject annotation
     *
     * @param object $obj
     * @return bool
     */
    public function isTransferObject($obj);

    /**
     * Find "ProxyMarker" objects in an object and convert it
     * into "Proxy" objects so they can receive commands
     *
     * @param \ObjRef\HostInterface $host
     * @param $a
     * @return array
     */
    public function findProxyMarker(HostInterface $host, $a);

    /**
     * @param HostInterface $host
     * @param $class
     * @param $ref
     * @return mixed
     */
    public function createProxyInstance(HostInterface $host, $class, $ref);

    /**
     * @return RefPool
     */
    public function getRefPool();

} 