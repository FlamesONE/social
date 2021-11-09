<?php 

namespace Flamesone\Social\Socials;

use Exception;

class Discord
{   
    /**
     * Api URL
     */
    public $baseUrl = "https://discord.com";

    /**
     * @var string $red_url = Discord redirect site
     */
    protected $red_url;

    /**
     * @var int $client_id = Client id, from Discord application
     */
    protected $client_id;

     /**
     * @var bool $bot_token = Bot token, for guild auth
     */
    protected $bot_token = null;

    /**
     * @var array $scopes = Custom identifity scopes
     */
    protected $scopes = [];

    /**
     * @var string Auth token
     */
    protected $token = null;
    
    /**
     * @var bool Save state to session
     */
    protected $session = false;

    /**
     * @var string Client secret
     */
    protected $client_secret;

    /**
     * Constructor
     * 
     * @var array $arr - Config data
     */
    public function setParams( array $arr )
    {
        $this->red_url          = $arr["red_url"];
        $this->client_id        = $arr["client_id"];
        $this->client_secret    = $arr["client_secret"];
        $this->bot_token        = $arr["bot_token"];
        $this->scopes           = $arr["scopes"];
        $this->session          = $arr["session"];

        $this->session && !isset( $_SESSION ) && session_start();
    }

    /**
     * Get scopes
     */
    protected function getScopes()
    {
        return implode( "%20", $this->scopes );
    }

    /**
     * Generate hash state
     */
    public function generateState()
    {
        $state = bin2hex(openssl_random_pseudo_bytes(12));
        $this->session && $this->saveToSession($state);
        return $state;
    }

    /**
     * Save state to session
     */
    protected function saveToSession( string $state )
    {
        $_SESSION["state"] = $state;
    }

    /**
     * Is valid state
     */
    protected function isState( string $state )
    {
        return ( $_SESSION["state"] == $state );
    }

    /**
     * Generate URL
     */
    protected function generateURL()
    {
        return "https://discordapp.com/oauth2/authorize?response_type=code&client_id={$this->client_id}&redirect_uri={$this->red_url}&scope={$this->getScopes()}&state={$this->generateState()}";
    }

    /**
     * auth redirect
     */
    public function auth()
    {
        !isset( $_GET['code'] ) && header("Location: {$this->generateURL()}");
        return true;
    }

    /**
     * send cURL request
     */
    protected function sendCurl( string $url, $data = [], string $token = null, array $headers = [], string $method = "POST", bool $decode = true )
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        
        if( $method == "POST" )
            curl_setopt($curl, CURLOPT_POST, 1);

        $token != null && $headers = [
            'Content-Type: application/x-www-form-urlencoded', 
            "Authorization: Bearer $token"
        ];

        !empty( $headers ) && curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if( !empty( $data ) )
        {
            if( is_array( $data ) )
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( $data ) );
            else
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data );
        }

        $response = curl_exec($curl);
        curl_close($curl);
        
        return $decode == true ? json_decode($response, true) : $response;
    }

    /**
     * Init request
     */
    public function Handle()
    {
        $code   = $_GET['code'];

        if( $this->session )
        {
            if( !$this->isState( $_GET["state"] ) )
                throw new Exception("State is not currect {$_SESSION['state']}");
        }

        $return = [];
        
        # Request data array
        $data   = [
            "client_id"         => $this->client_id,
            "client_secret"     => $this->client_secret,
            "grant_type"        => "authorization_code",
            "code"              => $code,
            "redirect_uri"      => $this->red_url
        ];

        $curl = $this->sendCurl( $this->baseUrl . "/api/oauth2/token", $data, false, ["Content-Type: application/x-www-form-urlencoded"] );

        if( empty( $curl["access_token"] ) )
            throw new Exception($curl["error_description"]);

        # set token for functions
        $this->token        = $curl["access_token"];
        
        $return["token"]    = $curl["access_token"];
        $return["user"]     = $this->initUser();
        $return["guilds"]   = $this->getGuilds();

        return $return;
    }

    /**
     * Init user
     */
    protected function initUser() : array
    {
        return $this->sendCurl( $this->baseUrl . "/api/users/@me", [], $this->token, [], "GET" );
    }

    /**
     * Get user guilds
     */
    public function getGuilds() : array
    {
        return $this->sendCurl( $this->baseUrl . "/api/users/@me/guilds", [], $this->token, [], "GET" );
    }
}