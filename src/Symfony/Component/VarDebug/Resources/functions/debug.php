<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\VarDebug\Collector\ExtCollector;
use Symfony\Component\VarDebug\Collector\PhpCollector;
use Symfony\Component\VarDebug\Dumper\CliDumper;

if (!function_exists('debug')) {

    /**
     * @author Nicolas Grekas <p@tchwork.com>
     */
    function debug($var)
    {
        static $reflector;

        if (!isset($reflector)) {
            $reflector = new ReflectionFunction('set_debug_handler');
        }

        $h = $reflector->getStaticVariables();

        if (!isset($h['handler'])) {
            if (class_exists('Symfony\Component\VarDebug\Dumper\CliDumper')) {
                $collector = extension_loaded('symfony_debug') ? new ExtCollector : new PhpCollector;
                $dumper = new CliDumper;
                $h['handler'] = function ($var) use ($collector, $dumper) {
                    $dumper->dump($collector->collect($var));
                };
            } else {
                $h['handler'] = 'var_dump';
            }
            set_debug_handler($h['handler']);
        }

        return $h['handler']($var);
    }

    function set_debug_handler($callable)
    {
        static $handler = null;

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Invalid PHP callback.');
        }

        $h = $handler;
        $handler = $callable;

        return $h;
    }
}
