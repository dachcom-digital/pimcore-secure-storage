# Pimcore Secure Storage Bundle
[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Release](https://img.shields.io/packagist/v/dachcom-digital/secure-storage.svg?style=flat-square)](https://packagist.org/packages/dachcom-digital/secure-storage)

### Release Plan

| Release | Supported Pimcore Versions | Supported Symfony Versions | Release Date | Maintained     | Branch |
|---------|----------------------------|----------------------------|--------------|----------------|--------|
| **1.x** | `^11.3`                    | `6.2`                      | --           | Feature Branch | master |


## Installation

```json
"require" : {
    "dachcom-digital/secure-storage" : "~1.0.0",
}
```

Add Bundle to `bundles.php`:
```php
return [
    SecureStorageBundle\SecureStorageBundle::class => ['all' => true],
];
```

## Description
Encrypt/Decrypt assets on the fly!

## Usage

> [!CAUTION]  
> This is a very, very dangerous bundle which can lead to heavy data loss, if you're not careful!
> Please read the instructions carefully!

## Safety Instructions
- Do not define paths with existing assets. Create a new folder or delete all assets first. Those assets can't be opened after defined (since they're not encrypted)
- You'll never be able to remove those paths from configuration. If you have to, you need to download the assets from backend first
- Do not change the key, after you pushed this to production. Encrypted assets will be end up corrupt

## Limitations
- The secure adapter only supports the `LocalFilesystemAdapter`. This is fine, since other adapters like aws or cloudflare usually already support encryption by default
- Thumbnails can't be generated, since pimcore uses the `getLocaleFileFromStream` method in `TemporaryFileHelperTrait`. This is something we might can fix in the near future

## Configuration

### File Encryption

```yaml
secure_storage:
    encrypter:
        options:
            cipher: 'aes-128-cbc'   # default
            key: 'your-12-bit-key'  # create your key with base64_encode(openssl_random_pseudo_bytes(16));

    secured_fly_system_storages:

        # form builder (if you want to encrypt form builder data)
        - storage: form_builder.chunk.storage
        - storage: form_builder.files.storage

        # pimcore
        -
            storage: pimcore.asset.storage
            paths:
                - /secure-storage
                - /formdata
```

#### Custom Encrypter
TBD

***

### Asset Protection

```yaml
secure_storage:

    pimcore_asset_protection:

        # protects:
        # - public/var/assets [pimcore.asset.storage]
        # - public/tmp/asset-cache [pimcore.asset_cache.storage]
        # - public/tmp/thumbnails [pimcore.thumbnail.storage]
        htaccess_protection_public_directories:
            paths:
                - /secure-storage

        omit_backend_search_indexing:
            paths:
                - /secure-storage
```

***

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)
