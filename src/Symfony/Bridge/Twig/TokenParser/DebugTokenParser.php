<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\TokenParser;

use Symfony\Bridge\Twig\Node\DebugNode;

/**
 * Token Parser for the 'debug' tag.
 *
 * @author Julien Galenski <julien.galenski@gmail.com>
 *
 * Debug all variables in context or one given
 *
 * <pre>
 *  {% debug %}
 *
 *  {% debug foo %}
 *  {% debug foo, bar %}
 * </pre>
 */
class DebugTokenParser extends \Twig_TokenParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(\Twig_Token $token)
    {
        $values = null;
        if (!$this->parser->getStream()->test(\Twig_Token::BLOCK_END_TYPE)) {
            $values = $this->parser->getExpressionParser()->parseMultitargetExpression();
        }
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new DebugNode($this->parser->getEnvironment(), $values, $token->getLine(), $this->getTag());
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return 'debug';
    }
}
