<?php

declare(strict_types=1);

namespace WEM\UtilsBundle\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

final class PregReplaceRuntime implements RuntimeExtensionInterface
{
    /**
     * @internal
     */
    public function __construct()
    {
    }

    public function pregReplace(string $html, $pattern, $replace): string
    {
        return preg_replace($pattern, $replace, $html);
    }
}
