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

class Files
{
    /**
     * Contao Friendly Base64 Converter to FileSystem.
     *
     * @param [String] $data   [Base64]
     * @param [String] $folder [Folder name]
     * @param [String] $file   [File name]
     * @param [String] $type   [File type]
     *
     * @return [Object] [File Object]
     */
    public static function base64ToImage($data, $folder, $file, $type = '')
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif
            $data = str_replace(' ', '+', $data);
        }

        if (!$type || !\in_array($type, \Config::get('validImageTypes'))) {
            throw new \Exception('No valid type found');
        }

        $data = base64_decode($data);

        if (false === $data) {
            throw new \Exception('base64_decode failed');
        }

        $path = $folder.'/'.$file.'.'.$type;

        // open the output file for writing
        $objFile = new \File($path);
        $objFile->truncate();
        $objFile->write($data);
        $objFile->close();

        return $objFile;
    }

    /**
     * Contao Friendly Image Converter to Base64.
     *
     * @param \Contao\FilesModel $objFile
     *
     * @return string
     */
    public static function imageToBase64($objFile)
    {
        $objFile = new \File($objFile->path);

        return sprintf(
            'data:image/%s;base64,%s',
            $objFile->extension,
            base64_encode($objFile->getContent())
        );
    }
}
