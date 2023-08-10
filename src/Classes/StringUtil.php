<?php

declare(strict_types=1);

/**
 * Contao Utilities for Contao Open Source CMS
 * Copyright (c) 2019-2020 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-utils
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/contao-utils/
 */

namespace WEM\UtilsBundle\Classes;

use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;

/**
 * String utilities.
 *
 * @author Web ex Machina <https://www.webexmachina.fr>
 */
class StringUtil extends \Contao\StringUtil
{
    /**
     * Generate a random token.
     *
     * @return string
     */
    public static function generateToken()
    {
        $objToken = new UriSafeTokenGenerator();

        return $objToken->generateToken();
    }

    /**
     * Generate a random password.
     *
     * @param int $length Optional password length
     *
     * @return string
     *
     * @todo Add pattern rules
     */
    public static function generatePassword($length = 8)
    {
        $characters = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789';
        $randstring = '';
        for ($i = 0; $i < $length; ++$i) {
            $randstring .= $characters[random_int(0, \strlen($characters) - 1)];
        }

        return $randstring;
    }

    /**
     * Generate a random code.
     *
     * @param int $length Optional code length
     *
     * @return string
     */
    public static function generateCode($length = 6)
    {
        $characters = '0123456789';
        $randstring = '';
        for ($i = 0; $i < $length; ++$i) {
            $randstring .= $characters[random_int(0, \strlen($characters) - 1)];
        }

        return $randstring;
    }

    /**
     * Convert a string value of keywords into Array.
     *
     * @param string $keywords      [keywords to format]
     * @param int    $charsRequired [exclude keywords with less than x chars]
     *
     * @return array
     */
    public static function formatKeywords($keywords, $charsRequired = 0)
    {
        $arrKeywords = [];
        $keywords = str_replace(['+', "'", ' '], ['-', '-', '-'], $keywords);
        $arrKeywords = explode('-', $keywords);

        foreach ($arrKeywords as $key => $keyword) {
            if ($charsRequired > 0 && \strlen($keyword) <= $charsRequired) {
                unset($arrKeywords[$key]);
            }
        }

        return array_values($arrKeywords);
    }
}
