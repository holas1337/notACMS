<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Content\Enum\FilterType;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\Content\ContentTreeBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFunction;

final readonly class BlogFilterExtension
{
    public function __construct(
        private ContentServiceInterface $contentService,
        private TranslatorInterface $translator,
    ) {
    }

    #[AsTwigFunction(name: 'blog_filter_title')]
    public function blogFilterTitle(
        ?string $filterType,
        ?string $filterValue,
        ?int $archiveYear,
        ?int $archiveMonth,
        string $locale,
    ): string {
        if (FilterType::Category->value === $filterType) {
            return ucfirst((string) $filterValue);
        }

        if (FilterType::Tag->value === $filterType) {
            return '#'.$filterValue;
        }

        if (FilterType::Archive->value === $filterType) {
            return $this->formatArchiveDate($archiveYear, $archiveMonth, $locale);
        }

        $blog = $this->contentService->findByDirectoryKey(ContentTreeBuilderInterface::BLOG_DIRECTORY_KEY, $locale);

        return $blog?->menuLabel() ?? $this->translator->trans('blog.title');
    }

    private function formatArchiveDate(?int $year, ?int $month, string $locale): string
    {
        $intlDateFormatter = new \IntlDateFormatter(
            $locale,
            null !== $month ? \IntlDateFormatter::LONG : \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
        );
        $intlDateFormatter->setPattern(null !== $month ? 'MMMM yyyy' : 'yyyy');

        $date = new \DateTimeImmutable(sprintf('%04d-%02d-01', $year ?? 0, $month ?? 1));

        return (string) $intlDateFormatter->format($date);
    }
}
