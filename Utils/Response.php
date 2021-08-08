<?php
/**
 * Created by Robert Wilson.
 * Date: 12/28/2016
 * Time: 1:22 PM
 */

namespace Utils;

use Api\Route;

class Response
{
    private $code;
    private $data;
    private $errors;
    private $pager;
    private $headers;
    private $authorization;
    private $responseMessages = [
        'SUCCESS',
        'FAILED',
    ];

    private $customData = [];
    private $instance;

    private function __construct()
    {
    }

    public static function newInstance( $code, $data, $pager = null, $headers = null, $errors = null )
    {
        $response = new Response();
        $response->code = $code;
        $response->data = $data;
        $response->errors = $errors;
        $response->pager = $pager;
        $response->headers = $headers;
        $instance = $response;
        return $response;
    }

    public function withAuthorization( $token )
    {
        $this->authorization = $token;
        return $this;
    }

    public function setCustomDataProperty( $key, $value )
    {
        $this->customData[$key] = $value;
        return $this;
    }

    public function render()
    {
        if ( is_null($this->code) ) throw new \Exception('Code required');

        header('X-XSS-Protection: 1');
        header('X-Frame-Options: SAMEORIGIN');
        header('Content-Type: application/json');

        if ( !is_null($this->pager) && array_key_exists('total', $this->pager) ) {
            $this->pager['total'] = intval($this->pager['total']);
        }

        if ( !is_null($this->pager) && array_key_exists('page', $this->pager) ) {
            $this->pager['page'] = intval($this->pager['page']);
        }

        if ( !is_null($this->pager) && array_key_exists('limit', $this->pager) ) {
            $this->pager['limit'] = intval($this->pager['limit']);
        }

        $response = [
            'code' => $this->code,
            'message' => $this->responseMessages[$this->code],
            'data' => $this->data,
            'pager' => $this->pager
        ];

        if ( !empty( $this->customData ) ) {
            foreach ( $this->customData as $key => $value ) {
                $response['data'][$key] = $value;
            }
        }

        if ( !is_null($this->errors) ) {
            $response['errors'] = $this->errors;
        }

        echo json_encode($response, JSON_NUMERIC_CHECK);
        exit;
    }
}
