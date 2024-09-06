<?php

namespace SecureStorageBundle\DependencyInjection\CompilerPass;

use League\Flysystem\Local\LocalFilesystemAdapter;
use SecureStorageBundle\Encrypter\EncrypterInterface;
use SecureStorageBundle\Flysystem\Adapter\SecuredAdapter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FlysystemStoragePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $securedStorageConfig = $container->getParameter('pimcore.secure_storage.config');

        if (!$container->hasDefinition($securedStorageConfig['encrypter']['class'])) {
            $encrypterDefinition = new Definition($securedStorageConfig['encrypter']['class']);
            $encrypterDefinition->setAutoconfigured(true);
            $container->setDefinition($securedStorageConfig['encrypter']['class'], $encrypterDefinition);
        }

        /** @var EncrypterInterface $encrypter */
        $encrypter = $container->get($securedStorageConfig['encrypter']['class']);

        $optionsResolver = new OptionsResolver();
        $encrypter::configureOptions($optionsResolver);

        foreach ($securedStorageConfig['secured_fly_system_storages'] as $storageConfig) {

            $adapterName = sprintf('flysystem.adapter.%s', $storageConfig['storage']);
            $securedAdapterName = sprintf('flysystem.adapter.secured.%s', $storageConfig['storage']);

            if (!$container->hasDefinition($adapterName)) {
                continue;
            }

            if ($container->getDefinition($adapterName)->getClass() !== LocalFilesystemAdapter::class) {
                continue;
            }

            $securedAdapter = new Definition(SecuredAdapter::class);
            $securedAdapter->setArguments([
                $optionsResolver->resolve($securedStorageConfig['encrypter']['options']),
                $storageConfig['paths'],
                new Reference($securedStorageConfig['encrypter']['class']),
                new Reference(sprintf('%s.inner', $securedAdapterName))
            ]);

            $securedAdapter->setDecoratedService($adapterName);

            $container->setDefinition($securedAdapterName, $securedAdapter);
        }
    }
}
