<?php

namespace Tobuli\Importers\Device;

use Exception;
use Tobuli\Exceptions\ValidationException;

class CsvImporter extends Importer
{
    protected $header;

    protected $rows;

    public function load($file)
    {
        $source = file_get_contents($file);

        $csv = str_getcsv($source, "\n");

        foreach ($csv as &$row)
            $row = str_getcsv($row, ";");


        $this->header = array_shift($csv);
        $this->rows = $csv;

        return $this;
    }

    public function prepare($data)
    {
        try {
            return array_combine($this->header, $data);
        } catch (Exception $e) {
            throw new ValidationException('Invalid content for csv device import');
        }
    }

    public function getItems()
    {
        return $this->rows;
    }

    public function validFormat()
    {
        if (empty($this->header))
            return false;

        if ( ! is_array($this->header))
            return false;

        if (false === array_search('imei', $this->header))
            return false;

        if (false === array_search('name', $this->header))
            return false;



        return true;
    }
}
