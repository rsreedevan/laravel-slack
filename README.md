# laravel-slack
Laravel Slack API Library - Very Simple Slack API Library for Laravel

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rsreedevan/laravel-slack/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/rsreedevan/laravel-slack/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/rsreedevan/laravel-slack/badges/build.png?b=master)](https://scrutinizer-ci.com/g/rsreedevan/laravel-slack/build-status/master)

## Installation 

Add the library to your Laravel application by simply running
```php 
composer require 'sreedev\laravel-slack' 
```

After the installation of package and dipendencies publish the config using laravel Artisan command
```php
php artisan vendor:publish --provider="Sreedev\Slack\SlackServiceProvider" --tag="config"
```

Now you can add your Slack API TOKEN to the ```.env``` file. Add ``` SLACK_API_TOKEN=<YOUR_SLACK_API_TOKEN> ``` 

Note: To get the Slack API TOKEN you have to create an APP in slack, you can create one by visiting https://api.slack.com/apps
please ensure that you are giving the proper permissions for your App. Once you did setup the app you can find your ``` Bot User OAuth Access Token ``` under
the **OAuth & Permissions** menu inside app settings.

## Usage 

Import the Facade to your Controller 

```php 
use Sreedev\Slack\Facades\Slack
```

Once imported you can literally run any Slack API requests as shown bellow 

## Calling Convention

Take the example of a Slack API Mentiod ```chat.postMessage($argsArray)``` , you can access it as 
```php 
Slack::chat('postMessage',$argsArray)
```

#### Send a chat message 

```php
//to a channel
Slack::chat('postMessage',['channel'=>'#channel_name', 'text'=>'Message to be send');

//to a user
Slack::chat('postMessage',['channel'=>'@username', 'text'=>'Message to be send');
```
