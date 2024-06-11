<?php

declare(strict_types=1);

/**
 * Contao Utilities for Contao Open Source CMS
 * Copyright (c) 2019-2024 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-utils
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/contao-utils/
 */

namespace WEM\UtilsBundle\Classes;

use Contao\CoreBundle\Routing\ScopeMatcher as ScopeMatcherBase;
use Symfony\Component\HttpFoundation\RequestStack;

class ScopeMatcher
{
    private RequestStack $requestStack;

    private ScopeMatcherBase $scopeMatcher;

    /**
     * I use FQDN because PHP doesn't care about the "use".
     */
    public function __construct( RequestStack $requestStack, ScopeMatcherBase $scopeMatcher) {
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }

    /**
     * Returns whether the current request is a backend request.
     *
     * @return bool Returns true if the current request is a backend request, false otherwise.
     */
    public function isBackend(): bool
    {
        return ( $this->requestStack->getCurrentRequest() !== null ) ? $this->scopeMatcher->isBackendRequest($this->requestStack->getCurrentRequest()): false;
    }

    /**
     * Checks if the current request is a frontend request.
     *
     * @return bool Whether the current request is a frontend request, false otherwise.
     */
    public function isFrontend(): bool
    {
        return ( $this->requestStack->getCurrentRequest() !== null ) ? $this->scopeMatcher->isFrontendRequest($this->requestStack->getCurrentRequest()): false;
    }
}