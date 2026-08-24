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

use Contao\File as ContaoFile;
use Contao\Files as ContaoFiles;

class PdfUtil
{
    /**
     * Extract a page from a PDF and return the generated picture path
     * 
     * @param string $iPath - The input file path
     * @param string|null $oPath - Where to put the extracted page (if null, picture will be generated in the same folder as PDF)
     * @param int $page - PDF page to extract
     * @param int $antialiasing - Ghostscript setting
     * @param int $resolution - Ghostscript/Imagick setting
     * @param bool $useGhostScript - Set to true to use Ghostscript, fallback is Imagick
     * 
     * @return string|null - either the path of the generated picture either null
     */ 
    public static function getPageAsJpeg(
        string $iPath,
        ?string $oPath = null,
        int $page = 1,
        int $antialiasing = 4,
        int $resolution = 300,
        bool $useGhostScript = true,
        bool $eraseIfExists = false,
    ): string|null 
    {
        $iExt = Files::getExtensionFromFilename($iPath);

        // Stop if file is not a PDF
        if ('pdf' !== $iExt) {
            return null;
        }

        // Prepare vars
        $oFormat = "jpeg";

        // Prepare output
        if (null === $oPath) {
            $oPath = str_replace("." . $iExt, "." . $oFormat, $iPath);
        } else {
            $folder = str_replace(basename($oPath), "", $oPath);

            // Make sure wanted folder exists
            if (!ContaoFiles::getInstance()->mkdir($folder)) {
                return null;
            }

            // Make sure output file has the right extension
            $oExt = Files::getExtensionFromFilename($oPath);

            if ($oExt !== $oFormat) {
                $oPath = str_replace("." . $oExt, "." . $oFormat, $oPath);
            }
        }

        // Make path absolutes
        $iPath = Files::getAbsolutePath($iPath);
        $oPath = Files::getAbsolutePath($oPath);

        // Check if output file exists
        if (!$eraseIfExists && Files::exists($oPath)) {
            return $oPath;
        }

        // Ghostscript
        \system( "which gs > /dev/null", $retval);
        if ($useGhostScript && 0 === (int) $retval) {
            $exec_command  = "gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=" . $oFormat . " ";
            $exec_command .= "-dTextAlphaBits=". $antialiasing . " -dGraphicsAlphaBits=" . $antialiasing . " ";
            $exec_command .= "-dFirstPage=" . $page . " -dLastPage=" . $page . " ";
            $exec_command .= "-r" . $resolution . " ";
            $exec_command .= "-sOutputFile=" . $oPath . " '" . $iPath . "'";

            exec($exec_command, $co, $rv);

            if (!$rv) {
                return $oPath;
            }
            
            return null;
        }

        // Else use Imagick
        $im = new \Imagick();
        $im->setResolution($resolution, $resolution);
        $im->readImage($iPath . '[' . $page - 1 . ']');
        $im = $im->flattenImages();
        $im->setImageFormat($oFormat);
        $im->thumbnailImage($resolution, 0);

        $objFile = new ContaoFile(Files::getRelativePath($oPath));
        $objFile->write((string) $im);
        $objFile->close();

        return $oPath;
    }
}
