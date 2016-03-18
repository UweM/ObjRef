<?php
namespace ObjRef;

abstract class C {
    const CMD = 0;
    const REF = 1;
    const NAME = 2;
    const ARGS = 3;
    const CALL = 4;
    const GET = 5;
    const SET = 6;
    const CISSET = 7;
    const CUNSET = 8;
    const CRETURN = 9;
    const EXCEPTION = 10;
    const SCALAR = 11;
    const OBJECT = 12;
    const CLASSNAME = 13;
    const GET_INIT_CLASSNAME = 14;

    /**
     * string literals for connection debugging
     */
    /*
    const CMD = 'cmd';
    const REF = 'ref';
    const NAME = 'name';
    const ARGS = 'args';
    const CALL = 'call';
    const GET = 'get';
    const SET = 'set';
    const CISSET = 'isset';
    const CUNSET = 'unset';
    const CRETURN = 'return';
    const EXCEPTION = 'exception';
    const SCALAR = 'scalar';
    const OBJECT = 'obj';
    const CLASSNAME = 'classname';
    const GET_INIT_CLASSNAME = 'get_init_class';
    */
}
