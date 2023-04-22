<?php
/**
 * Created by PhpStorm.
 * User: antanas
 * Date: 18.3.28
 * Time: 10.50
 */

namespace Tobuli\Services;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Tobuli\Services\FractalSerializers\DataArraySerializer;

class FractalTransformerService {

    protected $fractalManager;

    /**
     * @var TransformerAbstract
     */
    protected $transformer;

    protected $data;

    public function __construct(Manager $manager) {
        $this->fractalManager = $manager;
        $this->fractalManager->setSerializer(new DataArraySerializer());
    }


    /**
     * @param TransformerAbstract $transformerClass
     * @return FractalTransformerService
     */
    public function setTransformer($transformerClass) {
        $this->transformer = new $transformerClass();
        return $this;
    }

    /**
     * @param mixed $data
     * @return FractalTransformerService
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function item($data, $transformerClass) {
        $this->setTransformer($transformerClass);
        $this->setData($data);

        $transformedData = new Item($this->data, $this->transformer);
        $transformedData = $this->fractalManager->createData($transformedData);

        return $transformedData;
    }

    public function collection($data, $transformerClass) {
        $this->setTransformer($transformerClass);
        $this->setData($data);

        $transformedData = new Collection($this->data, $this->transformer);
        $transformedData = $this->fractalManager->createData($transformedData);
        return $transformedData;
    }

    public function paginate($data, $transformerClass) {
        $this->setTransformer($transformerClass);
        $this->setData($data->getCollection());

        $transformedData = new Collection($this->data, $this->transformer);
        $transformedData->setPaginator(new IlluminatePaginatorAdapter($data));
        $transformedData = $this->fractalManager->createData($transformedData);
        return $transformedData;
    }

}