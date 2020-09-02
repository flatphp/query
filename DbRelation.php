<?php namespace Flatphp\Query;


class DbRelation
{
    const ONE = 'One';
    const MANY = 'Many';

    protected $type;
    /**
     * @var DbQuery
     */
    protected $query;
    protected $foreign_key;
    protected $local_key;
    protected $data = [];
    protected $default = null;

    public function __construct($type, DbQuery $query, $foreign_key, $local_key)
    {
        $this->type = $type;
        $this->query = $query;
        $this->foreign_key = $foreign_key;
        $this->local_key = $local_key;
    }

    public function getLocalKey()
    {
        return $this->local_key;
    }

    public function prepare(array $values)
    {
        if (empty($values)) {
            return;
        }
        // 调用whereXxxIn()
        $where = 'where' . str_replace('_', '', ucwords($this->foreign_key, '_')) .'In';
        $this->query->$where($values);
        $method = 'prepare'. $this->type;
        $this->$method();
    }

    public function get($value)
    {
        return isset($this->data[$value]) ? $this->data[$value] : $this->default;
    }

    protected function prepareMany()
    {
        $this->default = [];
        $k = $this->foreign_key;
        foreach ($this->query->getAll() as $item) { /** @var Model $item */
            $this->data[$item[$k]][] = $item;
        }
    }

    protected function prepareOne()
    {
        $k = $this->foreign_key;
        foreach ($this->query->getAll() as $item) { /** @var Model $item */
            $this->data[$item[$k]] = $item;
        }
    }
}