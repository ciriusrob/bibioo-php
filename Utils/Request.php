<?php
/**
 * Created Robert Wilson.
 * Date: 01/09/2017
 * Time: 1:08 AM
 */

namespace Utils;


class Request
{
    private $params = [];

    private $headers = [];

    private $payload = [];

    private static $instance;

    /**
     * @param $key
     * @param $value
     */
    protected function setParam( $key, $value )
    {
        $this->params[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getParam( $key )
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $payload
     */
    protected function setPayload( $payload )
    {
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return !empty($this->payload) ? $this->payload : null;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        if ( !function_exists('getallheaders') ) {
            foreach ( $_SERVER as $name => $value ) {
                if ( substr($name, 0, 5) == 'HTTP_' ) {
                    $this->headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }
        else {
            $this->headers = getallheaders();
        }

        return $this->headers;
    }

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        if ( is_null(self::$instance) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}