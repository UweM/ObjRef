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


use Doctrine\Common\Annotations\Reader;
use ObjRef\TransferObject;
use ObjRef\HostInterface;

class Manager implements ManagerInterface {

    /**
     * @var RefPool
     */
    private $refPool;

    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $annotationReader;

    /**
     * @var GeneratorInterface
     */
    private $proxyGenerator;


    public function __construct(Reader $annotationReader, GeneratorInterface $proxyGenerator) {
        $this->refPool = new RefPool();
        $this->annotationReader = $annotationReader;
        $this->proxyGenerator = $proxyGenerator;
    }

    /**
     * @inheritdoc
     */
    public function insertProxyMarker($a) {
        if(is_object($a)) {
            if(!$this->isTransferObject($a)) {
                $ref = $this->refPool->pushRef($a);
                $a = new Marker($ref, get_class($a));
            } else {
                // look for objects in transferobject properties
                $r = new \ReflectionClass($a);
                foreach($r->getProperties() as $prop) {
                    $prop->setAccessible(true);
                    $pval = $prop->getValue($a);
                    if(!is_scalar($pval)) {
                        $pval = $this->insertProxyMarker($pval);
                        $prop->setValue($a, $pval);
                    }
                }
            }
        }
        else if(is_array($a)) {
            foreach($a as &$val) {
                $val = $this->insertProxyMarker($val);
            }
        }
        return $a;
    }

    /**
     * @inheritdoc
     */
    public function restoreWithoutProxyMarker($a) {
        if(is_object($a)) {
            if($this->isTransferObject($a)) {
                $r = new \ReflectionClass($a);
                foreach($r->getProperties() as $prop) {
                    $prop->setAccessible(true);
                    $pval = $prop->getValue($a);
                    if($pval instanceof Marker) {
                        $pval = $this->refPool->getObj($pval->getRef());
                    }
                    else {
                        $pval = $this->restoreWithoutProxyMarker($pval);
                    }
                    $prop->setValue($a, $pval);
                }
            }
        }
        else if(is_array($a)) {
            foreach($a as &$val) {
                $val = $this->restoreWithoutProxyMarker($val);
            }
        }
        return $a;
    }

    /**
     * @inheritdoc
     */
    public function isTransferObject($obj) {
        $reflClass = new \ReflectionClass($obj);
        $classAnnotations = $this->annotationReader->getClassAnnotations($reflClass);
        foreach ($classAnnotations AS $annot) {
            if ($annot instanceof TransferObject) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function findProxyMarker(HostInterface $host, $a) {
        if(is_object($a))
            if($a instanceof Marker) {
                $a = $this->createProxyInstance($host, $a->getClassname(), $a->getRef());
            } else {
                // must be a transfer object
                $r = new \ReflectionClass($a);
                foreach($r->getProperties() as $prop) {
                    $prop->setAccessible(true);
                    $pval = $prop->getValue($a);
                    if(!is_scalar($pval)) {
                        $pval = $this->findProxyMarker($host, $pval);
                        $prop->setValue($a, $pval);
                    }
                }
            }
        else if(is_array($a)) {
            foreach($a as &$val) {
                $val = $this->findProxyMarker($host, $val);
            }
        }
        return $a;
    }

    /**
     * @inheritdoc
     */
    public function createProxyInstance(HostInterface $host, $class, $ref) {
        $name = $this->proxyGenerator->getProxyNameFQCN($class);
        return new $name($this, $host, $ref);
    }

    /**
     * @return RefPool
     */
    public function getRefPool() {
        return $this->refPool;
    }
}
 