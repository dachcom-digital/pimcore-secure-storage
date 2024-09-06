<?php

namespace SecureStorageBundle;

use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use SecureStorageBundle\DependencyInjection\CompilerPass\FlysystemStoragePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SecureStorageBundle extends Bundle
{
    use PackageVersionTrait;

    public const PACKAGE_NAME = 'dachcom-digital/secure-storage';

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new FlysystemStoragePass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }
}
