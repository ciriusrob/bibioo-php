<?php
/**
 * Created by IntelliJ IDEA.
 * User: Robert
 * Date: 04/03/2017
 * Time: 9:30 PM
 */

namespace Utils;


class Logger
{
    const DELETED = 1;
    const UPDATED = 2;
    const CREATED = 3;
    const LOGIN_ATTEMPT = 4;

    private $files = array(
        'error' => 'error.log',
        'warning' => 'warning.log',
        'critical' => 'critical.log',
        'success' => 'success.log',
        'general' => 'general.log',
        'login' => 'login.log',
        'logout' => 'logout.log',
        'update' => 'update.log',
        'delete' => 'delete.log',
        'saved' => 'saved.log',
        'dbError' => 'db.error.log'
    );
    private $warning = array();
    private $fileHandle = null;
    private $flag = false;
    private static $instance;
    const DS = DIRECTORY_SEPARATOR;

    public function write( $file = '', $msg = array(), $skipLog = false )
    {

        $logMsg = '';
        if ( is_array($msg) && !empty($msg) ) {
            $logMsg = '"' . array_shift(array_keys($msg)) . '"  "' . array_shift(array_values($msg)) . '"';
        }
        $this->setAndOpenFile($file);
        if ( $this->isLoggable() ) {
            $logDate = strftime('%a %d %b, %Y Time: %I:%M:%S %p', time());
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            fwrite($this->fileHandle, "{$logMsg} | {$logDate} | " .
                "IP: " . $_SERVER['REMOTE_ADDR'] . " | " . urldecode($_SERVER['REQUEST_URI']) . ' | ' . $userAgent . "\r\n");
            fclose($this->fileHandle);
            $this->flag = false;
        }
    }

    private function setAndOpenFile( $logType = '' )
    {
        $file = realpath('./') . Logger::DS . '_Tmp' . Logger::DS . $this->files[$logType];

        if ( !file_exists($file) ) {
            try {
                touch($file, time());
                $this->flag = true;
                $this->fileHandle = fopen($file, 'a');
            }
            catch ( \Exception $e ) {
                print $e->getMessage();
            }
        }
        else {
            if ( is_writable($file) ) {
                $this->flag = true;
                $this->fileHandle = fopen($file, 'a');
            }
            else {
                $this->flag = false;
            }
        }
    }

    public function logAction( $logType, array $data, $skipLog = false )
    {

        $this->setAndOpenFile($logType);
        if ( $this->isLoggable() ) {
            $param = "------------------------ BEGIN PAYLOAD ---------------------------\r\n";
            $logDate = strftime('%a %d %b, %Y Time: %I:%M:%S %p', time());
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            foreach ( $data as $key => $val ) {
                $param .= "{$key} : {$val} | DATE: {$logDate} | " . "From IP: " . $_SERVER['REMOTE_ADDR'] . ' | ' . $userAgent . "\r\n";
            }
            $param .= "------------------------ END PAYLOAD ---------------------------\r\n";
            fwrite($this->fileHandle, $param);
            fclose($this->fileHandle);
            $this->flag = false;
        }
    }

    private function isLoggable()
    {
        return $this->flag;
    }


    public static function getInstance()
    {
        if ( is_null(self::$instance) ) {
            self::$instance = new self();
            return self::$instance;
        }
        return self::$instance;

    }
}