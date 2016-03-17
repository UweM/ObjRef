<?php
namespace ObjRef\Tests;

use ObjRef\C;
use ObjRef\Host;
use ObjRef\Proxy\RefPool;
use ObjRef\Transport\StreamClosedException;
use ObjRef\Transport\Transport;
use Mockery as m;

class TestException extends \Exception { }

class TestTransport implements Transport {
    private $buffer, $ring, $isValid, $closure=null;
    public function __construct($ring=true) {
        $this->ring = $ring;
        $this->isValid = $ring;
    }
    public function send($cmd) {
        if(is_callable($this->closure)) {
            $c = $this->closure;
            $c();
        }
        else {
            $this->buffer = $cmd;
            if(!$this->ring) $this->isValid = false;
        }
    }
    public function recv() {
        if($this->isValid) $b = $this->buffer;
        else $b = false;
        if(!$this->ring) $this->isValid = false;
        return $b;
    }
    public function setBuffer($buffer) {
        $this->buffer = $buffer;
        $this->isValid = true;
    }
    public function getBuffer() {
        return $this->buffer;
    }
    public function setRing($ring) {
        $this->ring = $ring;
        $this->isValid = $ring;
    }
    public function setClosure($c) {
        $this->closure = $c;
    }
}

class HostTest extends \PHPUnit_Framework_TestCase {

    const MAGIC = "abc123";

    /**
     * @var TestTransport
     */
    private $transport;

    /** @var  Host */
    private $host;

    /**
     * @var \stdClass
     */
    private $initialObj;

    /**
     * @var RefPool
     */
    private $refpool;

    public function setUp() {
        $this->initialObj = new \stdClass();
        $this->transport = new TestTransport();
        $this->refpool = new RefPool();
        $manager = m::mock('ObjRef\Proxy\ManagerInterface');
        $manager
            ->shouldReceive('createProxyInstance')
            ->with(m::any(), 'stdClass', m::any())
            ->andReturn(self::MAGIC)
        ;
        $manager
            ->shouldReceive('getRefPool')
            ->withNoArgs()
            ->andReturn($this->refpool)
        ;
        /** @noinspection PhpUnusedParameterInspection */
        $manager
            ->shouldReceive('findProxyMarker')
            ->andReturnUsing(function($obj, $a) {
                return $a;
            })
        ;
        $manager
            ->shouldReceive('isTransferObject')
            ->andReturn(false)
        ;
        /** @noinspection PhpParamsInspection */
        $this->host = new Host($manager, $this->initialObj);
        $this->host->setTransport($this->transport);
    }

    public function tearDown() {
        m::close();
    }

    public function testGetTransport() {
        $this->assertEquals($this->transport, $this->host->getTransport());
    }

    public function testGetRemoteInitialObjectName() {
        $r = $this->host->getRemoteInitialObjectName();
        $this->assertEquals('stdClass', $r);
    }

    public function testGetRemoteInitialObject() {
        $obj = $this->host->getRemoteInitialObject();
        $this->assertEquals(self::MAGIC, $obj);
    }

    public function testDoCmdOnRef() {
        $obj = $this->getCmdMock();
        $ref = $this->refpool->pushRef($obj);
        $reflect = new \ReflectionObject($this->host);
        $m = $reflect->getMethod('doCmdOnRef');
        $m->setAccessible(true);

        $r = $this->runCmdOnRef($m, $ref, C::CALL, 'noParamCall', []);
        $this->assertEquals('foo', $r);

        $r = $this->runCmdOnRef($m, $ref, C::CALL, 'withParamCall', ['a', 'b', 1]);
        $this->assertEquals('bar', $r);

        $this->runCmdOnRef($m, $ref, C::SET, 'var', ['foo']);

        $r = $this->runCmdOnRef($m, $ref, C::GET, 'var', null);
        $this->assertEquals('foo', $r);

        $r = $this->runCmdOnRef($m, $ref, C::CISSET, 'var', null);
        $this->assertTrue($r);

        $this->runCmdOnRef($m, $ref, C::CUNSET, 'var', null);

        $r = $this->runCmdOnRef($m, $ref, C::CISSET, 'var', null);
        $this->assertFalse($r);


        $this->expectException(\ErrorException::class);
        $this->runCmdOnRef($m, $ref, -999, null, null);
    }

    private function runCmdOnRef(\ReflectionMethod $method, $ref, $cmd, $name, $args) {
        $c = [
            C::REF => $ref,
            C::CMD => $cmd,
            C::NAME => $name,
            C::ARGS => $args,
        ];
        return $method->invoke($this->host, $c);
    }

    private function getCmdMock() {
        $obj = m::mock();
        $obj
            ->shouldReceive('noParamCall')
            ->once()
            ->withNoArgs()
            ->andReturn('foo')
        ;
        $obj
            ->shouldReceive('withParamCall')
            ->once()
            ->with('a', 'b', 1)
            ->andReturn('bar')
        ;
        return $obj;
    }


    public function testRunOnObject() {
        $reflect = new \ReflectionObject($this->host);
        $m = $reflect->getMethod('runOnObject');
        $m->setAccessible(true);

        $s = m::mock();
        $s->shouldReceive('foo')->withNoArgs()->andReturn('foobar');
        $refS = $this->refpool->pushRef($s);

        $this->runCmdOnRef($m, $refS, C::CALL, 'foo', []);
        $this->assertEquals([C::CMD => C::CRETURN, C::SCALAR => 'foobar'], $this->transport->getBuffer());


        $p = m::mock();
        $p->shouldReceive('bar')->withNoArgs()->andReturn(new \stdClass());
        $refP = $this->refpool->pushRef($p);

        $this->runCmdOnRef($m, $refP, C::CALL, 'bar', []);
        $this->assertEquals([C::CMD => C::CRETURN, C::REF => $refP+1, C::CLASSNAME => 'stdClass'], $this->transport->getBuffer());

        $this->expectException(\ErrorException::class);
        $this->runCmdOnRef($m, -999, null, null, null);

    }

    public function testRun() {
        $this->transport->setRing(false);
        $s = m::mock('stdClass');
        $s->shouldReceive('foo')->withNoArgs()->andReturn('foobar');
        $ref = $this->refpool->pushRef($s);


        $this->transport->setBuffer([
            C::REF => $ref,
            C::CMD => C::CALL,
            C::NAME => 'foo',
            C::ARGS => [],
        ]);
        $this->host->run();
        $this->assertEquals([C::CMD => C::CRETURN, C::SCALAR => 'foobar'], $this->transport->getBuffer());


        $this->transport->setBuffer([
            C::REF => $ref,
            C::CMD => C::CRETURN,
            C::CLASSNAME => 'stdClass',
            C::ARGS => [],
        ]);
        $proxy = $this->host->run();
        $this->assertEquals(self::MAGIC, $proxy);

        $this->transport->setBuffer([
            C::CMD => C::CRETURN,
            C::SCALAR => 5,
        ]);
        $val = $this->host->run();
        $this->assertEquals(5, $val);

        $this->transport->setBuffer([
            C::CMD => C::CRETURN,
            C::SCALAR => null,
        ]);
        $val = $this->host->run();
        $this->assertNull($val);


    }

    public function testRunTransferedException() {
        $this->transport->setRing(false);
        $transport = $this->transport;
        $exception = new \Exception;
        $transport->setClosure(function() use($transport, $exception) {
            $transport->setClosure(null);
            throw $exception;
        });

        $this->transport->setBuffer([C::CMD => C::GET_INIT_CLASSNAME]);
        $this->host->run();
        $this->assertEquals([C::CMD => C::EXCEPTION, C::OBJECT => $exception], $this->transport->getBuffer());
    }

    public function testRunStreamClosedException() {
        $this->expectException(StreamClosedException::class);
        $this->transport->setClosure(function() {
            throw new StreamClosedException;
        });
        $this->transport->setBuffer([C::CMD => C::GET_INIT_CLASSNAME]);
        $this->host->run();
    }

    public function testRunTestException() {
        $this->expectException(TestException::class);
        $this->transport->setBuffer([
            C::CMD => C::EXCEPTION,
            C::OBJECT => new TestException(),
        ]);
        $this->host->run();
    }
}
