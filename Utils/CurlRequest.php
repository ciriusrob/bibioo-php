<?php
/**
 * Created by IntelliJ IDEA.
 * User: Robert
 * Date: 01/22/2017
 * Time: 6:02 PM
 */

namespace Utils;


class CurlRequest
{

    private static $timeOut = 0;
    private static $connectionTimeOut = 0;

    public static function get($url)
    {
        $channel = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_CONNECTTIMEOUT => self::$connectionTimeOut,
            CURLOPT_TIMEOUT => self::$timeOut,
            CURLOPT_HTTPHEADER => ['Expect:'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ];

        curl_setopt_array($channel, $options);

        curl_exec($channel);

        curl_close($channel);
    }

    public static function post($url, $postData, $returnTransfer = false)
    {
        $channel = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => $returnTransfer,
            CURLOPT_CONNECTTIMEOUT => self::$connectionTimeOut,
            CURLOPT_TIMEOUT => self::$timeOut,
            CURLOPT_HTTPHEADER => ['Expect:'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_POSTFIELDS => $postData
        ];

        curl_setopt_array($channel, $options);

        if ( $returnTransfer ) {
            $result = curl_exec($channel);
            curl_close($channel);
            return $result;
        }

        curl_exec($channel);

        curl_close($channel);
    }

}