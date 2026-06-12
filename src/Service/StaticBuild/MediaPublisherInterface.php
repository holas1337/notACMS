<?php

declare(strict_types=1);

namespace NotACms\Service\StaticBuild;

use NotACms\Content\ValueObject\MediaPublishResult;

interface MediaPublisherInterface
{
    public function publish(string $outputDir): MediaPublishResult;
}
