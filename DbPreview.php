<?php namespace Flatphp\Query;


use Lightdb\Query;

class DbPreview
{
    protected $query;
    public $created_at = 'created_at';
    public $updated_at = 'updated_at';
    public $deleted_at = '';
    public $datetime = '';

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function getOne()
    {
        return $this->query->previewSelect();
    }

    public function getAll()
    {
        return $this->query->previewSelect();
    }

    public function insert(Model $model)
    {
        $data = $model->toArray();
        if ($this->created_at) {
            $data['created_at'] = $this->datetime;
        }
        if ($this->updated_at) {
            $data['updated_at'] = $this->datetime;
        }
        return $this->query->previewInsert($data);
    }

    public function update(Model $model)
    {
        $data = $model->toArray();
        if ($this->updated_at) {
            $data['updated_at'] = $this->datetime;
        }
        return $this->query->previewUpdate($data);
    }

    public function delete()
    {
        if ($this->deleted_at) {
            // 软删除
            return $this->query->previewUpdate(['deleted_at' => $this->datetime]);
        } else {
            return $this->drop();
        }
    }

    public function drop()
    {
        return $this->query->previewDelete();
    }
}