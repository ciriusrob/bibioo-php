<?php
/**
 * Created by Robert Wilson.
 * User: Robert
 * Date: 12/28/2016
 * Time: 1:27 PM
 */

namespace Api;


class Exception extends \Exception
{
    function __construct($message, $code, Exception $previous)
    {
        parent::__construct($message, $code, $previous);
    }
}