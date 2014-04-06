<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDebug\Dumper;

use Symfony\Component\VarDebug\Collector\Data;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface DumperInterface
{
    public function dump(Data $data);
    public function dumpStart();
    public function dumpEnd();
    public function dumpScalar(Cursor $cursor, $type, $value);
    public function dumpString(Cursor $cursor, $str, $bin, $cut);
    public function enterArray(Cursor $cursor, $count, $indexed, $children, $cut);
    public function leaveArray(Cursor $cursor, $count, $indexed, $children, $cut);
    public function enterObject(Cursor $cursor, $class, $children, $cut);
    public function leaveObject(Cursor $cursor, $class, $children, $cut);
    public function enterResource(Cursor $cursor, $res, $children, $cut);
    public function leaveResource(Cursor $cursor, $res, $children, $cut);
}
