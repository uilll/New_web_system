<?php
use Tobuli\Repositories\DeviceFuelMeasurement\DeviceFuelMeasurementRepositoryInterface as DeviceFuelMeasurement;
use Illuminate\Database\Seeder;

class FuelMeasurementsTableSeeder extends Seeder {
    /**
     * @var DeviceFuelMeasurement
     */
    private $DeviceFuelMeasurement;

    public function __construct(DeviceFuelMeasurement $DeviceFuelMeasurement)
    {
        $this->DeviceFuelMeasurement = $DeviceFuelMeasurement;
    }

	public function run()
	{
			$this->DeviceFuelMeasurement->create([
                'title' => 'l/100km',
                'fuel_title' => 'liter',
                'distance_title' => 'Kilometers'
			]);

            $this->DeviceFuelMeasurement->create([
                'title' => 'MPG',
                'fuel_title' => 'gallon',
                'distance_title' => 'Miles'
            ]);
	}

}