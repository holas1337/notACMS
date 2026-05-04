<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Content\ContentItem;
use Twig\Attribute\AsTwigFunction;

final readonly class PostBadgeExtension
{
    #[AsTwigFunction(name: 'post_badge')]
    public function postBadge(ContentItem $contentItem, int $newPostDays): ?string
    {
        $now = new \DateTimeImmutable();

        if (!$contentItem->isDraft() && !$contentItem->isScheduled() && $contentItem->date() instanceof \DateTimeImmutable) {
            $threshold = $newPostDays * 86400;
            $age = $now->getTimestamp() - $contentItem->date()->getTimestamp();

            if ($age < $threshold) {
                return 'new';
            }
        }

        if ($contentItem->updatedDate() instanceof \DateTimeImmutable) {
            $threshold = $newPostDays * 86400;
            $age = $now->getTimestamp() - $contentItem->updatedDate()->getTimestamp();

            if ($age < $threshold) {
                return 'updated';
            }
        }

        return null;
    }
}
