<?php
/**
 * Created Robert Wilson.
 * Date: 12/30/2016
 * Time: 6:51 AM
 */

namespace Api;

use Database\Database;
use Utils\CurlRequest;
use Utils\Request;
use Utils\Utils;

class Route
{
    private static $callbacks = [];
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    private static $authorization;
    private static $badCharacter = array('\'', '--', '/*', '*/');
    private static $badChars = "/('|--|\/\*|\*\/|\*?(order\*?by|select)\s?\d+)/i";

    private static $exceptions = [
        '/authenticate'
    ];

    /**
     * @param $url
     * @param $callback
     * @param $requestMethod
     */
    private static function submit( $url, $callback, $requestMethod )
    {
        array_push(self::$callbacks, [
            'requestMethod' => $requestMethod,
            'callback' => $callback,
            'url' => $url,
        ]);

    }

    /**
     * @param $url
     * @param $callback
     */
    public static function get( $url, $callback )
    {
        self::submit($url, $callback, self::GET);
    }

    /**
     * @param $url
     * @param $callback
     */
    public static function delete( $url, $callback )
    {
        self::submit($url, $callback, self::DELETE);
    }

    /**
     * @param $url
     * @param $callback
     */
    public static function post( $url, $callback )
    {
        self::submit($url, $callback, self::POST);
    }

    /**
     * @param $url
     * @param $callback
     */
    public static function put( $url, $callback )
    {
        self::submit($url, $callback, self::PUT);
    }

    /**
     *
     */
    public static function start()
    {
        $currentUrl = isset( $_GET['url'] ) ? '/' . $_GET['url'] : '/';

        $serverRequestMethod = strtoupper($_SERVER['REQUEST_METHOD']);

        $currentUrlAsArray = explode('/', $currentUrl);

        foreach ( self::$callbacks as $key => $callback ) {

            $url = $callback['url'];
            $callbackMethod = $callback['callback'];
            $requestMethod = $callback['requestMethod'];

            if ( $requestMethod !== $serverRequestMethod ) continue;

            $explodedUrl = explode('/', $url);

            if ( self::compareArrayValues($explodedUrl, $currentUrlAsArray) ) {

                preg_match_all("~\:\w+~", $url, $matches);

                $params = [];

                list( $matches, $params ) = self::getParams($matches, $currentUrlAsArray, $params);

                for ( $i = 0; $i < count($currentUrlAsArray); $i++ ) {
                    $url = preg_replace("~\:\w+~", $currentUrlAsArray[$i], $currentUrl);
                }

                if ( $url === $currentUrl ) {

                    self::validate($callback['url']);

                    switch ( $requestMethod ) {
                        case self::GET:
                        case self::DELETE:
                            self::handleGetOrDelete($callbackMethod, $params);
                            break;
                        case self::POST:

                            $payload = self::preparePayload(json_decode(file_get_contents('php://input'), true));

                            self::handlePost($callbackMethod, $payload);
                            break;
                        case self::PUT:
                            $payload = self::preparePayload(json_decode(file_get_contents('php://input'), true));

                            self::handlePut($callbackMethod, $payload, $params);
                            break;
                    }
                    break;
                }

                if ( $url !== $currentUrl && $key == count(self::$callbacks) - 1 ) {
                    header('HTTP/1.0 404 Not Found');
                    exit;
                }
            }

            if ( $url !== $currentUrl && $key == count(self::$callbacks) - 1 ) {
                header('HTTP/1.0 404 Not Found');
                exit;
            }
        }
    }

    /**
     * @param array $arrayLeft
     * @param array $arrayRight
     * @return bool
     */
    private static function compareArrayValues( array $arrayLeft, array $arrayRight )
    {
        if ( count($arrayLeft) !== count($arrayRight) ) return false;

        for ( $i = 0; $i < count($arrayLeft); $i++ ) {
            if ( preg_match('/:/', $arrayLeft[$i]) ) {
                $arrayLeft[$i] = $arrayRight[$i];
                if ( $arrayLeft[$i] !== $arrayRight[$i] ) return false;
            }

            if ( $arrayLeft[$i] !== $arrayRight[$i] ) return false;
        }

        return true;
    }

    /**
     * @param $url
     */
    public static function validate( $url )
    {
        $headers = self::getHeaders();

        if ( is_null($headers) ) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
    }

    /**
     * @return mixed
     */
    public static function getAuthorization()
    {
        return self::$authorization;
    }

    /**
     * @param $matches
     * @param $currentUrlAsArray
     * @param $params
     * @return array
     */
    private static function getParams( $matches, $currentUrlAsArray, $params )
    {
        
        if ( !empty( $matches[0] ) ) {

            if ( count($matches[0]) == 1 ) {

                if ( isset( $params['id'] ) ) {
                    $params['id'] = $currentUrlAsArray[count($currentUrlAsArray) - 1];
                }
                else {
                    $params[substr($matches[0][0], 1)] = floatval($currentUrlAsArray[count($currentUrlAsArray) - 1]) > 0
                        ? floatval($currentUrlAsArray[count($currentUrlAsArray) - 1])
                        : $currentUrlAsArray[count($currentUrlAsArray) - 1];
                }
                return [$matches, $params];
            }
            else {
                for ( $j = 0; $j < count($matches[0]); $j++ ) {

                    $index = array_search(substr($matches[0][$j], 1), $currentUrlAsArray);

                    $params[substr($matches[0][$j], 1)] = floatval($currentUrlAsArray[$index + 1]) > 0 ? floatval($currentUrlAsArray[$index + 1]) : $currentUrlAsArray[$index + 1];
                }
                return [$matches, $params];
            }
        }

        $params = array_merge($params, self::getParam());

        return [$matches, $params];
    }

    private static function getParam()
    {
        $queryString = $_SERVER['QUERY_STRING'];
        $params = [];
        if (!empty($queryString) && !preg_match('/url=favicon\.ico/i', $queryString)) {
            $mainURL = '';

            if (count($_GET) > 1) {

                if (isset($_GET['url'])) {
                    $mainURL = rtrim($_GET['url'], '/');
                    unset($_GET['url']);
                }

                foreach ($_GET as $key => $value) {
                    $params[$key] = filter_var($value, FILTER_SANITIZE_ENCODED);
                }
            }
            elseif (count($_GET) == 1 && isset($_GET['url'])) {
                $mainURL = rtrim($_GET['url'], '/');
            }
            if (!empty($mainURL)) {
                $raw = array_map(function($p) {
                    if ( preg_match('/[-]/', $p)) {
                        return preg_replace( self::$badChars, '', lcfirst( Utils::camelize( $p ) ) );
                    }
                    else {
                        return preg_replace(self::$badChars, '' , $p );
                    }
                }, explode('/', $mainURL));

                if (count($raw) > 2) {

                    $raw[0] = lcfirst(Utils::camelize($raw[0]));
                    $raw[1] = lcfirst(Utils::camelize($raw[1]));

                    $c = count($raw);
                    for ($i = 0; $i < $c; $i++) {
                        $key = $raw[$i];

                        if ( isset ($raw[$i + 1]) ) {
                            if ( is_numeric($key) ) {
                                $params['id'] = $key;
                            }
                            else {
                                $i++;
                                $params[$key] = $raw[$i];
                            }
                        }
                    }
                }
                elseif( count($raw) == 2 ) {
                    if ( preg_match('/[0-9]/', $raw[1]) ) {
                        $params['id'] = $raw[1];
                    }
                }
            }

        }

        return $params;
    }

    /**
     * @param $callbackMethod
     * @param $params
     */
    private static function handleGetOrDelete( $callbackMethod, $params )
    {
        if ( !is_null($callbackMethod) ) {

            if ( !empty( $params ) ) {
                call_user_func_array($callbackMethod, $params);
            }
            else {
                call_user_func($callbackMethod);
            }
        }
    }

    /**
     * @param $callbackMethod
     * @param $payload
     */
    private static function handlePost( $callbackMethod, $payload )
    {
        if ( !is_null($callbackMethod) ) {

            if ( !is_null($payload) ) {
                call_user_func_array($callbackMethod, [$payload]);
            }
            else {
                call_user_func($callbackMethod);
            }
        }
    }

    /**
     * @param $callbackMethod
     * @param $payload
     * @param $params
     */
    private static function handlePut( $callbackMethod, $payload, $params )
    {
        if ( !is_null($callbackMethod) ) {

            if ( !is_null($payload) ) {
                call_user_func_array($callbackMethod, array_merge([$payload], $params));
            }
            else {
                call_user_func($callbackMethod);
            }
        }
    }

    /**
     * @return array|false
     */
    public static function getHeaders()
    {
        if ( !function_exists('getallheaders') ) {

            $headers = [];
            foreach ( $_SERVER as $name => $value ) {
                if ( substr($name, 0, 5) == 'HTTP_' ) {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        }

        return getallheaders();
    }

    /**
     * @param $payloadRaw
     * @return mixed
     */
    private static function preparePayload( $payloadRaw )
    {
        $payload = [];
        foreach ( $payloadRaw as $pKey => $pValue ) {
            $payload[strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $pKey))] = $pValue;
        }
        return $payload;
    }

}