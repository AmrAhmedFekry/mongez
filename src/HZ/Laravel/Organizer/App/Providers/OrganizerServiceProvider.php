<?php

namespace HZ\Laravel\Organizer\App\Providers;

use App;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use HZ\Laravel\Organizer\App\Events\Events;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class OrganizerServiceProvider extends ServiceProvider
{
    /**
     * Startup config
     * 
     * @var array
     */
    protected $config = [];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->config = config('organizer');

        // register the repositories as singletones, only one instance in the entire application
        foreach (config('organizer.repositories') as $repositoryClass) {
            App::singleton($repositoryClass);
        }

        App::singleton(Events::class);

        // register macros
        $this->registerMacros();

        $this->registerEventsListeners();

        if (strtolower(config('database.driver')) === 'mysql') {
            // manage database options
            $this->manageDatabase();
        }
    }

    /**
     * Register the events listeners
     * 
     * @return void
     */
    protected function registerEventsListeners()
    {
        $events = App::make(Events::class);

        foreach ((array) config('organizer.events') as $eventName => $eventListeners) {
            $eventListeners = (array) $eventListeners;
            foreach ($eventListeners as $eventListener) {
                $events->subscribe($eventName, $eventListener);
            }
        }
    }
    
    /**
     * Register all macros
     * 
     * @return void 
     */
    protected function registerMacros()
    {
        $macros = (array) $this->config['macros'];

        foreach ($macros as $original => $mixin) {
            $mixinObject = new $mixin;
            $original::mixin($mixinObject);

            // if the original class is the query builder
            // then we will inject same macro in the eloquent builder
            if ($original == QueryBuilder::class) {
                foreach (get_class_methods($mixinObject) as $method) {
                    $callback = $mixinObject->$method();
                    EloquentBuilder::macro($method, Closure::bind($callback, null, EloquentBuilder::class));
                }
            }
        }
    }

    /**
     * Manage database options
     * 
     * @return void
     */
    public function manageDatabase()
    {
        $defaultLength = Arr::get($this->config, 'database.mysql.defaultStringLength');

        if ($defaultLength) {
            Schema::defaultStringLength($defaultLength);
        }
    }
}
