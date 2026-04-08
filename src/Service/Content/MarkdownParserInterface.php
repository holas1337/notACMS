<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ValueObject\ParsedMarkdown;

interface MarkdownParserInterface
{
    public function parse(string $markdown): ParsedMarkdown;
}
