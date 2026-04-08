<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $c): void {
    $rootSass = ['assets/styles/app.scss'];

    // The local override file must be named local.scss (not app.scss) because
    // symfonycasts_sass requires unique basenames across all root_sass entries.
    if (file_exists(__DIR__ . '/../../local/assets/styles/local.scss')) {
        $rootSass[] = 'local/assets/styles/local.scss';
    }

    $c->extension('symfonycasts_sass', ['root_sass' => $rootSass]);
};
