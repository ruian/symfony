<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDebug\Tests;

use Symfony\Component\VarDebug\Collector\PhpCollector;
use Symfony\Component\VarDebug\Dumper\JsonDumper;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class JsonDumperTest extends \PHPUnit_Framework_TestCase
{
    function testGet()
    {
        require __DIR__.'/Fixtures/dum-var.php';

        $dumper = new JsonDumper();
        $collector = new PhpCollector();
        $data = $collector->collect($var);

        $var['file'] = str_replace('\\', '\\\\', $var['file']);

        $json = array();
        $dumper->dump($data, function ($line, $depth) use (&$json) {
            $json[] = str_repeat('  ', $depth).$line;
        });
        $json = implode("\n", $json);

        $this->assertSame(
'{"_":"1:array:24",
  "number": 1,
  "n`0": 1.1,
  "const": null,
  "n`1": true,
  "n`2": false,
  "n`3": "n`NAN",
  "n`4": "n`INF",
  "n`5": "n`-INF",
  "n`6": "n`'.PHP_INT_MAX.'",
  "str": "déjà",
  "n`7": "b`é",
  "[]": [],
  "res": {"_":"14:resource:stream",
    "wrapper_type": "plainfile",
    "stream_type": "dir",
    "mode": "r",
    "unread_bytes": 0,
    "seekable": true,
    "timed_out": false,
    "blocked": true,
    "eof": false
  },
  "n`8": {"_":"23:resource:Unknown"},
  "obj": {"_":"24:stdClass"},
  "closure": {"_":"25:Closure",
    "~:reflection": "Closure [ <user> public method {closure} ] {\n  @@ '.$var['file'].' '.$var['line'].' - '.$var['line'].'\n\n  - Parameters [2] {\n    Parameter #0 [ <required> $a ]\n    Parameter #1 [ <optional> PDO or NULL &$b = NULL ]\n  }\n}\n"
  },
  "line": '.$var['line'].',
  "nobj": [
    {"_":"29:stdClass"}
  ],
  "recurs": [
    "R`31:30"
  ],
  "n`9": "R`32:3",
  "sobj": "r`33:24",
  "snobj": "R`34:29",
  "snobj2": "r`35:29",
  "file": "'.$var['file'].'",
  "__refs": {"30":[31],"3":[32],"24":[-33],"29":[34,-35]}
}
',
            $json
        );
    }
}
