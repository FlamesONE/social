# Social auth on PHP

Supported platforms:
- Steam
- VK
- Discord

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
