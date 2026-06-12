<?php

declare(strict_types=1);

namespace NotACms\Service;

use NotACms\Service\Image\ImageConfigInterface;

interface SiteConfigServiceInterface extends LocaleConfigInterface, SiteSettingsInterface, ImageConfigInterface
{
}
