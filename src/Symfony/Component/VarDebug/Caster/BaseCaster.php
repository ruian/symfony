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

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class BaseCaster
{
    public static function castReflector(\Reflector $c, array $a)
    {
        $a["\0~\0reflection"] = $c->__toString();

        return $a;
    }

    public static function castClosure(\Closure $c, array $a)
    {
        $a = static::castReflector(new \ReflectionFunction($c), $a);
        unset($a[0], $a['name']);

        return $a;
    }

    public static function castDba($dba, array $a)
    {
        $list = dba_list();
        $a['file'] = $list[substr((string) $dba, 13)];

        return $a;
    }

    public static function castProcess($process, array $a)
    {
        return proc_get_status($process);
    }

    public static function castStream($stream, array $a)
    {
        return stream_get_meta_data($stream);
    }

    public static function castGd($gd, array $a)
    {
        $a['size'] = imagesx($gd).'x'.imagesy($gd);
        $a['trueColor'] = imageistruecolor($gd);

        return $a;
    }

    public static function castMysqlLink($h, array $a)
    {
        $a['host'] = mysql_get_host_info($h);
        $a['protocol'] = mysql_get_proto_info($h);
        $a['server'] = mysql_get_server_info($h);

        return $a;
    }
}
