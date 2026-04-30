<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Twig;

use NotACms\Content\ContentItem;
use NotACms\Twig\PostBadgeExtension;
use PHPUnit\Framework\TestCase;

final class PostBadgeExtensionTest extends TestCase
{
    private PostBadgeExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new PostBadgeExtension();
    }

    public function testReturnsNewForRecentPost(): void
    {
        $post = new ContentItem(
            [
                'date' => new \DateTimeImmutable('-1 day'),
                'draft' => false,
            ],
            '',
            'en',
        );

        $result = $this->extension->postBadge($post, 14);

        self::assertSame('new', $result);
    }

    public function testReturnsNullForOldPost(): void
    {
        $post = new ContentItem(
            [
                'date' => new \DateTimeImmutable('-30 days'),
                'draft' => false,
            ],
            '',
            'en',
        );

        $result = $this->extension->postBadge($post, 14);

        self::assertNull($result);
    }

    public function testReturnsUpdatedForRecentlyUpdatedPost(): void
    {
        $post = new ContentItem(
            [
                'date' => new \DateTimeImmutable('-60 days'),
                'updated' => new \DateTimeImmutable('-2 days'),
                'draft' => false,
            ],
            '',
            'en',
        );

        $result = $this->extension->postBadge($post, 14);

        self::assertSame('updated', $result);
    }

    public function testReturnsNullForDraftPost(): void
    {
        $post = new ContentItem(
            [
                'date' => new \DateTimeImmutable('-1 day'),
                'draft' => true,
            ],
            '',
            'en',
        );

        $result = $this->extension->postBadge($post, 14);

        self::assertNull($result);
    }

    public function testReturnsNullForScheduledPost(): void
    {
        $post = new ContentItem(
            [
                'date' => new \DateTimeImmutable('+3 days'),
                'draft' => false,
            ],
            '',
            'en',
        );

        $result = $this->extension->postBadge($post, 14);

        self::assertNull($result);
    }

    public function testReturnsNullWhenPostHasNoDate(): void
    {
        $post = new ContentItem([], '', 'en');

        $result = $this->extension->postBadge($post, 14);

        self::assertNull($result);
    }

    public function testNewBadgeTakesPrecedenceOverUpdated(): void
    {
        $post = new ContentItem(
            [
                'date' => new \DateTimeImmutable('-1 day'),
                'updated' => new \DateTimeImmutable('-1 day'),
                'draft' => false,
            ],
            '',
            'en',
        );

        $result = $this->extension->postBadge($post, 14);

        self::assertSame('new', $result);
    }
}
