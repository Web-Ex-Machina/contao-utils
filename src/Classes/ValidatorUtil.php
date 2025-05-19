<?php

declare(strict_types=1);

/**
 * Contao Utilities for Contao Open Source CMS
 * Copyright (c) 2019-2025 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-utils
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/contao-utils/
 */

namespace WEM\UtilsBundle\Classes;

class ValidatorUtil extends \Contao\Validator
{
    /**
     * Check if a string is serialized
     */
    public static function isSerialized(string $value): string
    {
       return preg_match('^([adObis]:|N;)^', $value);
    }
}
