<?php

namespace SecureStorageBundle\Flysystem\Adapter;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use SecureStorageBundle\Encrypter\EncrypterInterface;

class SecuredAdapter implements FilesystemAdapter
{
    protected Config $config;

    public function __construct(
        private array $encyrpterOptions,
        private ?array $securedPaths,
        private EncrypterInterface $encrypter,
        private FilesystemAdapter $inner
    ) {
        $encrypter::register();
    }

    private function isSecuredPath(string $path): bool
    {
        if (empty($this->securedPaths)) {
            return true;
        }

        foreach ($this->securedPaths as $securedPath) {
            if (str_starts_with($path, ltrim($securedPath, '/'))) {
                return true;
            }
        }

        return false;
    }

    public function fileExists(string $path): bool
    {
        return $this->inner->fileExists($path);
    }

    public function directoryExists(string $path): bool
    {
        return $this->inner->directoryExists($path);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        if ($config->get('bypass_secured_adapter') === true) {
            $this->inner->write($path, $contents, $config);

            return;
        }

        if (!$this->isSecuredPath($path)) {
            $this->inner->write($path, $contents, $config);

            return;
        }

        $stream = fopen('php://temp', 'w+');
        fwrite($stream, $contents);
        rewind($stream);

        $this->writeStream($path, $stream, $config);

        fclose($stream);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        if ($config->get('bypass_secured_adapter') === true) {
            $this->inner->write($path, $contents, $config);

            return;
        }

        if (!$this->isSecuredPath($path)) {
            $this->inner->writeStream($path, $contents, $config);

            return;
        }

        $this->encrypter::appendEncryption($contents, $this->encyrpterOptions);

        $this->inner->writeStream($path, $contents, $config);
    }

    public function read(string $path): string
    {
        if (!$this->isSecuredPath($path)) {
            return $this->inner->read($path);
        }

        $stream = $this->readStream($path);
        $contents = stream_get_contents($stream);
        fclose($stream);

        return $contents;
    }

    public function readStream(string $path)
    {
        if (!$this->isSecuredPath($path)) {
            return $this->inner->readStream($path);
        }

        $contents = $this->inner->readStream($path);

        $this->encrypter::appendDecryption($contents, $this->encyrpterOptions);

        return $contents;
    }

    public function delete(string $path): void
    {
        $this->inner->delete($path);
    }

    public function deleteDirectory(string $path): void
    {
        $this->inner->deleteDirectory($path);
    }

    public function createDirectory(string $path, Config $config): void
    {
        $this->inner->createDirectory($path, $config);
    }

    public function setVisibility(string $path, string $visibility): void
    {
        $this->inner->setVisibility($path, $visibility);
    }

    public function visibility(string $path): FileAttributes
    {
        return $this->inner->visibility($path);
    }

    public function mimeType(string $path): FileAttributes
    {
        if (!$this->isSecuredPath($path)) {
            return $this->inner->mimeType($path);
        }

        // @todo:
        // guessing mime type by its content is not possible because of the encrypted file
        // mostly the extension will be used to guess mime type

        return $this->inner->mimeType($path);
    }

    public function lastModified(string $path): FileAttributes
    {
        return $this->inner->lastModified($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        if (!$this->isSecuredPath($path)) {
            return $this->inner->fileSize($path);
        }

        // @todo: the file size returns size of encrypted file, not the original one

        return $this->inner->fileSize($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        return $this->inner->listContents($path, $deep);
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->inner->move($source, $destination, $config);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->inner->copy($source, $destination, $config);
    }

    public function __call(string $method, array $parameters)
    {
        return $this->forwardCallTo($this->inner, $method, $parameters);
    }
}