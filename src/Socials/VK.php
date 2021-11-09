<?php 

namespace Flamesone\Social\Socials;

use Exception;

class VK
{ 
    /**
     * @var string $red_url = VK redirect site
     */
    protected $red_url;

    /**
     * @var int $client_id = Client id, from VK application
     */
    protected $client_id;
    
    /**
     * @var string Client secret
     */
    protected $client_secret;

    /**
     * @var string VK URL
     */
    protected $vk_url = "http://oauth.vk.com/authorize";

    /**
     * @var array Params
     */
    protected $params;

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

        $this->params = [
            'client_id'     => $arr["client_id"], 
            'redirect_uri'  => $arr["red_url"], 
            'response_type' => "code"
        ];
    }

    /**
     * Generate URL
     */
    protected function generateURL() : string
    {
        $url = sprintf("%s?%s", $this->vk_url, urldecode(http_build_query($this->params)));
        return $url;
    }

    /**
     * Redirect to VK Oauth
     */
    public function auth()
    {
        !isset( $_GET["code"] ) && header("Location: {$this->generateURL()}");

        return true;
    }

    /**
     * Handle
     */
    public function Handle()
    {
        if( isset( $_GET["code"] ) && !empty( $_GET["code"] ) )
        {
            $params = [
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'code'          => $_GET['code'],
                'redirect_uri'  => $this->red_url
            ];

            $url = sprintf("https://oauth.vk.com/access_token?%s", urldecode(http_build_query($params)));
            $token = json_decode(file_get_contents( $url ), true);

            if( isset( $token["access_token"] ) )
            {
                $params = [
                    'uids'          => $token['user_id'],
                    'fields'        => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
                    'access_token'  => $token['access_token'],
                    'v'             => '5.131'
                ];
        
                $urlinfo = sprintf("https://api.vk.com/method/users.get?%s", urldecode(http_build_query($params)));
                $userInfo = json_decode(file_get_contents( $urlinfo ), true);

                if (isset( $userInfo["response"][0] ))
                    return $userInfo["response"][0];
                
                throw new Exception("User get error");
            }
            throw new Exception("Token error");
        }
        throw new Exception("Code not found");
    }
}