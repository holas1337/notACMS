<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $c): void {
    $rootSass = ['assets/styles/app.scss'];

    // Local override entrypoint — named app_local.scss to avoid basename
    // collision with core assets/styles/app.scss.
    if (file_exists(__DIR__ . '/../../local/assets/styles/app_local.scss')) {
        $rootSass[] = 'local/assets/styles/app_local.scss';
    }

    $c->extension('symfonycasts_sass', ['root_sass' => $rootSass]);
};
