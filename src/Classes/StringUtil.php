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
     * Generate a password from words.
     *
     * @param int $length The number of words to generate.
     * @param string $separator The separator between words.
     * @param bool $withNumber Whether to include a random number with each word.
     * @return string The generated password.
     * @throws \Exception If an error occurs during the process.
     */
    public static function generatePasswordFromWords(int $length = 4, string $separator = '-', bool $withNumber = true): string
    {

        $password = '';
        $number = null;
        $transliterator = \Transliterator::createFromRules(':: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', \Transliterator::FORWARD);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://trouve-mot.fr/api/random/' . $length);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $headers[] = 'Accept: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch) !== 0) {
            return self::generatePassword();
        }

        curl_close($ch);

        try {
            $words = json_decode($result, true,512,JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            return self::generatePassword();
        }

        if($words == null){return self::generatePassword();}

        foreach ($words as $key => $word) {
            if ($withNumber){$number = random_int(0,99);}

            $word = $transliterator->transliterate($word['name']);
            $password .= ucfirst($word).$number;
            if($key !== array_key_last($words)){
                $password .= $separator;
            }
        }

        return $password;
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

}
