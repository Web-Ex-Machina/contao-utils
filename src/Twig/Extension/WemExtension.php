<?php

declare(strict_types=1);

/**
 * Contao Utilities for Contao Open Source CMS
 * Copyright (c) 2019-2023 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-utils
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/contao-utils/
 */

namespace WEM\UtilsBundle\Twig\Extension;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Twig\Global\ContaoVariable;
use Contao\CoreBundle\Twig\Loader\ContaoFilesystemLoader;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use WEM\UtilsBundle\Twig\Runtime\PregReplaceRuntime;

/**
 * @experimental
 */
final class WemExtension extends AbstractExtension
{
    public function __construct(
        private readonly Environment $environment,
        private readonly ContaoFilesystemLoader $filesystemLoader,
        ContaoCsrfTokenManager $tokenManager,
        private readonly ContaoVariable $contaoVariable,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'preg_replace',
                [PregReplaceRuntime::class, 'pregReplace'],
                ['is_safe' => ['html']],
            ),
        ];
    }
}
