<?php

class Model
{
    protected static $connection = null;
    protected $table = '';
    private $config = null;

    public function __construct($table = '')
    {
        $this->config = Config::load('mysql');
        if (!self::$connection) $this->getConnection();
        if (!$table && !$this->table) throw new Exception('need table name');
        $this->table = $this->config['prefix'].($table ? $table : $this->table);
    }

    public function insert ($data)
    {
        $sql = 'INSERT INTO `%s` (%s) VALUES (%s)';
        $keys = array_keys($data);
        foreach ($keys as &$key) {
            $key = '`'.$key.'`';
        }
        $values = array_values($data);
        foreach ($values as &$value) {
            $value = '"'.self::$connection->real_escape_string($value).'"';
        }
        $query = sprintf($sql, $this->table, implode(', ', $keys), implode(', ', $values));
        $this->query($query);
        return $this->insertId();
    }

    public function delete ($where)
    {
        $sql = 'DELETE FROM `%s` WHERE %s';
        $query = sprintf($sql, $this->table, $this->buildWhere($where));
        $this->query($query);
        return $this->affectedRows();
    }

    public function update ($data, $where)
    {
        $set = array();
        foreach ($data as $key => $val) {
            $set[] = sprintf(
                '`%s` = "%s"',
                $key,
                self::$connection->real_escape_string($val)
            );
        }
        $sql = 'UPDATE `%s` SET %s WHERE %s';
        $query = sprintf($sql, $this->table, implode(', ', $set), $this->buildWhere($where));
        $this->query($query);
        return $this->affectedRows();
    }

    public function select ($column = '*', $where = null, $limit = null, $orderBy = null, $groupBy = null)
    {
        $sql = 'SELECT %s FROM `%s`';
        $column = $this->buildColumn($column);
        $query = sprintf($sql, $column, $this->table);
        if (!empty($where)) $query .= ' WHERE '.$this->buildWhere($where);
        if (!empty($groupBy)) $query .= ' GROUP BY '.$groupBy;
        if (!empty($orderBy)) $query .= ' ORDER BY '.$orderBy;
        if (!empty($limit)) $query .= ' LIMIT '.$limit;
        $result = $this->query($query);
        $res = array();
        while ($row = $result->fetch_assoc()) $res[] = $row;
        return $res;
    }

    public function selectOne ($column = '*', $where = null, $offset = 0)
    {
        $sql = 'SELECT %s FROM `%s`';
        $column = $this->buildColumn($column);
        $query = sprintf($sql, $column, $this->table);
        if (!empty($where)) $query .= ' WHERE '.$this->buildWhere($where);
        if ($offset) {
            $query .= " LIMIT {$offset}, 1";
        } else {
            $query .= ' LIMIT 1';
        }
        return $this->query($query)->fetch_assoc();
    }

    public function count ($where)
    {
        $sql = 'SELECT COUNT(*) as count FROM `%s`';
        $query = sprintf($sql, $this->table);
        if (!empty($where)) $query .= ' WHERE '.$this->buildWhere($where);
        $res = $this->query($query)->fetch_assoc();
        return (int) $res['count'];
    }

    public function countSelect (
        $column = '*',
        $where = null,
        $pageNow = 1,
        $pageSize = 20,
        $orderBy = null,
        $groupBy = null
    ) {
        $count = $this->count($where);
        $list = $this->select($column, $where, (($pageNow - 1) * $pageSize).', '.$pageSize, $orderBy, $groupBy);
        $result = array(
            'total' => $count,
            'pageNow' => $pageNow,
            'pageSize' => $pageSize,
            'list' => $list
        );
        return $result;
    }

    public function query ($query)
    {
        Logger::debug("mysql query: {$query}");
        $res = self::$connection->query($query);
        if (!$res) {
            Logger::error('mysql query error: '.self::$connection->error);
            throw new Exception('Error in db query');
        }
        return $res;
    }

    public function affectedRows ()
    {
        return self::$connection->affected_rows;
    }

    public function insertId ()
    {
        return self::$connection->insert_id;
    }

    protected function buildColumn ($column)
    {
        if (is_array($column)) {
            $arr = array();
            foreach ($column as $item) {
                $arr[] = "`{$item}`";
            }
            return implode(', ', $arr);
        } else {
            return $column;
        }
    }

    protected function buildWhere ($where)
    {
        $res = array();
        foreach ($where as $key => $val) {
            if (is_array($val)) {
                $op = strtoupper($val[0]);
                if ($op == 'BETWEEN' || $op == 'NOT BETWEEN') {
                    $tmp = array();
                    foreach ($val[1] as $item) {
                        $tmp[] = '"'.self::$connection->real_escape_string($item).'"';
                    }
                    $val = implode(' AND ', $tmp);
                } elseif ($op == 'IN' || $op == 'NOT IN') {
                    $tmp = array();
                    foreach ($val[1] as $item) {
                        $tmp[] = '"'.self::$connection->real_escape_string($item).'"';
                    }
                    $val = '('.implode(', ', $tmp).')';
                } else {
                    $val = '"'.self::$connection->real_escape_string($val[1]).'"';
                }
                $res[] = sprintf(
                    '`%s` %s %s',
                    $key,
                    $op,
                    $val
                );
            } else {
                $res[] = sprintf(
                    '`%s` = "%s"',
                    $key,
                    self::$connection->real_escape_string($val)
                );
            }
        }
        return implode(' AND ', $res);
    }

    protected function getConnection ()
    {
        $connection = new Mysqli(
            $this->config['host'],
            $this->config['user'],
            $this->config['password'],
            $this->config['database'],
            $this->config['port']
        );
        if ($connection->connect_error) {
            Logger::error('mysql error: '.$connection->connect_error);
            throw new Exception('Fail to connect mysql');
        }
        $connection->query("SET NAMES {$this->config['charset']} COLLATE {$this->config['collate']}");
        self::$connection = $connection;
    }
}