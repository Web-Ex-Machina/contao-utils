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

namespace WEM\UtilsBundle\Classes;

use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;

class StringUtil extends \Contao\StringUtil
{
    /**
     * Generate a random token.
     */
    public static function generateToken(): string
    {
        $objToken = new UriSafeTokenGenerator();

        return $objToken->generateToken();
    }

    /**
     * Generate a random password.
     *
     * @param int $length Optional password length
     *
     * @todo Add pattern rules
     */
    public static function generatePassword(int $length = 8): string
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
     */
    public static function generateCode(int $length = 6): string
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
     * @param int $charsRequired [exclude keywords with less than x chars]
     */
    public static function formatKeywords(string $keywords, int $charsRequired = 0): array
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

    /**
     * Convert a number of bits to a readable filesize format
     *
     */
    public static function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes .= ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes .= ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * Clean the tinyMCE data, see rules below
     * Rule #1 : Replace [nbsp] tags by ' '
     * Rule #2 : Find special characters and add an [nbsp] just before.
     *
     * @param string $varValue [Value to clean]
     */
    public static function cleanSpaces(string $varValue): string
    {
        // Rule #1
        $varValue = str_replace(['[nbsp]', '&nbsp;'], [' ', ' '], $varValue);

        // Rule #2
        $varValue = preg_replace("/\s(\?|\!|\:|\;|\»)/", '&nbsp;\\1', $varValue);

        return preg_replace("/(\«)\s/", '\\1&nbsp;', $varValue);
    }

    /**
     * Generates a random key of specified length.
     *
     * @param int|null $length The length of the key (default is 16)
     * @return string The random key generated
     * @throws \Exception
     */
    public static function generateKey(?int $length = 16): string
    {
        $characters = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789_-';
        $randstring = '';
        for ($i = 0; $i < $length; ++$i) {
            $randstring .= $characters[random_int(0, \strlen($characters) - 1)];
        }

        return $randstring;
    }

    public static function getFormStorageDataValueAsString($mixed): string
    {
        $value = self::deserialize($mixed);
        if (\is_array($value)) {
            $formattedValue = [];
            foreach ($value as $valueChunk) {
                $formattedValue[] = sprintf('%s (%s)', $valueChunk['label'], $valueChunk['value']);
            }
            $formattedValue = implode(',', $formattedValue);
        } else {
            $formattedValue = (string) $value;
        }

        return $formattedValue;
    }
}
