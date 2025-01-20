<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace SecureStorageBundle\Encrypter;

use php_user_filter;
use SecureStorageBundle\Exception\FeedMeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpenSslEncrypter extends php_user_filter implements EncrypterInterface
{
    private const FILTERNAME_PREFIX = 'secure_storage_encryptor';
    private const MODE_ENCRYPT = '.encrypt';
    private const MODE_DECRYPT = '.decrypt';
    private const CHUNK_SIZE = 8192;
    private const SUPPORTED_CIPHERS = [
        'aes-128-cbc',
        'aes-256-cbc',
        'aes-128-gcm',
        'aes-256-gcm',
    ];
    private const OPENSSL_OPTIONS = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;

    private static bool $filterRegistered = false;
    private string $mode;
    private int $blockSize;
    private string $buffer;
    private ?string $iv;

    public static function register(): void
    {
        if (self::$filterRegistered) {
            return;
        }

        stream_filter_register(self::FILTERNAME_PREFIX . '.*', __CLASS__);

        self::$filterRegistered = true;
    }

    public static function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'cipher' => 'aes-128-cbc',
            'key'    => null
        ]);

        $optionsResolver->setRequired('key');
        $optionsResolver->setAllowedTypes('cipher', ['string']);
        $optionsResolver->setAllowedTypes('key', ['string']);
        $optionsResolver->setAllowedValues('cipher', self::SUPPORTED_CIPHERS);
    }

    public static function appendEncryption($stream, array $options): void
    {
        stream_filter_append(
            $stream,
            self::FILTERNAME_PREFIX . self::MODE_ENCRYPT,
            STREAM_FILTER_READ,
            $options
        );
    }

    public static function appendDecryption($stream, array $options): void
    {
        stream_filter_append(
            $stream,
            self::FILTERNAME_PREFIX . self::MODE_DECRYPT,
            STREAM_FILTER_READ,
            $options
        );
    }

    public function onCreate(): bool
    {
        $this->iv = null;
        $this->buffer = '';

        $length = openssl_cipher_iv_length($this->params['cipher']);

        $this->blockSize = $length;

        $this->mode = match ($this->filtername) {
            self::FILTERNAME_PREFIX . self::MODE_ENCRYPT => self::MODE_ENCRYPT,
            self::FILTERNAME_PREFIX . self::MODE_DECRYPT => self::MODE_DECRYPT,
            default                                      => ''
        };

        return true;
    }

    public function filter($in, $out, &$consumed, $closing): int
    {
        try {
            $this->handleIv($in, $out, $consumed);
        } catch (FeedMeException) {
            return PSFS_FEED_ME;
        }

        while ($bucket = stream_bucket_make_writeable($in)) {
            $this->buffer .= $bucket->data;
            $consumed += strlen($bucket->data);

            while (strlen($this->buffer) >= self::CHUNK_SIZE) {
                $chunk = substr($this->buffer, 0, self::CHUNK_SIZE - (self::CHUNK_SIZE % $this->blockSize)); // align chunk to block size
                $this->buffer = substr($this->buffer, self::CHUNK_SIZE - (self::CHUNK_SIZE % $this->blockSize)); // keep remainder in buffer

                $processed = match ($this->mode) {
                    self::MODE_ENCRYPT => $this->encryptChunkData($chunk),
                    self::MODE_DECRYPT => $this->decryptChunkData($chunk),
                    default            => false
                };

                if ($processed === false) {
                    throw new \Exception(sprintf('[%s] Error: %s', $this->mode, openssl_error_string()));
                }

                $newBucket = stream_bucket_new($this->stream, $processed);
                stream_bucket_append($out, $newBucket);
            }
        }

        if (!$closing) {
            return PSFS_PASS_ON;
        }

        if ($this->buffer === '') {
            return PSFS_PASS_ON;
        }

        $processed = match ($this->mode) {
            self::MODE_ENCRYPT => $this->encryptClosingData(),
            self::MODE_DECRYPT => $this->decryptClosingData(),
            default            => false
        };

        if ($processed === false) {
            throw new \Exception(openssl_error_string());
        }

        $newBucket = stream_bucket_new($this->stream, $processed);
        stream_bucket_append($out, $newBucket);

        $this->buffer = '';

        return PSFS_PASS_ON;
    }

    private function encryptChunkData($chunk): mixed
    {
        return openssl_encrypt($chunk, $this->params['cipher'], $this->params['key'], self::OPENSSL_OPTIONS, $this->iv);
    }

    private function encryptClosingData(): mixed
    {
        $padLength = $this->blockSize - strlen($this->buffer) % $this->blockSize;
        $this->buffer .= str_repeat(chr($padLength), $padLength);

        return openssl_encrypt($this->buffer, $this->params['cipher'], $this->params['key'], self::OPENSSL_OPTIONS, $this->iv);
    }

    private function decryptChunkData($chunk): mixed
    {
        return openssl_decrypt($chunk, $this->params['cipher'], $this->params['key'], self::OPENSSL_OPTIONS, $this->iv);
    }

    private function decryptClosingData(): mixed
    {
        $processed = openssl_decrypt($this->buffer, $this->params['cipher'], $this->params['key'], self::OPENSSL_OPTIONS, $this->iv);

        if ($processed !== false) {
            // Remove PKCS7 padding during decryption
            $padLength = ord(substr($processed, -1));
            if ($padLength > 0 && $padLength <= $this->blockSize) {
                $processed = substr($processed, 0, -$padLength);
            }
        }

        return $processed;
    }

    private function handleIv($in, $out, ?int &$consumed): void
    {
        if ($this->iv !== null) {
            return;
        }

        if ($this->mode === self::MODE_ENCRYPT) {
            $this->iv = random_bytes($this->blockSize);
            $ivBucket = stream_bucket_new($this->stream, $this->iv);
            stream_bucket_append($out, $ivBucket);

            return;
        }

        // Handle IV for decryption: extract it from the first block of data
        if ($this->mode === self::MODE_DECRYPT) {
            $bucket = stream_bucket_make_writeable($in);

            if ($bucket !== null) {
                $this->buffer .= $bucket->data;
                $consumed += strlen($bucket->data);

                // If we don't have enough data for the IV, continue accumulating
                if (strlen($this->buffer) < $this->blockSize) {
                    throw new FeedMeException();
                }

                // Extract the IV from the first block of data
                $this->iv = substr($this->buffer, 0, $this->blockSize);
                $this->buffer = substr($this->buffer, $this->blockSize); // Remove IV from buffer
            }

            return;
        }

        throw new \Exception(sprintf('Unknown mode %s"', $this->mode));
    }
}
