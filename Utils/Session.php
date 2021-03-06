<?php
/**
 * Created by Robert Wilson.
 * Date: 12/30/2016
 * Time: 5:44 PM
 */

namespace Utils;

use \Api\Config;

class Session
{
    private static $instance;

    private function __construct()
    {
        ob_start();
        $this->start();
    }

    public function start()
    {
        if ( empty( $_SESSION ) && !isset( $_SESSION ) ) {
            session_name($_config['SESSION_NAME']);
            ini_set('session.cookie_httponly', $_config['SESSION_COOKIE_HTTP_ONLY']);
            session_start();
        }
    }

    /**
     * @param $key
     * @return string|null
     */
    public function get( $key )
    {
        return isset( $_SESSION[$key] ) ? $_SESSION[$key] : null;
    }

    /**
     * @param $key
     * @param $value
     */
    public function put( $key, $value )
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param int $len
     * @param bool $nonAlpha
     * @return string
     */
    public static function generateRandomString( $len = 8, $nonAlpha = true )
    {
        $na = '!@#%&*~?$^()+-,';
        $base = 'ABCDEFGHKLMNPQRSTWXYZ23456789abcdefghijkmnpqrstuvwxyz';
        if ( $nonAlpha ) {
            $base .= $na;
        }
        $max = strlen($base) - 1;
        $passcode = '';
        mt_srand((double) microtime() * 1000000);
        while ( strlen($passcode) < $len ) {
            $passcode .= $base{mt_rand(0, $max)};
        }
        return $passcode;
    }

    /**
     * @return Session
     */
    public static function getInstance()
    {
        if ( self::$instance == null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}