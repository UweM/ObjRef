<?php

/*
 * This file is part of the ObjRef package.
 *
 * (c) Uwe Mueller <uwe@namez.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ObjRef\Transport;

interface Transport {
    public function send($cmd);
    public function recv();
}
