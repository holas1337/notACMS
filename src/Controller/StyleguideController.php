<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Content\ContentItem;
use NotACms\Content\ValueObject\ArchiveMonth;
use NotACms\Content\ValueObject\CategoryCount;
use NotACms\Content\ValueObject\SidebarData;
use NotACms\Content\ValueObject\TagCount;
use NotACms\Service\LocaleConfigInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class StyleguideController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.debug%')]
        private readonly bool $debug,
        private readonly LocaleConfigInterface $localeConfig,
    ) {
    }

    #[Route('/styleguide/', name: 'styleguide')]
    public function index(): Response
    {
        if (!$this->debug) {
            throw new NotFoundHttpException();
        }

        $dummyPost = new ContentItem(
            frontMatter: [
                'title' => 'Example Blog Post Title',
                'description' => 'A short description of this post for SEO and previews.',
                'date' => new \DateTimeImmutable('2025-03-15'),
                'tags' => ['symfony', 'php', 'docker'],
                'image' => '/media/example-post/placeholder.webp',
                'image_alt' => 'Placeholder image',
            ],
            htmlContent: '<p>Post content with <strong>bold</strong>, <em>italic</em>, <code>inline code</code>, and <a href="#">links</a>. This is a longer excerpt to demonstrate how the post card truncates text in listing views.</p>',
            locale: $this->localeConfig->getDefaultLocale(),
            url: '/blog/example-post/',
            sourcePath: 'blog/tutorials/example-post/en.md',
        );

        $dummyPost2 = new ContentItem(
            frontMatter: [
                'title' => 'Another Post Without an Image',
                'description' => 'This post has no featured image to show the fallback state.',
                'date' => new \DateTimeImmutable('2025-02-10'),
                'tags' => ['linux', 'homelab'],
            ],
            htmlContent: '<p>Content of the second dummy post without a featured image.</p>',
            locale: $this->localeConfig->getDefaultLocale(),
            url: '/blog/another-post/',
            sourcePath: 'blog/notes/another-post/en.md',
        );

        $dummyPostScheduled = new ContentItem(
            frontMatter: [
                'title' => 'Upcoming Scheduled Post',
                'description' => 'This post is scheduled for a future date — demonstrates the planned variant.',
                'date' => new \DateTimeImmutable('2099-12-01'),
                'tags' => ['symfony', 'php'],
                'image' => '/media/example-post/placeholder.webp',
                'image_alt' => 'Placeholder image',
            ],
            htmlContent: '<p>Scheduled post content.</p>',
            locale: $this->localeConfig->getDefaultLocale(),
            url: '/blog/scheduled-post/',
            sourcePath: 'blog/notes/scheduled-post/en.md',
        );

        $sidebarData = new SidebarData(
            recentPosts: [$dummyPost, $dummyPost2],
            categories: [
                new CategoryCount('tutorials', 12),
                new CategoryCount('notes', 8),
                new CategoryCount('projects', 5),
            ],
            tags: [
                new TagCount('symfony', 15),
                new TagCount('php', 12),
                new TagCount('docker', 9),
                new TagCount('linux', 7),
                new TagCount('homelab', 4),
            ],
            archiveMonths: [
                new ArchiveMonth(2026, 3, 4),
                new ArchiveMonth(2026, 1, 2),
                new ArchiveMonth(2025, 11, 3),
                new ArchiveMonth(2025, 8, 1),
                new ArchiveMonth(2024, 6, 4),
                new ArchiveMonth(2024, 2, 3),
                new ArchiveMonth(2023, 10, 3),
                new ArchiveMonth(2023, 4, 2),
            ],
        );

        return $this->render('page/styleguide.html.twig', [
            'locale' => $this->localeConfig->getDefaultLocale(),
            'dummyPosts' => [$dummyPost, $dummyPost2, $dummyPostScheduled],
            'sidebarData' => $sidebarData,
        ]);
    }
}
