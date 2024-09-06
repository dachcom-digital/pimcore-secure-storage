<?php

namespace SecureStorageBundle\Encrypter;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface EncrypterInterface
{
    public static function register(): void;

    public static function configureOptions(OptionsResolver $optionsResolver): void;

    public static function appendEncryption($stream, array $options): void;

    public static function appendDecryption($stream, array $options): void;

    public function onCreate(): bool;

    public function filter($in, $out, &$consumed, bool $closing): int;
}