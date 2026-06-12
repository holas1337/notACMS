<?php

declare(strict_types=1);

namespace NotACms\Routing;

use NotACms\Attribute\LocalizedRoute;
use NotACms\Service\LocaleConfigInterface;
use NotACms\Service\SiteSettingsInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

#[AutoconfigureTag('routing.loader')]
final class LocalizedRouteLoader extends Loader
{
    public const string TYPE = 'localized';

    public const string ROUTES_FILENAME = '_routes.yaml';

    private bool $loaded = false;

    public function __construct(
        private readonly LocaleConfigInterface $localeConfig,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire('%notacms_content%')]
        private readonly string $contentDir,
        #[Autowire('%notacms.local_dir%')]
        private readonly string $localDir,
    ) {
        parent::__construct();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        if ($this->loaded) {
            throw new \RuntimeException('LocalizedRouteLoader is already loaded.');
        }

        $this->loaded = true;
        $routeCollection = new RouteCollection();

        foreach ([$this->contentDir.'/'.SiteSettingsInterface::SITE_CONFIG_FILENAME, $this->contentDir.'/'.self::ROUTES_FILENAME] as $configFile) {
            if (file_exists($configFile)) {
                $routeCollection->addResource(new FileResource($configFile));
            }
        }

        $locales = $this->localeConfig->getLocales();
        $defaultLocale = $this->localeConfig->getDefaultLocale();
        $overrides = $this->loadOverrides();

        $controllerNamespaces = [
            $this->projectDir.'/src/Controller' => 'NotACms\\Controller\\',
            $this->projectDir.'/'.$this->localDir.'/src/Controller' => 'NotACms\\Local\\Controller\\',
        ];

        foreach ($controllerNamespaces as $controllerDir => $controllerNamespace) {
            if (!is_dir($controllerDir)) {
                continue;
            }

            $finder = new Finder();
            $finder->files()->in($controllerDir)->name('*Controller.php');

            $this->registerControllerRoutes($routeCollection, $finder, $controllerNamespace, $locales, $defaultLocale, $overrides);
        }

        return $routeCollection;
    }

    /**
     * @param string[]                             $locales
     * @param array<string, array<string, string>> $overrides
     */
    private function registerControllerRoutes(RouteCollection $routeCollection, Finder $finder, string $controllerNamespace, array $locales, string $defaultLocale, array $overrides): void
    {
        foreach ($finder as $file) {
            $className = $controllerNamespace.$file->getFilenameWithoutExtension();

            if (!class_exists($className)) {
                continue;
            }

            $reflectionClass = new \ReflectionClass($className);

            foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $attributes = $method->getAttributes(LocalizedRoute::class);

                if ([] === $attributes) {
                    continue;
                }

                foreach ($attributes as $attribute) {
                    /** @var LocalizedRoute $localizedRoute */
                    $localizedRoute = $attribute->newInstance();

                    foreach ($locales as $locale) {
                        $routeName = $localizedRoute->name.'_'.$locale;
                        $path = $this->resolvePath($localizedRoute->name, $localizedRoute->path, $locale, $defaultLocale, $overrides);

                        $route = new Route($path);
                        $route->setDefault('_controller', $className.'::'.$method->getName());
                        $route->setDefault('locale', $locale);
                        $route->setRequirements($localizedRoute->requirements);

                        if ([] !== $localizedRoute->methods) {
                            $route->setMethods($localizedRoute->methods);
                        }

                        $routeCollection->add($routeName, $route, $localizedRoute->priority);
                    }
                }
            }
        }
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return self::TYPE === $type;
    }

    /**
     * @param array<string, array<string, string>> $overrides
     */
    private function resolvePath(string $name, string $defaultPath, string $locale, string $defaultLocale, array $overrides): string
    {
        if ($defaultLocale === $locale) {
            return $defaultPath;
        }

        if (isset($overrides[$name][$locale])) {
            return '/'.$locale.$overrides[$name][$locale];
        }

        return '/'.$locale.$defaultPath;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function loadOverrides(): array
    {
        $path = $this->contentDir.'/'.self::ROUTES_FILENAME;

        if (!file_exists($path)) {
            return [];
        }

        $data = Yaml::parseFile($path);

        return $data['routes'] ?? [];
    }
}
