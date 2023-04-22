<?php
use Illuminate\Support\Facades\File;
use Tobuli\Repositories\MapIcon\MapIconRepositoryInterface as MapIcon;
use Illuminate\Database\Seeder;

class MapIconsTableSeeder extends Seeder {
    /**
     * @var MapIcon
     */
    private $mapIcon;

    public function __construct(MapIcon $mapIcon)
    {
        $this->mapIcon = $mapIcon;
    }

	public function run()
    {
        # Icons
        $files = File::allFiles(base_path('images/map_icons'));
        foreach ($files as $file) {
            if (!is_object($file) || empty($file->getFilename()))
                continue;

            list($width, $height) = getimagesize($file);
            if (!$width || !$height)
                continue;

            $this->mapIcon->create([
                'path'   => 'images/map_icons/' . $file->getFilename(),
                'width'  => $width,
                'height' => $height,
            ]);
        }
    }
}