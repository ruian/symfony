<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDebug\Collector;

use Symfony\Component\VarDebug\Exception\ThrowingCasterException;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
abstract class AbstractCollector implements CollectorInterface
{
    public static $defaultCasters = array(
        'o:Closure'        => 'Symfony\Component\VarDebug\Caster\BaseCaster::castClosure',
        'o:Reflector'      => 'Symfony\Component\VarDebug\Caster\BaseCaster::castReflector',

        'o:ErrorException' => 'Symfony\Component\VarDebug\Caster\ExceptionCaster::castErrorException',
        'o:Exception'      => 'Symfony\Component\VarDebug\Caster\ExceptionCaster::castException',
        'o:Symfony\Component\VarDebug\Exception\ThrowingCasterException'
                           => 'Symfony\Component\VarDebug\Caster\ExceptionCaster::castThrowingCasterException',

        'r:dba'            => 'Symfony\Component\VarDebug\Caster\BaseCaster::castDba',
        'r:dba persistent' => 'Symfony\Component\VarDebug\Caster\BaseCaster::castDba',
        'r:gd'             => 'Symfony\Component\VarDebug\Caster\BaseCaster::castGd',
        'r:mysql link'     => 'Symfony\Component\VarDebug\Caster\BaseCaster::castMysqlLink',
        'r:process'        => 'Symfony\Component\VarDebug\Caster\BaseCaster::castProcess',
        'r:stream'         => 'Symfony\Component\VarDebug\Caster\BaseCaster::castStream',
    );

    protected $maxItems = 500;
    protected $maxString = 5000;

    private $casters = array();
    private $data = array(array(null));
    private $prevErrorHandler = null;


    public function __construct(array $defaultCasters = null)
    {
        isset($defaultCasters) or $defaultCasters = static::$defaultCasters;
        $this->addCasters($defaultCasters);
    }

    public function addCasters(array $casters)
    {
        foreach ($casters as $type => $callback) {
            $this->casters[strtolower($type)][] = $callback;
        }
    }

    public function setMaxItems($maxItems)
    {
        $this->maxItems = (int) $maxItems;
    }

    public function setMaxString($maxString)
    {
        $this->maxString = (int) $maxString;
    }

    public function collect($var)
    {
        $this->prevErrorHandler = set_error_handler(array($this, 'handleError'));
        try {
            $data = $this->doCollect($var);
        } catch (\Exception $e) {
            restore_error_handler();

            throw $e;
        }
        restore_error_handler();
        $this->prevErrorHandler = null;

        return new Data($data);
    }

    abstract protected function doCollect($var);

    protected function castObject($class, $obj)
    {
        $a = (array) $obj;

        $p = array($class => $class)
            + class_parents($obj)
            + class_implements($obj)
            + array('*' => '*');

        foreach (array_reverse($p) as $p) {
            if (!empty($this->casters[$p = 'o:'.strtolower($p)])) {
                foreach ($this->casters[$p] as $p) {
                    $a = $this->callCaster($p, $obj, $a);
                }
            }
        }

        return $a;
    }

    protected function castResource($type, $res)
    {
        $a = array();

        if (!empty($this->casters['r:'.$type])) {
            foreach ($this->casters['r:'.$type] as $c) {
                $a = $this->callCaster($c, $res, $a);
            }
        }

        return $a;
    }

    private function callCaster($callback, $obj, $a)
    {
        try {
            // Ignore invalid $callback
            $callback = @call_user_func($callback, $obj, $a);

            if (is_array($callback)) {
                $a = $callback;
            }
        } catch (\Exception $e) {
            $a["\0~\0⚠"] = new ThrowingCasterException($callback, $e);
        }

        return $a;
    }

    /**
     * @internal
     */
    public function handleError($type, $msg, $file, $line, $context)
    {
        if (E_RECOVERABLE_ERROR === $type || E_USER_ERROR === $type) {
            // Collector never dies
            throw new \ErrorException($msg, 0, $type, $file, $line);
        }

        if ($this->prevErrorHandler) {
            return call_user_func_array($this->prevErrorHandler, array($type, $msg, $file, $line, $context));
        }

        return false;
    }
}
