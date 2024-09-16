<?php

declare(strict_types=1);

namespace SecureStorageBundle\Messenger\Middleware;

use Pimcore\Bundle\SimpleBackendSearchBundle\Message\SearchBackendMessage;
use Pimcore\Messenger\AssetPreviewImageMessage;
use Pimcore\Messenger\AssetUpdateTasksMessage;
use Pimcore\Messenger\OptimizeImageMessage;
use Pimcore\Model\Asset;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class GuardMiddleware implements MiddlewareInterface
{
    public function __construct(protected array $secureStorageConfig)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (!$this->isGuardedMessage($envelope)) {
            return $stack->next()->handle($envelope, $stack);
        }

        return $envelope;
    }

    private function isGuardedMessage(Envelope $envelope): bool
    {
        $omitMessage = match (get_class($envelope->getMessage())) {
            SearchBackendMessage::class => $this->omitByBackendSearch($envelope),
            //AssetPreviewImageMessage::class => false,
            //AssetUpdateTasksMessage::class => false,
            //OptimizeImageMessage::class => false,
            default => false
        };

        return $omitMessage === true;
    }

    private function omitByBackendSearch(Envelope $envelope): bool
    {
        $config = $this->secureStorageConfig['pimcore_asset_protection']['omit_backend_search_indexing'];

        if (count($config['paths']) === 0) {
            return false;
        }

        $path = $this->extractElementPath($envelope);

        if ($path === null) {
            return false;
        }

        return $this->checkPaths($path, $config['paths']);
    }

    private function checkPaths(string $path, array $securePaths): bool
    {
        foreach ($securePaths as $securedPath) {
            if (str_starts_with($path, $securedPath)) {
                return true;
            }
        }

        return false;
    }

    private function extractElementPath(Envelope $envelope): ?string
    {
        $asset = null;

        // restriction type: allow_backend_search_indexing
        if (get_class($envelope->getMessage()) === SearchBackendMessage::class) {
            /** @var SearchBackendMessage $message */
            $message = $envelope->getMessage();
            if ($message->getType() === 'asset') {
                $asset = Asset::getById($message->getId());
            }
        }

        // restriction type: allow_asset_preview_image_generation
        if (get_class($envelope->getMessage()) === AssetPreviewImageMessage::class) {
            /** @var AssetPreviewImageMessage $message */
            $message = $envelope->getMessage();
            $asset = Asset::getById($message->getId());
        }

        // restriction type: allow_asset_update_preview_image_generation
        if (get_class($envelope->getMessage()) === AssetUpdateTasksMessage::class) {
            /** @var AssetUpdateTasksMessage $message */
            $message = $envelope->getMessage();
            $asset = Asset::getById($message->getId());
        }

        // restriction type: allow_image_optimizing
        if (get_class($envelope->getMessage()) === OptimizeImageMessage::class) {
            /** @var OptimizeImageMessage $message */
            $message = $envelope->getMessage();
            $asset = sprintf('/%s', ltrim($message->getPath(), '/'));
        }

        if (is_string($asset)) {
            return $asset;
        }

        if ($asset instanceof Asset) {
            return $asset->getPath();
        }

        return null;
    }
}