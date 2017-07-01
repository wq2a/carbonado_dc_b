<?php
namespace Cb\Model\Repository;

use Doctrine\DBAL\DBALException;

abstract class BaseRepository {

    protected $conn;
    protected $caches;

    // dataset array with key as action
    abstract public function datasets();

    public function __construct($app, $caches = null)
    {
        $this->app = $app;
        $this->conn = $app['db'];
        #$this->caches = $app['caches'];
        $this->cache_disabled = true;
    }

    public function Action($action, $params = array()) {

        if (isset($this->datasets()[$action]))
        {
            $dataset = $this->datasets()[$action];
            // Func first , then sql , this way the func can use the sql, dataset is passed to func
            if (isset($dataset['func'])) {
                // use reflection since call_user_func only works for static func
                return $this->{$dataset['func']}($params, $dataset);
            }
            // Get data
            return $this->dbQueryBuild($dataset, $params);
        }
        $res = array(
            'status' => 'fail',
            'message'=> "Action: $action not found",
            'data'   => []
        );
        return $res;

    }

    public function dbQueryRow($sql, $args = array(), $opts = [])
    {
       try {
            $row = $this->conn->fetchAssoc($sql, $args);
            return $row;
        } catch(DBALException $e) {
          $msg = "Error Querying row : $sql " . $e->getMessage();
            $this->rx_error_log($msg);
            die($msg);
        }
    }

    public function dbQuery($sql, $args = array(), $withTotals = true)
    {
        $res = array(
            'status' => 'success',
            'message'=> 'OK',
            'data'   => []
        );
        try {
            if (preg_match('/^delete|update|insert/i', $sql)) {
                $res['data']['count'] = $this->conn->executeUpdate($sql, $args);
            } else {
                if($withTotals) {
                    if(!$this->cache_disabled && $this->caches) {
                        $key = md5($sql . json_encode($args));
                        if($this->caches['memcached.totalitems']->exists($key)) {
                            // $totalItems = $this->caches['memcached.totalitems']->fetch($key);
                            // $results['data']  = $this->conn->fetchAll($sql,$args);
                            // $this->caches['filecache.example']->store($key, $totalItems);
                        } else{
                            $res['data']  = $this->conn->fetchAll($sql,$args);
                            // $this->caches['memcached.totalitems']->store($key, $totalItems);
                        }
                    } else {
                        $res['data']  = $this->conn->fetchAll($sql,$args);
                    }
                } else{
                    $res['data'] = $this->conn->fetchAll($sql, $args);
                }
            }
        } catch(DBALException $e) {
            $res['status'] = 'error';
            $res['message'] = '' . $e;
        } finally {
            return $res;
        }
    }

    public function dbQueryBuild($dataset, $params, $withTotals=true) {
        $bind_params = array();
        $sql = $dataset['sql'];
        if (isset($dataset['withTotals'])) {
            $withTotals = $dataset['withTotals'];
        }
        $fields = array_keys($dataset['fields']);
        $sortby = $dataset['sortby'];
        /* These are pass by ref */
        $this->addWhereClause($sql,$params,$fields,$bind_params);
        $this->addOrderClause($sql,$params,$fields,$sortby);
        $this->addLimitClause($sql,$params,$bind_params);
        $res = $this->dbQuery($sql, $bind_params, $withTotals);

        return $res;
    }

    function addOrderClause(&$sql, $params = array(), $fields = array(), $sortby = null)
    {
        if (!isset($params['sort'])) {
            if (!$sortby) {
                return;
            }
            else {
                $params['sort'] = $sortby;
            }
        }
        if (in_array($params['sort'], $fields)) {
            $sql .= " ORDER BY " . $params['sort'];
        }
    }


    // Add where clause
    function addWhereClause(&$sql, $params = array(), $fields = array(), &$bind_params){
        $where_clause = " WHERE ";
        $and = " ";
        if (preg_match('/\bwhere\b/i', $sql) ) {
            $where_clause = " ";
            $and = " and  ";
        }

        foreach($params as $key=>$value){
            if(in_array($key, $fields)){
                if (is_array($value) && count($value) )  {
                    // If array for this this field OR them
                    $or = " ";
                    foreach ($value as $val) {
                        $sql .= $where_clause . $and . $or . $key . " like ? ";
                        $bind_params[] = '%' . $val . '%';
                        $where_clause = " ";
                        $or = " OR ";
                        $and = " ";
                    }
                    $and = " AND ";
                }
                else {
                    $sql .= $where_clause . $and . $key . " like ? ";
                    $bind_params[] = '%' . $value . '%';
                    $where_clause = " ";
                    $and = " AND ";
                }
            }
        }
    }

    // Add limit clause
    function addLimitClause(&$sql, $params, &$bind_params, $page_default=0, $page_len_default=100)
    {
        // default page number 0
        $page = isset($params['page'])?$params['page']-1:$page_default;
        // default page length 100, TODO
        $page_len = isset($params['page_len'])?$params['page_len']:$page_len_default;
        // Len -1 means get them all
        if ($page_len == -1) {
            return;
        }
        $sql .= " limit " . $page*$page_len . " , " .  $page_len;
    }
}
