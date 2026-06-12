<?php

declare(strict_types=1);

namespace NotACms\Content\Enum;

enum CardLayout: string
{
    case Top = 'layout-top';
    case Right = 'layout-right';
    case Text = 'layout-text';
    case Left = 'layout-left';

    /** @return list<string> */
    public static function cycle(): array
    {
        return array_map(fn (self $cardLayout) => $cardLayout->value, self::cases());
    }
}
