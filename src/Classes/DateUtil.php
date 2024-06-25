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

class DateUtil
{
    /**
     * Converts a number of milliseconds into a human-readable duration.
     *
     * @param int $duration Number of milliseconds
     *
     * @return string The duration in 12m34s567ms
     */
    public static function humanReadableDuration(int $duration): string
    {
        $minutes = (int) ($duration / 60000);
        $duration = ($duration % 60000);
        $seconds = (int) ($duration / 1000);
        $duration = ($duration % 1000);
        $ms = $duration;

        return sprintf('%02dm%02ds%03dms', $minutes, $seconds, $ms);
    }
}
