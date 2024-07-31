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

use phpseclib3\Crypt\Blowfish;

/**
 * Encryption class to encrypt and decrypt values using Blowfish cipher with CBC mode.
 */
class Encryption
{
    private ?string $encryptionKey;

    private const IV = "\0\0\0\0\0\0\0\0";


    /**
     * Constructor for the class.
     *
     * @param string $secret The encryption secret.
     * @param bool $truncateKey Whether to truncate the encryption key or not.
     */
    public function __construct(string $secret, bool $truncateKey)
    {
        $this->encryptionKey = $secret;
        if ($truncateKey) {
            $this->encryptionKey = substr($this->encryptionKey, 0, 56);
        }
    }


    /**
     * Encrypts a given value using Blowfish cipher with CBC mode.
     *
     * @param ?string $value The value to be encrypted.
     * @return string The encrypted value.
     */
    public function encrypt(?string $value): ?string
    {
        if ($value !== "") {

            $cipher = new Blowfish('cbc');
            $cipher->setKey($this->encryptionKey);
            $cipher->setIV(self::IV);

            return $cipher->encrypt($value);
        }
        return null;
    }

    /**
     * Decrypts a value using Blowfish encryption.
     *
     * @param ?string $value The encrypted value to decrypt.
     * @return string The decrypted value.
     */
    public function decrypt(?string $value): ?string
    {
        if ($value !== "") {

            $cipher = new Blowfish('cbc');
            $cipher->setKey($this->encryptionKey);
            $cipher->setIV(self::IV);

            return $cipher->decrypt($value);
        }
        return null;
    }

    /**
     * Encrypts the given data and returns it as a base64 encoded string.
     *
     * @param string $data The data to be encrypted.
     * @return string The encrypted data as a base64 encoded string.
     */
    public function encrypt_b64(string $data): string
    {
        $data_secured = $this->encrypt($data);
        return base64_encode($data_secured);
    }

    /**
     * Decrypts base64 encoded data.
     *
     * @param string $data64 The base64 encoded data.
     * @return string The decrypted data.
     */
    public function decrypt_b64(string $data64): string
    {
        $data = \base64_decode($data64);
        return $this->decrypt($data);
    }
}