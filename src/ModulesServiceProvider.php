<?php

namespace Coolsam\FilamentModules;

use Coolsam\FilamentModules\Commands\ModuleMakePanelCommand;
use Coolsam\FilamentModules\Extensions\LaravelModulesServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Nwidart\Modules\Facades\Module;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ModulesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('modules')
            ->hasConfigFile('modules')
            ->hasViews()
            ->hasCommands([
                ModuleMakePanelCommand::class,
            ]);
    }

    public function register()
    {
        $this->app->register(LaravelModulesServiceProvider::class);
        $this->app->singleton('coolsam-modules', Modules::class);
        $this->app->afterResolving('filament', function () {
            foreach (Filament::getPanels() as $panel) {
                $id = \Str::of($panel->getId());
                if ($id->contains('::')) {
                    $moduleName = $panel->getPath();

                    $module = Module::find($moduleName);

                    $configPath = $module->getPath() . '/Config/config.php';

                    $config = require_once($configPath);

                    $title = Arr::get($config, 'panel_title.admin', null) ?? $id->replace(['::', '-'], [' ', ' '])->title()->toString();

                    $panel
                        ->renderHook(
                            'panels::sidebar.nav.start',
                            fn () => Blade::render(
                                <<<'HTML'
                                <x-filament::link href="/" size="xl" color="gray" icon="sui-wrap-back" class="self-start" tooltip="Voltar ao módulo principal">Voltar</x-filament::link>
                                <h2 class='font-black text-xl'>{{ $title }}</h2>
                                HTML,
                                ['title' => $title]
                            )
                        )
                        ->renderHook(
                            'panels::sidebar.nav.end',
                            fn () => Blade::render(
                                <<<'HTML'
                                <x-filament::link href="/" size="xl" color="gray" icon="sui-wrap-back" class="self-start" tooltip="Voltar ao módulo principal">Voltar</x-filament::link>
                                HTML,
                                ['title' => $title]
                            )
                        );
                }
            }
        });

        return parent::register();
    }
}
