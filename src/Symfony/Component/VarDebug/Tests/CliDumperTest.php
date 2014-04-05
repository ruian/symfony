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
use Symfony\Component\VarDebug\Dumper\CliDumper;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class CliDumperTest extends \PHPUnit_Framework_TestCase
{
    function testGet()
    {
        require __DIR__.'/Fixtures/dum-var.php';

        $dumper = new CliDumper('php://output');
        $dumper->setColors(false);
        $collector = new PhpCollector();
        $data = $collector->collect($var);

        ob_start();
        $dumper->dump($data);
        $out = ob_get_clean();

        $this->assertSame(
            <<<EOTXT
[
  number: 1
  0: 1.1 #1
  const: null
  1: true
  2: false
  3: NAN
  4: INF
  5: -INF
  6: 9223372036854775807
  str: déjà
  7:bé
  []: []
  res: resource:stream{
    wrapper_type: plainfile
    stream_type: dir
    mode: r
    unread_bytes: 0
    seekable: true
    timed_out: false
    blocked: true
    eof: false
  }
  8: resource:Unknown{}
  obj: stdClass{} #2
  closure: Closure{
    reflection: 
      Closure [ <user> public method {closure} ] {
        @@ {$var['file']} {$var['line']} - {$var['line']}
      
        - Parameters [2] {
          Parameter #0 [ <required> \$a ]
          Parameter #1 [ <optional> PDO or NULL &\$b = NULL ]
        }
      }
      
  }
  line: {$var['line']}
  nobj: [
    stdClass{} #3
  ]
  recurs: [ #4
    [&4]
  ]
  9: &11.1
  sobj: stdClass{@2}
  snobj: stdClass{&3}
  snobj2: stdClass{@3}
  file: /home/nicolas.grekas/Clones/symfony/src/Symfony/Component/VarDebug/Tests/Fixtures/dum-var.php
]

EOTXT
            ,

            $out
        );
    }
}
