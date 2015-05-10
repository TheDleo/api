<?php

namespace Dingo\Api\Provider;

use ReflectionClass;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Dingo\Api\Routing\Adapter\LaravelAdapter;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');

        $this->app->instance('app.middleware', $this->gatherAppMiddleware($kernel));

        $this->addRequestMiddlewareToBeginning($kernel);

        $this->app->register('Dingo\Api\Provider\ApiServiceProvider');

        $this->app->singleton('api.router.adapter', function ($app) {
            return new LaravelAdapter($app['router']);
        });
    }

    /**
     * Add the request middleware to the beggining of the kernel.
     *
     * @param \Illuminate\Contracts\Http\Kernel $kernel
     *
     * @return void
     */
    protected function addRequestMiddlewareToBeginning(Kernel $kernel)
    {
        $kernel->prependMiddleware('Dingo\Api\Http\Middleware\RequestMiddleware');
    }

    /**
     * Gather the application middleware besides this one so that we can send
     * our request through them, exactly how the developer wanted.
     *
     * @param \Illuminate\Contracts\Http\Kernel $kernel
     *
     * @return array
     */
    protected function gatherAppMiddleware(Kernel $kernel)
    {
        $reflection = new ReflectionClass($kernel);

        $property = $reflection->getProperty('middleware');
        $property->setAccessible(true);

        $middleware = $property->getValue($kernel);

        return $middleware;
    }
}