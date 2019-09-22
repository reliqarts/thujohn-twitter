<?php

namespace ReliqArts\Thujohn\Twitter;

use Illuminate\Support\ServiceProvider;

class TwitterServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     */
    public function register()
    {
        $app = $this->app ?: app();
        $appVersion = method_exists($app, 'version') ? $app->version() : $app::VERSION;
        $laravelVersion = substr($appVersion, 0, strpos($appVersion, '.'));
        $isLumen = false;

        if (strpos(strtolower($laravelVersion), 'lumen') !== false) {
            $isLumen = true;
            $laravelVersion = str_replace('Lumen (', '', $laravelVersion);
        }

        if ($laravelVersion === 5) {
            $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'ttwitter');

            if ($isLumen) {
                $this->publishes([
                    __DIR__ . '/config/config.php' => base_path('config/ttwitter.php'),
                ]);
            } else {
                $this->publishes([
                    __DIR__ . '/../config/config.php' => config_path('ttwitter.php'),
                ]);
            }
        } elseif ($laravelVersion === 4) {
            $this->package('reliqarts/thujohn-twitter', 'ttwitter', __DIR__ . '/../src');
        }

        $this->app->singleton(Twitter::class, function () use ($app) {
            return new Twitter($app['config'], $app['session.store']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['ttwitter'];
    }
}
