<?php

namespace Tobuli\Importers\Device;

use Tobuli\Exceptions\ValidationException;

class ImporterManager
{
    protected $importers = [
        'csv' => 'Tobuli\Importers\Device\CsvImporter',
    ];

    public function getImporter($name)
    {
        return new $this->importers[$name]();
    }

    public function resolve($file)
    {
        foreach ($this->importers as $name => $class) {
            $importer = $this->getImporter($name);

            if ($importer->load($file)->validFormat()) {
                return $importer;
            }
        }

        throw new ValidationException(['id' => 'File format incorrect']);
    }

    public function import($file)
    {
        $importer = $this->resolve($file);

        $importer->load($file)->import();
    }
}
