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

use Contao\System;

class CountriesUtil
{

    /**
     *  @return array<mixed>
     */
    public static function getCountries(): ?array
    {
        $arrCountries = System::getContainer()->get('contao.intl.countries')->getCountries();

        return array_combine(array_map('strtolower', array_keys($arrCountries)), $arrCountries);
    }
}
