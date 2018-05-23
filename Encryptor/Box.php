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
 * @name Box.php - 22-05-2018 10:03
 */

namespace BrandOriented\Encryption\Encryptor;

/**
 * Class Box
 * @package BrandOriented
 */
class Box implements EncryptorInterface
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $suffix;
    /**
     * @var string
     */
    private $nonce;

    /**
     * Box constructor.
     * @param string $key
     * @param string $nonce
     * @param string $suffix
     */
    public function __construct(string $key, string $nonce, string $suffix)
    {
        $this->key = mb_substr($key, 0, \Sodium\CRYPTO_SECRETBOX_KEYBYTES);
        $this->suffix = $suffix;
        $this->nonce = mb_substr($nonce, 0, \Sodium\CRYPTO_SECRETBOX_NONCEBYTES);
    }

    const HASH_ALGORITHM = 'sha512';

    /**
     * Encrypts the given data and returns an encrypted version of it
     * @param string $data
     * @param string|null $nonce
     * @return string
     */
    public function encrypt(string $data, string $nonce = null): string
    {
        if($nonce !== null) {
            $this->nonce = mb_substr($nonce.$this->nonce, 0, \Sodium\CRYPTO_SECRETBOX_NONCEBYTES);
        }

        return base64_encode(
            \Sodium\crypto_secretbox(
                $data,
                $this->nonce,
                $this->key
            )
        );
    }

    /**
     * Takes the encrypted data and returns the data decrypted
     *
     * @param string $data
     * @param string|null $nonce
     * @return string
     */
    public function decrypt(string $data, string $nonce = null): string
    {
        if($nonce !== null) {
            $this->nonce = mb_substr($nonce.$this->nonce, 0, \Sodium\CRYPTO_SECRETBOX_NONCEBYTES);
        }
        $encrypted = base64_decode($data);
        return \Sodium\crypto_secretbox_open(
            $encrypted,
            $this->nonce,
            $this->key
        );
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