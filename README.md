# Social auth on PHP

Supported platforms:
- Steam
- VK
- Discord

VK Auth data:
https://vk.com/apps?act=manage

![image](https://user-images.githubusercontent.com/62756604/140979351-934c1d6f-797c-4870-927b-b47f667c7099.png)

![image](https://user-images.githubusercontent.com/62756604/140979257-961f8d65-c332-448e-82b3-acb22609857f.png)

Discord auth data:
https://discord.com/developers/applications

![image](https://user-images.githubusercontent.com/62756604/140979525-5f0ec014-0665-44da-9c79-ec9058e0e01d.png)

Steam auth data:
https://steamcommunity.com/dev/apikey

![image](https://user-images.githubusercontent.com/62756604/140979649-6b615222-5130-4aba-837e-2cb4e903f837.png)

Example:
```php
<?php

require "../vendor/autoload.php";

use src\Social;

$domain = "http://domain.com/";

$Social = new Social([
    "steam" => [
        "domain"  => $domain,
        "apikey"  => "d",
        "api"     => true
    ],
    "discord" => [
        "red_url"       => "$domain?auth=discord",
        "client_id"     => "c",
        "bot_token"     => "b",
        "client_secret" => "a",
        "session"       => "true",
        "scopes"        => [
            "identify", "guilds"
        ]
    ],
    "vk" => [
        "red_url"       => "$domain?auth=vk",
        "client_id"     => "1",
        "client_secret" => "2"
    ],
]);

!$_SESSION && session_start();

if( isset( $_GET["auth"] ) && !empty( $_GET["auth"] ) )
{
    // Dynamic auth
    if( $Social->auth($_GET["auth"]) )
    {
        $Social->handle($_GET["auth"], function($data) {
            // Get request data
            print_r( $data );
        });
    }

    // Current auth
    if( $Social->auth("steam") ) // If auth, we get auth data
    {
        $Social->handle("steam", function($data) {
            // Get request data
            print_r( $data );
        });
    }
}
```
