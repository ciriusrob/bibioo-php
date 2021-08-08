<?php
/**
 * Created by Robert Wilson.
 * Date: 12/28/2016
 * Time: 1:09 PM
 */

namespace Database;

date_default_timezone_set('Africa/Accra');

require_once realpath('./') . DIRECTORY_SEPARATOR . 'Api' . DIRECTORY_SEPARATOR . 'Config.php';

class Database
{
    private $connection;
    private static $instance;

    private function __construct()
    {
        $this->openConnection();
    }

    public static function getInstance()
    {
        if ( self::$instance == null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     *
     */
    private function openConnection()
    {
        global $_config;

        $this->connection = new \PDO("mysql:host={$_config['DB_HOST']};dbname={$_config['DB']}",
            $_config['DB_USER'],
            $_config['DB_PASSWORD'],
            array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET utf8'));
        $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        $this->connection->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
    }

    /**
     * @param $table
     * @return array|null
     */
    public function find( $table )
    {
        $statement = $this->connection->query("SELECT * FROM `{$table}`");
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $table
     * @param int $start
     * @param int $limit
     * @return array|null
     */
    public function findPaged( $table, $start = 0, $limit = 20, $otherBy = ['id'], $sortDirection = 'DESC' )
    {
        $start = $start > 0 ? --$start : $start;

        $query = "SELECT * FROM `{$table}`";
        $query .= ' ORDER BY ' . implode(",", $otherBy) . ' ' . $sortDirection . ' LIMIT :start,:limit';

        $statement = $this->connection->prepare($query);
        $statement->bindParam(':start', $start, \PDO::PARAM_INT);
        $statement->bindParam(':limit', $limit, \PDO::PARAM_INT);

        $count = $this->connection->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();

        if ( $statement->execute() !== false ) {
            return [
                'data' => $statement->fetchAll(\PDO::FETCH_ASSOC),
                'count' => $count
            ];
        }
        return null;
    }

    /**
     * @param $table
     * @param $id
     * @param string $field
     * @return mixed|null
     */
    public function findById( $table, $id, $field = 'id' )
    {
        $sql = "SELECT * FROM `{$table}` WHERE `{$field}` = ?";
        $statement = $this->connection->prepare($sql);
        if ( $statement->execute([$id]) !== false ) {
            return $statement->fetch(\PDO::FETCH_ASSOC);
        }
        return null;
    }


    /**
     * @param $table
     * @param $data
     * @param bool $updateIfExist
     * @return bool
     */
    public function save( $table, $data, $updateIfExist = false )
    {
        $sql = "INSERT INTO `{$table}` ";
        $sql .= '(' . implode(',', array_keys($data)) . ') VALUES (';
        $record = [];
        foreach ( $data as $key => $value ) {
            $record[':' . $key] = $value;
            $sql .= ":{$key},";
        }
        $sql = rtrim($sql, ',') . ')';

        if ( $updateIfExist ) {
            $sql .= "ON DUPLICATE KEY UPDATE ";
            foreach ( $data as $key => $value ) {
                $sql .= "`{$key}` = VALUES({$key}), ";
            }
            $sql = rtrim($sql, ', ');
        }

        $statement = $this->connection->prepare($sql);
        if ( $statement->execute($record) ) {
            return $this->connection->lastInsertId();
        }

        return false;
    }

    /**
     * @param $table
     * @param $id
     * @param $data
     * @param string $field
     * @return bool
     */
    public function updateRecord( $table, $id, $data, $field = 'id' )
    {
        $sql = "UPDATE `{$table}` SET ";
        $record = [];
        foreach ( $data as $key => $value ) {
            $record[':' . $key] = $value;
            $sql .= "`{$key}` = :{$key}, ";
        }
        $sql = rtrim($sql, ', ');
        $sql .= " WHERE `{$field}` = :{$field}";
        $record[':' . $field] = $id;

        $statement = $this->connection->prepare($sql);
        if ( $statement->execute($record) ) {
            return true;
        }

        return false;
    }

    /**
     * @param $id
     * @param $table
     * @param string $field
     * @return bool
     */
    public function delete( $id, $table, $field = 'id' )
    {
        $sql = "DELETE FROM `{$table}` WHERE `{$field}` = ? LIMIT 1";
        $statement = $this->connection->prepare($sql);
        if ( $statement->execute([$id]) ) {
            return true;
        }
        return false;
    }

    /**
     * @param $sql
     * @param $paramsArray
     * @return mixed|null
     */
    public function rawQuery( $sql, $paramsArray )
    {
        if ( !is_null($paramsArray) ) {
            $statement = $this->connection->prepare($sql);
            $statement->execute($paramsArray);
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result ? $result : null;
        }

        $statement = $this->connection->query($sql);
        $statement->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result : null;

    }

    /**
     * @param $sql
     * @param $paramsArray
     * @return mixed|null
     */
    public function rawQueryAll( $sql, $paramsArray )
    {
        if ( !is_null($paramsArray) ) {
            $statement = $this->connection->prepare($sql);
            $statement->execute($paramsArray);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result ? $result : null;
        }

        $statement = $this->connection->query($sql);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result ? $result : null;

    }
}