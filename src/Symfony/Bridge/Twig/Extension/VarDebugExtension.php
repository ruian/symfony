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

/**
 * Provides integration of the VarDebug component with Twig.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class VarDebugExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('debug', array($this, 'debug'), array('is_safe' => array('html'), 'needs_context' => true, 'needs_environment' => true)),
        );
    }

    public function getName()
    {
        return 'var_debug';
    }

    public function debug(\Twig_Environment $env, $context)
    {
        if (!$env->isDebug()) {
            return;
        }

        $count = func_num_args();
        if (2 === $count) {
            $vars = array();
            foreach ($context as $key => $value) {
                if (! $value instanceof \Twig_Template) {
                    $vars[$key] = $value;
                }
            }
        } elseif (3 === $count) {
            $vars = func_get_arg(2);
        } else{
            $vars = array_slice(func_get_args(), 2);
        }

        debug($vars);

        return '';
    }
}
