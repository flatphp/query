<?php namespace Flatphp\Query;


use Lightdb\Conn;
use Lightdb\DB;
use Lightdb\Query;


abstract class DbQuery
{
    protected $db;
    protected $table;
    protected $model;

    // 关系
    protected $relations = [];

    protected $auto_increment = true;
    protected $primary_key = 'id';
    protected $created_at = 'created_at';
    protected $updated_at = 'updated_at';
    protected $deleted_at = ''; // 软删除

    /**
     * @var Conn
     */
    protected $conn;
    /**
     * @var Query
     */
    protected $query;

    public function __construct()
    {
        $this->conn = DB::conn($this->db);
        $this->query = $this->conn->query()->table($this->table);
    }

    protected function hasMany($name, DbQuery $query, $foreign_key, $local_key = 'id')
    {
        return $this->hasRelation(DbRelation::MANY, $name, $query, $foreign_key, $local_key);
    }

    protected function hasOne($name, DbQuery $query, $foreign_key, $local_key = 'id')
    {
        return $this->hasRelation(DbRelation::ONE, $name, $query, $foreign_key, $local_key);
    }

    protected function hasRelation($type, $name, DbQuery $query, $foreign_key, $local_key = 'id')
    {
        $this->relations[$name] = new DbRelation($type, $query, $foreign_key, $local_key);
        return $this;
    }


    protected function draw(array $data)
    {
        if (!$this->model) {
            return $data;
        }
        $name = $this->model;
        $model = new $name($data);
        foreach ($this->relations as $name => $relation) { /** @var DbRelation $relation */
            $model->$name = $relation->get($data[$relation->getLocalKey()]);
        }
        return $model;
    }

    protected function fetchRow()
    {
        return $this->query->fetchRow();
    }

    protected function fetchAll()
    {
        return $this->query->fetchAll();
    }

    public function whereId($value)
    {
        $this->query->where($this->primary_key .'=?', $value);
        return $this;
    }

    public function sortDESC()
    {
        $this->query->orderBy($this->primary_key .' DESC');
        return $this;
    }

    public function page($page, $page_size = 20)
    {
        $this->query->page($page, $page_size);
        return $this;
    }

    public function getOne()
    {
        $data = $this->fetchRow();
        if (!$data) {
            return null;
        }
        foreach ($this->relations as $relation) { /** @var DbRelation $relation */
            $relation->prepare([$data[$relation->getLocalKey()]]);
        }
        return $this->draw($data);
    }

    public function getAll()
    {
        $data = $this->fetchAll();
        if (empty($data)) {
            return [];
        }
        foreach ($this->relations as $relation) { /** @var DbRelation $relation */
            $relation->prepare(array_column($data, $relation->getLocalKey()));
        }
        return array_map(function ($item) {
            return $this->draw($item);
        }, $data);
    }

    public function getId()
    {
        return $this->query->select($this->primary_key)->fetchOne();
    }

    public function count()
    {
        return $this->query->count();
    }

    public function exists()
    {
        return $this->query->exists();
    }


    public function insert(Model $model)
    {
        return $this->performInsert($model->toArray());
    }

    public function update(Model $model)
    {
        return $this->performUpdate($model->toArray());
    }

    public function delete()
    {
        return $this->performDelete();
    }

    public function drop()
    {
        return $this->query->delete();
    }

    public function preview()
    {
        $preview = new DbPreview($this->query);
        $preview->created_at = $this->created_at;
        $preview->updated_at = $this->updated_at;
        $preview->deleted_at = $this->deleted_at;
        $preview->datetime = $this->getDatetime();
        return $preview;
    }

    protected function performInsert(array $data)
    {
        $datetime = $this->getDatetime();
        if ($this->created_at) {
            $data['created_at'] = $datetime;
        }
        if ($this->updated_at) {
            $data['updated_at'] = $datetime;
        }
        $res = $this->query->insert($data);
        if (!$res) {
            return false;
        }
        if ($this->auto_increment) {
            return $this->conn->getLastInsertId();
        } elseif (isset($data[$this->primary_key])) {
            return $data[$this->primary_key];
        } else {
            return true;
        }
    }

    protected function performUpdate(array $data)
    {
        if ($this->updated_at) {
            $data['updated_at'] = $this->getDatetime();
        }
        return $this->query->update($data);
    }

    protected function performDelete()
    {
        if ($this->deleted_at) {
            // 软删除
            return $this->query->update(['deleted_at' => $this->getDatetime()]);
        } else {
            return $this->query->delete();
        }
    }

    /**
     * get current datetime
     * @return string
     */
    protected function getDatetime()
    {
        return date('Y-m-d H:i:s');
    }
}