<?php
declare(strict_types=1);
/*
 * This file is part of the BrandOriented package.
 *
 * (c) Brand Oriented sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Dominik Labudzinski <dominik@labudzinski.com>
 * @name Chacha20poly1305.php - 22-05-2018 10:03
 */

namespace BrandOriented\Encryption\Encryptor;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Class Chacha20poly1305
 * @package BrandOriented
 */
class Chacha20poly1305 implements EncryptorInterface
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $suffix;

    public function __construct(string $key, string $suffix)
    {
        $this->key = $key;
        $this->suffix = $suffix;
    }

    const HASH_ALGORITHM = 'sha512';
    /**
     * Encrypts the given data and returns an encrypted version of it
     * @param string $data
     * @return string
     */
    public function encrypt(string $data): string
    {
        $key = hash(self::HASH_ALGORITHM, $this->key);
        $aad = hash(self::HASH_ALGORITHM, hash('whirlpool', $this->key));

        $nonce = substr($key, 0, \Sodium\CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES);
        $key = substr($key, 0, \Sodium\CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES);
        $raw = \Sodium\crypto_aead_chacha20poly1305_encrypt(
            $data,
            $aad,
            $nonce,
            $key
        );

        $encrypted = base64_encode($raw);

        /**
         * Clear memory for variables
         */
        \Sodium\memzero($data);
        \Sodium\memzero($raw);
        \Sodium\memzero($key);
        \Sodium\memzero($nonce);
        \Sodium\memzero($aad);

        return $encrypted;
    }

    /**
     * Takes the encrypted data and returns the data decrypted
     *
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        $key = hash(self::HASH_ALGORITHM, $this->key);
        $aad = hash(self::HASH_ALGORITHM, hash('whirlpool', $this->key));

        $encrypted = base64_decode($data);

        $nonce = substr($key, 0, \Sodium\CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES);
        $key = substr($key, 0, \Sodium\CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES);
        $decrypted = \Sodium\crypto_aead_chacha20poly1305_decrypt(
            $encrypted,
            $aad,
            $nonce,
            $key
        );

        /**
         * Clear memory for variables
         */
        \Sodium\memzero($encrypted);
        \Sodium\memzero($key);
        \Sodium\memzero($nonce);
        \Sodium\memzero($aad);
        \Sodium\memzero($data);

        if ($decrypted === false) {
            /**
             * Clear memory for variables
             */

            throw new BadCredentialsException("Bad ciphertext");
        }

        return $decrypted;
    }

    /**
     * Adds a suffix to the encryptor
     *
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }
}