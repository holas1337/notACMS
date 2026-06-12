<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentItem;
use NotACms\Content\ContentTree;
use NotACms\Service\SiteConfigServiceInterface;

final class RelatedPostsService implements RelatedPostsServiceInterface
{
    /** @return ContentItem[] */
    public function getRelatedPosts(ContentTree $contentTree, ContentItem $contentItem, int $limit = SiteConfigServiceInterface::DEFAULT_RELATED_POSTS_LIMIT): array
    {
        $posts = $contentTree->getAllPosts();

        $related = [];

        foreach ($contentItem->relatedSlugs() as $slug) {
            foreach ($posts as $post) {
                if ($slug === $post->slug()) {
                    $related[] = $post;

                    break;
                }
            }
        }

        $related = array_slice($related, 0, $limit);

        if ($limit <= count($related)) {
            return $related;
        }

        $scores = [];

        foreach ($posts as $postIndex => $post) {
            if ($post->isSame($contentItem)) {
                continue;
            }

            if (in_array($post, $related, true)) {
                continue;
            }

            $score = 0;

            if (null !== $contentItem->category() && $post->category() === $contentItem->category()) {
                $score += 2;
            }

            $score += count(array_intersect($post->tags(), $contentItem->tags()));

            if (0 < $score) {
                $scores[$postIndex] = $score;
            }
        }

        arsort($scores);

        $remaining = $limit - count($related);

        foreach (array_keys(array_slice($scores, 0, $remaining, true)) as $postIndex) {
            $related[] = $posts[$postIndex];
        }

        if ($limit > count($related)) {
            foreach ($posts as $post) {
                if ($limit <= count($related)) {
                    break;
                }

                if ($post->isSame($contentItem)) {
                    continue;
                }

                if (in_array($post, $related, true)) {
                    continue;
                }

                $related[] = $post;
            }
        }

        return $related;
    }
}
