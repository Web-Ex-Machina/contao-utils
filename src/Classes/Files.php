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

use Exception;
use Contao\Config;
use Contao\File;
use Contao\Input;
use Contao\System;
use InvalidArgumentException;

class Files
{
    /**
     * Function to call in order to process files sent by DropZone
     *
     * @param string $folder Folder path of uploaded files
     * @return array<mixed>
     */
    public static function processDzFileUploads(string $folder): ?array
    {
        if ($_FILES === []) {
            return null;
        }

        $strStatus = 'success';
        $arrFiles = [];
        $arrErrors = [];

        foreach($_FILES as $f) {
            try {
                $arrFiles[] = static::processDzFileUpload($f, $folder);
            } catch (Exception $e) {
                $strStatus = 'error';
                $arrErrors[] = [
                    'file' => $f,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'status' => $strStatus,
            'files' => $arrFiles,
            'errors' => $arrErrors,
        ];
    }

    /**
     * Function to process one DropZone file
     *
     * @param array<mixed> $file Tmp File from PHP
     * @param string $folder Folder path of uploaded file
     * @return array<mixed>
     *
     * @throws Exception
     */
    public static function processDzFileUpload(array $file, string $folder): array
    {
        $folder = self::addLastSlashToPathIfNeeded($folder);

        $data = file_get_contents($file['tmp_name']);
        $path = $folder . $file['name'];

        // Detect if the upload is chunked or not
        // Post is empty if the file is not sent by chunked
        // if (!empty($_POST)) {
        if (Input::post('dzuuid')) {
            // Start a session to share upload events between chunks
            $session = System::getContainer()->get('session');
            $session->set(sprintf('dzupload_%s_%s_%s', $_POST['dzuuid'], $_POST['dzchunkindex'], $_POST['dztotalchunkcount'] - 1), false);
            // $path = $folder . $_POST['dzuuid'] . '_' . $_POST['dzchunkindex'];
            $path = self::buildPathForDzUploadChunk($folder, $_POST['dzuuid'], (int) $_POST['dzchunkindex']);
        } else {
            $path = $folder . $file['name'];
        }

        // open the output file for writing
        $objFile = new File($path);
        $objFile->write($data);
        $objFile->close();

        $file['complete'] = false;

        // Each chunk file, after writing its tmp file, will check if the other uploads have been completed
        // if (!empty($_POST)) {
        if (Input::post('dzuuid')) {
            // Tell session we finished to upload this chunk
            $session->set(sprintf('dzupload_%s_%s_%s', $_POST['dzuuid'], $_POST['dzchunkindex'], $_POST['dztotalchunkcount'] - 1), true);

            $blnMerge = true;
            for ($i = 0; $i < $_POST['dztotalchunkcount']; ++$i) {
                if (!$session->get(sprintf('dzupload_%s_%s_%s', $_POST['dzuuid'], $i, $_POST['dztotalchunkcount'] - 1))) {
                    $blnMerge = false;
                    break;
                }
            }
            
            if ($blnMerge) {
                $path = $folder . $file['name'];
                $objMergedFile = new File($path);
                $objMergedFile->truncate();

                for ($i = 0; $i < $_POST['dztotalchunkcount']; ++$i) {
                    $objTmpFile = new File(self::buildPathForDzUploadChunk($folder, $_POST['dzuuid'], $i));
                    $objMergedFile->append($objTmpFile->getContent(), '');
                    $objTmpFile->delete();
                }

                $file['complete'] = true;
                $objMergedFile->close();
            }
        }else{
            $file['complete'] = true;
        }

        // Add final path to returned array
        $file['path'] = $path;

        return $file;
    }

    /**
     * Cancel a DropZone file upload
     *
     * @param string $folder The folder in wich the upload file/chunks are
     * @param string $dzuuid The upload's UUID
     * @param int $dztotalchunkcount The total amount of chunks
     *
     * @throws Exception
     */
    public static function cancelDzFileUpload(string $folder, string $dzuuid, int $dztotalchunkcount): void
    {
        for ($i = 0; $i < $dztotalchunkcount; ++$i) {
            $objTmpFile = new File(self::buildPathForDzUploadChunk($folder,$dzuuid, $i));
            if($objTmpFile->exists()){
                $objTmpFile->delete();
            }
        }
    }

    /**
     * Contao Friendly Base64 Converter to FileSystem.
     *
     * @param string $data Base64 file
     * @param string $folder folder name
     * @param string $file file name
     * @param string $type file type (extensions)
     *
     * @throws Exception
     */
    public static function base64ToImage(string $data, string $folder, string $file, string $type = ''): File
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif
            $data = str_replace(' ', '+', $data);
        }

        if (!$type || !\in_array($type, explode(",", Config::get('validImageTypes')))) {
            throw new \Exception('No valid type found');
        }

        $data = base64_decode($data);

        if ($data === '' || $data === '0') {
            throw new Exception('base64_decode failed');
        }

        $path = $folder.'/'.$file.'.'.$type;

        // open the output file for writing
        $objFile = new File($path);
        $objFile->truncate();
        $objFile->write($data);
        $objFile->close();

        return $objFile;
    }

    /**
     * Contao Friendly Image Converter to Base64.
     *
     * @param  \Contao\FilesModel|\Contao\File $objFile  The file
     * @throws Exception
     */
    public static function imageToBase64($objFile): string
    {
        if(is_a($objFile,\Contao\FilesModel::class)){
            $objFile = new File($objFile->path);
        }elseif(!is_a($objFile,\Contao\File::class)){
            throw new InvalidArgumentException('$objFile must be an instance of \Contao\FilesModel or \Contao\File');
        }

        return sprintf(
            'data:image/%s;base64,%s',
            $objFile->extension,
            base64_encode($objFile->getContent())
        );
    }

    /**
     * Add last slash to a path if needed
     *
     */
    public static function addLastSlashToPathIfNeeded(string $path): string
    {
        if ('/' !== substr($path, -1, 1)) {
            $path .= '/';
        }

        return $path;
    }

    /**
     * Build path for a DZ upload's chunk
     * 
     * @param  string $folder  The folder path
     * @param  string $dzuuid  The DZ upload's UUID
     * @param  int    $chunkNo The DZ upload's chunk no
     * 
     * @return string The built path
     */
    public static function buildPathForDzUploadChunk(string $folder, string $dzuuid, int $chunkNo): string
    {
        return self::addLastSlashToPathIfNeeded($folder).$dzuuid.'_'.$chunkNo;
    }

    /**
     * Check if a file is displaybale in browser.
     *
     * @param File $objFile The file to check
     */
    public static function isDisplayableInBrowser(File $objFile): bool
    {
        $mime = strtolower($objFile->mime);
        return 'image/' === substr($mime, 0, 6) || 'application/pdf' === $mime;
    }
}
