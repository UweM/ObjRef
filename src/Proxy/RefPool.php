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

class RefPool {

    /**
     * @var object[]
     */
    private $refpool = array();

    /**
     * Add an object to the local object store and return the index (transferable reference) to it
     *
     * @param object $obj
     * @return int
     */
    public function pushRef($obj) {
        $key = array_search($obj, $this->refpool, true);
        if($key !== false) return $key;

        $this->refpool[] = $obj;
        return count($this->refpool) - 1;
    }

    public function getObj($ref) {
        return $this->refpool[$ref];
    }

    public function exists($ref) {
        return isset($this->refpool[$ref]);
    }

}
