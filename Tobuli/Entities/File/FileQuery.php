<?php

namespace Tobuli\Entities\File;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Eloquent;

class FileQuery
{
    protected $model;

    protected $entity;

    protected $directory;

    public function __construct(FileEntity $model, Eloquent $entity)
    {
        $this->model = $model;

        $this->entity = $entity;

        $this->directory = $model->getDirectory($entity);
    }

    public function all()
    {
        $files = File::files($this->directory);

        return $this->buildCollection($files);
    }

    public function find($filename)
    {
        $file = $this->directory . '/' . $filename;

        if (!File::exists($file)) return 'File does not exists';

        return $this->newModelInstance($file);
    }

    public function orderByDate($order = 'desc')
    {
        $files = File::files($this->directory);

        $in_date_order = File::orderByDate($files, $order);

        return $this->buildCollection($in_date_order);
    }

    private function buildCollection($files)
    {
        $collection = new Collection();

        foreach ($files as $file) {
            $instance = $this->newModelInstance($file);
            $collection->push($instance);
        }

        return $collection;
    }

    private function newModelInstance($file)
    {
        return new $this->model($file);
    }
}