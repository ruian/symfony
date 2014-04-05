<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDebug\Caster;

use Symfony\Component\VarDebug\Exception\ThrowingCasterException;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ExceptionCaster
{
    public static $traceArgs = true;
    public static $errorTypes = array(
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
    );

    public static function castException(\Exception $e, array $a)
    {
        $trace = $a["\0Exception\0trace"];
        unset($a["\0Exception\0trace"]); // Ensures the trace is always last

        static::filterTrace($trace, static::$traceArgs);

        if (isset($trace)) {
            $a["\0Exception\0trace"] = $trace;
        }
        if (empty($a["\0Exception\0previous"])) {
            unset($a["\0Exception\0previous"]);
        }
        unset($a["\0Exception\0string"], $a['xdebug_message'], $a['__destructorException']);

        return $a;
    }

    public static function castErrorException(\ErrorException $e, array $a)
    {
        if (isset($a[$s = "\0*\0severity"], self::$errorTypes[$a[$s]])) {
            $a[$s] = self::$errorTypes[$a[$s]];
        }

        return $a;
    }

    public static function castThrowingCasterException(ThrowingCasterException $e, array $a)
    {
        $b = (array) $a["\0Exception\0previous"];

        array_splice($b["\0Exception\0trace"], count($a["\0Exception\0trace"]));

        $t = static::$traceArgs;
        static::$traceArgs = false;
        $b = static::castException($a["\0Exception\0previous"], $b);
        static::$traceArgs = $t;

        empty($a["\0*\0message"]) and $a["\0*\0message"] = "Unexpected exception thrown from a caster: ".get_class($a["\0Exception\0previous"]);

        isset($b["\0*\0message"]) and $a["\0~\0message"] = $b["\0*\0message"];
        isset($b["\0*\0file"]) and $a["\0~\0file"] = $b["\0*\0file"];
        isset($b["\0*\0line"]) and $a["\0~\0line"] = $b["\0*\0line"];
        isset($b["\0Exception\0trace"]) and $a["\0~\0trace"] = $b["\0Exception\0trace"];

        unset($a["\0Exception\0trace"], $a["\0Exception\0previous"], $a["\0*\0code"], $a["\0*\0file"], $a["\0*\0line"]);

        return $a;
    }

    public static function filterTrace(&$trace, $dumpArgs, $offset = 0)
    {
        if (0 > $offset || empty($trace[$offset])) return $trace = null;

        $t = $trace[$offset];

        if (empty($t['class']) && isset($t['function'])) {
            if ('user_error' === $t['function'] || 'trigger_error' === $t['function']) {
                ++$offset;
            }
        }

        $offset and array_splice($trace, 0, $offset);

        foreach ($trace as &$t) {
            $t = array(
                'call' => (isset($t['class']) ? $t['class'].$t['type'] : '').$t['function'].'()',
                'file' => isset($t['line']) ? "{$t['file']}:{$t['line']}" : '',
                'args' => &$t['args'],
            );

            if (!isset($t['args']) || !$dumpArgs) {
                unset($t['args']);
            }
        }
    }
}
