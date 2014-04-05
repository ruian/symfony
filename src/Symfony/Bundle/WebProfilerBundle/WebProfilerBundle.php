<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\WebProfilerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class WebProfilerBundle extends Bundle
{
    public function boot()
    {
        $container = $this->container;

        set_debug_handler(function ($var) use ($container) {
            $data = $container->get('var_debug.collector')->collect($var);
            $container->get('data_collector.var_debug')->dump($data);
        });
    }
}
