<?php

namespace Flamesone\Social;

use Exception;

class Social
{
    /**
     * @var array Social auth data
     */
    protected $data = [];

    /**
     * @var array classes cache
     */
    protected $cache = [];

    /**
     * @var array Class pathes
     */
    protected $pathes = [
        "discord" => "Flamesone\Social\Socials\Discord",
        "vk"      => "Flamesone\Social\Socials\VK",
        "steam"   => "Flamesone\Social\Socials\Steam",
    ];

    /**
     * Construct
     */
    public function __construct( array $params )
    {
        $this->data = $params;
    }
    
    /**
     * Social is exists
     */
    public function existsSocial( string $name ) : bool
    {
        return isset( $this->data[ $name ] );
    }

    /**
     * Add social to array
     * 
     * class param is required
     */
    public function addSocial( string $name, array $config ) : void
    {
        !$this->existsSocial( $name ) && $this->data[ $name ] = $config;
    }
    
    /**
     * Get social data
     */
    protected function getSocialData( string $key ) : array
    {
        return $this->data[$key];
    }

    /**
     * Class cache
     */
    protected function putCache( string $key, $class )
    {
        $this->cache[$key] = $class;
    }

    /**
     * If not isset, put
     */
    protected function cacheHandle( string $key, $classpath )
    {
        $class = !isset( $this->cache[$key] ) ? new $classpath() : $this->getCache($key);

        !isset( $this->cache[$key] ) && $this->putCache($key, $class);

        return $class;
    }

    /**
     * Get cache
     */
    protected function getCache( string $key )
    {
        return $this->cache[$key];
    }

    /**
     * Auth handle for key
     */
    public function auth( string $key )
    {
        if( $this->existsSocial( $key ) )
        {
            if( isset( $this->pathes[ $key ] ) )
            {
                $class = $this->cacheHandle( $key, $this->pathes[$key] );
                $class->setParams( $this->getSocialData($key) );
                return $class->Auth();
            }
            throw new Exception( "Custom social auth is not suppored" );
        }
        throw new Exception( "Social $key not found!" );
    }

    /**
     * Auth handle
     */
    public function handle( string $key, $callback = null )
    {
        if( $this->existsSocial( $key ) )
        {
            if( isset( $this->pathes[ $key ] ) )
            {
                $class = $this->cacheHandle( $key, $this->pathes[$key] );
                $class->setParams( $this->getSocialData($key) );
                $handle = $class->Handle();
                $callback && $callback( $handle );
                return $handle;
            }
            throw new Exception( "Custom social handle is not suppored" );
        }
        throw new Exception( "Social $key not found!" );
    }
    
    /**
     * Remove custom socials
     */
    public function removeSocial( string $name ) : void
    {
        if( isset( $this->data[ $name ] ) )
            unset( $this->data[ $name ] );
    }
}