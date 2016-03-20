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

/**
 * Class to mark references for serialization.
 * After transfer and unserialization, this objects get replaced by "Proxy" objects
 */
class Marker {

    /**
     * @var int
     */
    private $ref;
    /**
     * @var string
     */
    private $classname;

    /**
     * @param int $ref
     * @param string $classname
     */
    public function __construct($ref, $classname) {
        $this->ref = $ref;
        $this->classname = $classname;
    }

    /**
     * @return int
     */
    public function getRef() {
        return $this->ref;
    }

    /**
     * @return string
     */
    public function getClassname() {
        return $this->classname;
    }
}
