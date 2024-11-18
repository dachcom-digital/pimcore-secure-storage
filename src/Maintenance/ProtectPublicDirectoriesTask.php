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

namespace SecureStorageBundle\Maintenance;

use League\Flysystem\FilesystemOperator;
use Pimcore\Maintenance\TaskInterface;
use Psr\Container\ContainerInterface;

class ProtectPublicDirectoriesTask implements TaskInterface
{
    public function __construct(
        protected array $secureStorageConfig,
        protected ContainerInterface $locator
    ) {
    }

    public function execute(): void
    {
        $assetProtectionConfig = $this->secureStorageConfig['pimcore_asset_protection']['htaccess_protection_public_directories'];

        if (count($assetProtectionConfig['paths']) === 0) {
            return;
        }

        foreach ($assetProtectionConfig['paths'] as $protectedPath) {
            $this->protectPath($protectedPath);
        }
    }

    private function protectPath(string $protectedPath): void
    {
        $data = implode(PHP_EOL, $this->getProtectionLines());

        $storages = [
            'pimcore.asset.storage',
            'pimcore.asset_cache.storage',
            'pimcore.thumbnail.storage'
        ];

        foreach ($storages as $storageName) {
            $storage = $this->getStorage($storageName);

            if ($protectedPath === '/') {
                $secureFilePath = '.htaccess';
                if (!$storage->fileExists($secureFilePath)) {
                    $storage->write($secureFilePath, $data, ['bypass_secured_adapter' => true]);
                }

                continue;
            }

            $secureFilePath = sprintf('%s/.htaccess', $protectedPath);
            if ($storage->directoryExists($protectedPath) && !$storage->fileExists($secureFilePath)) {
                $storage->write($secureFilePath, $data, ['bypass_secured_adapter' => true]);
            }
        }
    }

    private function getStorage(string $name): FilesystemOperator
    {
        return $this->locator->get($name);
    }

    private function getProtectionLines(): array
    {
        $data = [];

        $data[] = 'RewriteEngine On';
        $data[] = 'RewriteCond %{HTTP_HOST}==%{HTTP_REFERER} !^(.*?)==https?://\1/admin/ [OR]';
        $data[] = 'RewriteCond %{HTTP_COOKIE} !^.*pimcore_admin_sid.*$ [NC]';
        $data[] = 'RewriteRule ^ - [L,F]';

        return $data;
    }
}
