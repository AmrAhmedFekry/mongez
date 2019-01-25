<?php       
namespace HZ\Laravel\Organizer\App\Contracts\Events;

interface EventsInterface
{
    /**
     * Trigger the given event(s) and pass the given arguments to any callback that
     * is listening to that event
     * Multiple events could be triggered with one method by adding space between each event
     * 
     * @return  string $events
     * @return  mixed ...$callbackArguments
     * @return mixed
     */
    public function trigger(string $events, ...$callbackArguments);
    
    /**
     * Subscribe to the given event name, or in other words add event listener
     * 
     * @return  string $events
     * @return  string $callback
     * @return void
     */
    public function subscribe(string $events, string $callback);
}