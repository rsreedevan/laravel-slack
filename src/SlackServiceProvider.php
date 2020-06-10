<?php 
namespace Sreedev\Slack;

use Illuminate\Support\ServiceProvider;

class SlackServiceProvider extends ServiceProvider
{
    
    /**
     * boot
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Export the config file 
         * php artisan vendor:publish --provider="Sreedev\Slack\SlackServiceProvider" --tag="config"
        **/
        if ($this->app->runningInConsole()) {

            $this->publishes([
              __DIR__.'/../config/config.php' => config_path('slack.php'),
            ], 'config');
        
          }
    }
    
    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('slack', function($app){
            return new Slack(config("slack.SLACK_API_TOKEN"));
        });
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'slack'); 
    }

}