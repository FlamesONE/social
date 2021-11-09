<?php 

namespace Flamesone\Social\Socials;

use Exception;
use Flamesone\Social\Libs\LightOpenID;

class Steam
{
    /**
     * @var string $domain
     */
    protected $domain = null;

    /**
     * @var LightOpenID
     */
    protected $openid;

    /**
     * @var string WEB Steam api key - https://steamcommunity.com/dev/apikey
     */
    protected $api_key;
    
    /**
     * @var bool Info from API KEY
     */
    protected $info = true;

    /**
     * @var bool Session listen
     */
    protected $session = false;

    /**
     * @var string steam url
     */
    public $steamurl = "https://steamcommunity.com/openid";
    
    /**
     * Constructor
     * 
     * @var array $arr - Config data
     */
    public function setParams( array $arr )
    {
        $this->domain   = $arr["domain"];
        $this->api_key  = $arr["apikey"];
        $this->info     = $arr["api"] ?? false;
        $this->openid   = new LightOpenID( $this->domain );
    }

    /**
     * Auth function
     */
    public function Auth()
    {
        if ( !$this->openid->mode ) 
        {
            $this->openid->identity = $this->steamurl;

            $location = sprintf( "Location: %s", $this->openid->authUrl() );

            return header( $location );
        }
        elseif( $this->openid->mode == "cancel" )
            return false;
        else
            return true;
    }

    /**
     * Auth handle
     * 
     * @return array
     */
    public function Handle( )
    {
        if( $this->openid->mode && $this->openid->mode != "cancel" )
        {
            if( $this->openid->validate() )
            {
                $matches = [];

                $data = [];

                preg_match( "/^https?:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/", $this->openid->identity, $matches );
      
                if( $this->info )
                {
                    $url = sprintf( "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=%s&steamids=%s", $this->api_key, $matches[1] );
                    $data = json_decode( file_get_contents( $url ), true )['response']['players'][0]; 
                }

                !$data["steamid"] && $data["steamid"] = $matches[1];

                return $data;
            }
            throw new Exception("Validate error");
        }
        throw new Exception("Open id mode is undefined");
    }
}