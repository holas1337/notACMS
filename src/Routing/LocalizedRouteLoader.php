<?php

declare(strict_types=1);

namespace NotACms\Routing;

use NotACms\Attribute\LocalizedRoute;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\Config\Loader\Loader;
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

    private bool $loaded = false;

    /** @var array<string, array<string, string>>|null */
    private ?array $routeOverrides = null;

    public function __construct(
        private readonly SiteConfigServiceInterface $siteConfigService,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire('%notacms_content%')]
        private readonly string $contentDir,
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
        $locales = $this->siteConfigService->getLocales();
        $defaultLocale = $this->siteConfigService->getDefaultLocale();
        $overrides = $this->loadOverrides();

        $controllerDir = $this->projectDir.'/src/Controller';
        $finder = new Finder();
        $finder->files()->in($controllerDir)->name('*Controller.php');

        foreach ($finder as $file) {
            $className = 'NotACms\\Controller\\'.$file->getFilenameWithoutExtension();

            if (!class_exists($className)) {
                continue;
            }

            $refClass = new \ReflectionClass($className);

            foreach ($refClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $attributes = $method->getAttributes(LocalizedRoute::class);

                if ([] === $attributes) {
                    continue;
                }

                foreach ($attributes as $attribute) {
                    /** @var LocalizedRoute $attr */
                    $attr = $attribute->newInstance();

                    foreach ($locales as $locale) {
                        $routeName = $attr->name.'_'.$locale;
                        $path = $this->resolvePath($attr->name, $attr->path, $locale, $defaultLocale, $overrides);

                        $route = new Route($path);
                        $route->setDefault('_controller', $className.'::'.$method->getName());
                        $route->setDefault('locale', $locale);
                        $route->setRequirements($attr->requirements);

                        if ([] !== $attr->methods) {
                            $route->setMethods($attr->methods);
                        }

                        $routeCollection->add($routeName, $route, $attr->priority);
                    }
                }
            }
        }

        return $routeCollection;
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
        if (null !== $this->routeOverrides) {
            return $this->routeOverrides;
        }

        $path = $this->contentDir.'/_routes.yaml';

        if (!file_exists($path)) {
            return $this->routeOverrides = [];
        }

        $data = Yaml::parseFile($path);

        return $this->routeOverrides = $data['routes'] ?? [];
    }
}
