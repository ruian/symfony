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
class SplCaster
{
    public static function castIterator(\Iterator $c, array $a)
    {
        $a = array_merge($a, iterator_to_array($c));

        return $a;
    }

    public static function castSplDoublyLinkedList(\SplDoublyLinkedList $c, array $a)
    {
        $mode = $c->getIteratorMode();
        $c->setIteratorMode(\SplDoublyLinkedList::IT_MODE_KEEP | $mode & ~\SplDoublyLinkedList::IT_MODE_DELETE);
        $a = array_merge($a, iterator_to_array($c));
        $c->setIteratorMode($mode);

        return $a;
    }

    public static function castSplFixedArray(\SplFixedArray $c, array $a)
    {
        $a = array_merge($a, $c->toArray());

        return $a;
    }

    public static function castSplObjectStorage(\SplObjectStorage $c, array $a)
    {
        foreach ($c as $k => $obj) {
            $a[$k] = $obj;
            if (null !== $i = $c->getInfo()) {
                $a["\0~\0$k"] = $i;
            }
        }

        return $a;
    }
}
