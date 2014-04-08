<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\VarDebug\Collector\Data;
use Symfony\Component\VarDebug\Dumper\JsonDumper;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class VarDebugDataCollector extends DataCollector
{
    private $container;
    private $stopwatch;

    public function __construct(ContainerInterface $container, Stopwatch $stopwatch = null)
    {
        $this->container = $container;
        $this->stopwatch = $stopwatch;
        $this->data['dumps'] = array();
    }

    public function dump(Data $data)
    {
        $this->stopwatch and $this->stopwatch->start('debug');

        $trace = PHP_VERSION_ID >= 50306 ? DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS : true;
        if (PHP_VERSION_ID >= 50400) {
            $trace = debug_backtrace($trace, 5);
        } else {
            $trace = debug_backtrace($trace);
        }

        $file = false;
        $excerpt = false;

        if (isset($trace[4]['object']) && $trace[4]['object'] instanceof \Twig_Template) {
            $line = $trace[3]['line'];
            $trace = $trace[4]['object'];

            $name = $trace->getTemplateName();
            $src = $trace->getEnvironment()->getLoader()->getSource($name);
            $trace = $trace->getDebugInfo();
            $line = $trace[$line];

            $src = explode("\n", $src);
            $excerpt = array();

            for ($i = max($line - 3, 1), $max = min($line + 3, count($src)); $i <= $max; $i++) {
                $excerpt[] = '<li'.($i === $line ? ' class="selected"' : '').'><code>'.htmlspecialchars($src[$i - 1]).'</code></li>';
            }

            $excerpt = '<ol start="'.max($line - 3, 1).'">'.implode("\n", $excerpt).'</ol>';
        } else {
            if (isset($trace[2]['function']) && 'debug' === $trace[2]['function'] && empty($trace[2]['class'])) {
                $file = $trace[2]['file'];
                $line = $trace[2]['line'];
            } else {
                $file = $trace[0]['file'];
                $line = $trace[0]['line'];
            }

            $name = dirname($this->container->get('kernel')->getRootDir()).DIRECTORY_SEPARATOR;
            $name = strncasecmp($file, $name, strlen($name)) ? $file : substr($file, strlen($name));
        }

        $this->data['dumps'][] = compact('data', 'name', 'file', 'line', 'excerpt');

        $this->stopwatch and $this->stopwatch->stop('debug');
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    public function getDumps()
    {
        $dumper = new JsonDumper();
        $dumps = array();

        foreach ($this->data['dumps'] as $dump) {
            $json = '';
            $dumper->dump($dump['data'], function ($line) use (&$json) {$json .= $line;});
            $dump['json'] = $json;
            $dumps[] = $dump;
        }

        return $dumps;
    }

    public function getName()
    {
        return 'var_debug';
    }
}
