<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\Extension;

use Symfony\Bridge\Twig\TokenParser\DebugTokenParser;

/**
 * Provides integration of the VarDebug component with Twig.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class VarDebugExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(new DebugTokenParser());
    }

    public function getName()
    {
        return 'var_debug';
    }
}
