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
abstract class AbstractDumper
{
    public static $defaultOutputStream = 'php://output';

    protected $line = '';
    protected $lineDumper = array(__CLASS__, 'echoLine');
    protected $outputStream;

    public function __construct($outputStream = null)
    {
        if (is_callable($outputStream)) {
            $this->setLineDumper($outputStream);
        } else {
            isset($outputStream) or $outputStream =& static::$defaultOutputStream;
            is_string($outputStream) and $outputStream = fopen($outputStream, 'wb');
            $this->outputStream = $outputStream;
            $this->setLineDumper(array($this, 'echoLine'));
        }
    }

    public function setLineDumper($callback)
    {
        $prev = $this->lineDumper;
        $this->lineDumper = $callback;

        return $prev;
    }

    public function dump(Data $data, $lineDumper = null)
    {
        $dumper = clone $this;
        $lineDumper and $dumper->setLineDumper($lineDumper);
        $data->dump($dumper);
    }

    public function dumpStart()
    {
    }

    public function dumpEnd()
    {
        $this->dumpLine(false);
    }

    protected function dumpLine($depth)
    {
        call_user_func($this->lineDumper, $this->line, $depth);
        $this->line = '';
    }

    protected function echoLine($line, $depth)
    {
        if (false !== $depth) {
            fwrite($this->outputStream, str_repeat('  ', $depth).$line."\n");
        }
    }
}
