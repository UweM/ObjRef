<?php

/*
 * This file is part of the ObjRef package.
 *
 * (c) Uwe Mueller <uwe@namez.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ObjRef\Tests\Proxy;


use ObjRef\Proxy\Generator;

class TestObject {
    public function __construct($a) {}
    public function foo(\stdClass $b) { }
    public function bar($hello='world') { }
    protected function prot($x) { }
    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function priv() { }
}

class GeneratorTest extends \PHPUnit_Framework_TestCase {
    public function testGenererator() {

        $fqcn = __NAMESPACE__.'\\TestObject';
        $path = tempnam(sys_get_temp_dir(), 'GeneratorTest');
        unlink($path);

        $gen = new Generator($path, 'TESTSPACE');

        $this->assertEquals(
            'ObjRef_Tests_Proxy_TestObject',
            $gen->getProxyName($fqcn)
        );
        $this->assertEquals(
            'ObjRef_Tests_Proxy_TestObject',
            $gen->getProxyName('\\'.$fqcn)
        );
        $this->assertEquals(
            'TESTSPACE\\ObjRef_Tests_Proxy_TestObject',
            $gen->getProxyNameFQCN($fqcn)
        );
        $proxyPath = $gen->getFullProxyPath($fqcn);
        $this->assertEquals(
            $path.'/ObjRef_Tests_Proxy_TestObject.php',
            $proxyPath
        );
        if(file_exists($proxyPath)) {
            unlink($proxyPath);
        }
        if(is_dir($path)) {
            rmdir($path);
        }
        $this->assertFileNotExists($proxyPath);
        $gen->generate($fqcn);
        $this->assertFileExists($proxyPath);
        $this->assertFileEquals(__DIR__ . '/Result/GeneratedProxy-php'.((int)phpversion()).'.php', $proxyPath);
    }
}
