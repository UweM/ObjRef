<?php
namespace ObjRef;
use ObjRef\Transport\Transport;
use ObjRef\Transport\StreamClosedException;

class Host implements HostInterface {

    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var Proxy\ManagerInterface
     */
    private $proxyManager;

    public function __construct(Proxy\ManagerInterface $proxyManager, $initialObject){
        $this->proxyManager = $proxyManager;
        $this->proxyManager->getRefPool()->pushRef($initialObject);
    }

    public function setTransport(Transport $transport) {
        $this->transport = $transport;
    }

    /**
     * @inheritdoc
     */
    public function run() {
        while(($c = $this->transport->recv()) !== false) {
            try {
                // got "return" command.
                // we can get two types of return: Returning a scalar or an object.
                // in case of object, we have to create an proxy object for the reference we got
                if($c[C::CMD] == C::CRETURN) {
                    if(array_key_exists(C::SCALAR, $c)) return $c[C::SCALAR];
                    return $this->proxyManager->createProxyInstance($this, $c[C::CLASSNAME], $c[C::REF]);
                }
                // the other side requests the name of our initial object to build a proper proxy class
                if($c[C::CMD] == C::GET_INIT_CLASSNAME) {
                    $this->transport->send([
                        C::CMD => C::CRETURN,
                        C::SCALAR => get_class($this->proxyManager->getRefPool()->getObj(0)),
                    ]);
                }
                // every other command than "return" with a "ref" runs on an object (see "exception" below)
                if(isset($c[C::REF])) {
                    $this->runOnObject($c);
                }

            } catch(StreamClosedException $e) {
                throw $e;
            } catch(\Exception $e) {
                $this->transport->send([
                    C::CMD => C::EXCEPTION,
                    C::OBJECT => $e,
                ]);
            }
            if($c[C::CMD] == C::EXCEPTION) {
                throw $c[C::OBJECT];
            }
        }
        return null;
    }

    /**
     * @param $c
     * @throws \ErrorException
     */
    private function runOnObject($c) {
        if ($this->proxyManager->getRefPool()->exists($c[C::REF])) {
            $return = $this->doCmdOnRef($c);
            if (!is_object($return) || $this->proxyManager->isTransferObject($return)) {
                $cmd = [
                    C::CMD => C::CRETURN,
                    C::SCALAR => $return,
                ];
            } else {
                $cmd = [
                    C::CMD => C::CRETURN,
                    C::REF => $this->proxyManager->getRefPool()->pushRef($return),
                    C::CLASSNAME => get_class($return),
                ];
            }
            $this->transport->send($cmd);
        } else {
            throw new \ErrorException('Object reference not found');
        }
    }

    /**
     * Execute commands from other side on an object
     *
     * @param array $c
     * @return mixed
     * @throws \ErrorException
     */
    private function doCmdOnRef($c) {
        $obj = $this->proxyManager->getRefPool()->getObj($c[C::REF]);
        switch($c[C::CMD]) {
            case C::CALL:
                return call_user_func_array(
                    [$obj, $c[C::NAME]],
                    $this->proxyManager->findProxyMarker($this, $c[C::ARGS])
                );
            case C::GET:
                return $obj->{$c[C::NAME]};
            case C::SET:
                $obj->{$c[C::NAME]} = $c[C::ARGS][0];
                break;
            case C::CISSET:
                return isset($obj->{$c[C::NAME]});
            case C::CUNSET:
                unset($obj->{$c[C::NAME]});
                break;
            default:
                throw new \ErrorException('Object command not found');
        }
        return null;
    }

    public function getRemoteInitialObjectName() {
        $this->getTransport()->send([
            C::CMD => C::GET_INIT_CLASSNAME,
        ]);
        return $this->run();
    }

    public function getRemoteInitialObject() {
        return $this->proxyManager->createProxyInstance($this, $this->getRemoteInitialObjectName(), 0);
    }

    /**
     * @return Transport
     */
    public function getTransport() {
        return $this->transport;
    }
}
