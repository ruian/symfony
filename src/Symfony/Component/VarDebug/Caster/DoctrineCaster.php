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
class DoctrineCaster
{
    public static function castCommonProxy(\Doctrine\Common\Proxy\Proxy $p, array $a)
    {
        unset(
            $a['__cloner__'],
            $a['__initializer__'],
            $a['__isInitialized__']
        );

        return $a;
    }

    public static function castOrmProxy(\Doctrine\ORM\Proxy\Proxy $p, array $a)
    {
        $p = "\0".get_class($p)."\0";
        unset(
            $a[$p.'_entityPersister'],
            $a[$p.'_identifier'],
            $a['__isInitialized__']
        );

        return $a;
    }
}
