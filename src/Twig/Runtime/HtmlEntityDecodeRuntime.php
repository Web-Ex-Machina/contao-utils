<?php

declare(strict_types=1);

namespace WEM\UtilsBundle\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

final class HtmlEntityDecodeRuntime implements RuntimeExtensionInterface
{
    /**
     * @internal
     */
    public function __construct()
    {
    }

    public function htmlEntityDecode(string $string): string
    {
        return html_entity_decode($string);
    }
}
