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
