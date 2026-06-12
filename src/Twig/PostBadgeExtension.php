<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Content\ContentItem;
use Twig\Attribute\AsTwigFunction;

final readonly class PostBadgeExtension
{
    private const int SECONDS_PER_DAY = 86400;

    private const string BADGE_NEW = 'new';

    private const string BADGE_UPDATED = 'updated';

    #[AsTwigFunction(name: 'post_badge')]
    public function postBadge(ContentItem $contentItem, int $newPostDays): ?string
    {
        $now = new \DateTimeImmutable();

        if (!$contentItem->isDraft() && !$contentItem->isScheduled() && $contentItem->date() instanceof \DateTimeImmutable) {
            $threshold = $newPostDays * self::SECONDS_PER_DAY;
            $age = $now->getTimestamp() - $contentItem->date()->getTimestamp();

            if ($age < $threshold) {
                return self::BADGE_NEW;
            }
        }

        if ($contentItem->updatedDate() instanceof \DateTimeImmutable) {
            $threshold = $newPostDays * self::SECONDS_PER_DAY;
            $age = $now->getTimestamp() - $contentItem->updatedDate()->getTimestamp();

            if ($age < $threshold) {
                return self::BADGE_UPDATED;
            }
        }

        return null;
    }
}
