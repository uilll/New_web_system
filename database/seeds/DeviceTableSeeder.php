<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Tobuli\Repositories\Device\DeviceRepositoryInterface as Device;
use Tobuli\Repositories\TraccarDevice\TraccarDeviceRepositoryInterface as TrackarDevice;
use Tobuli\Repositories\TraccarPosition\TraccarPositionRepositoryInterface as TraccarPosition;

class DeviceTableSeeder extends Seeder
{
    /**
     * @var Device
     */
    private $device;

    /**
     * @var TrackarDevice
     */
    private $traccarDevice;

    /**
     * @var TraccarPosition
     */
    private $traccarPosition;

    public function __construct(Device $device, TrackarDevice $traccarDevice, TraccarPosition $traccarPosition)
    {
        $this->device = $device;
        $this->traccarDevice = $traccarDevice;
        $this->traccarPosition = $traccarPosition;
    }

    public function run()
    {
        $faker = Faker::create();

        // 1 Device
        $name = $faker->name;
        $imei = $faker->randomNumber(10);

        $position = $this->traccarPosition->create([
            'device_id' => '1',
            'latitude' => '54.688122',
            'longitude' => '25.279541',
            'speed' => 50,
            'time' => date('d-m-Y H:i:s'),
        ]);

        $traccarDevice = $this->traccarDevice->create([
            'name' => $name,
            'uniqueId' => $imei,
            'latestPosition_id' => $position->id,
        ]);
        $this->traccarPosition->update($position->id, ['device_id' => $traccarDevice->id]);

        $this->device->create([
            'user_id' => '1',
            'traccar_device_id' => $traccarDevice->id,
            'icon_id' => 1,
            'name' => $name,
            'imei' => $imei,
            'fuel_measurement_id' => 1,
        ]);

        // 2 Device
        $name = $faker->name;
        $imei = $faker->randomNumber(10);

        $position = $this->traccarPosition->create([
            'device_id' => '1',
            'latitude' => '54.8994',
            'longitude' => '23.9071',
            'speed' => 40,
            'time' => date('d-m-Y  H:i:s'),
        ]);

        $traccarDevice = $this->traccarDevice->create([
            'name' => $name,
            'uniqueId' => $imei,
            'latestPosition_id' => $position->id,
        ]);
        $this->traccarPosition->update($position->id, ['device_id' => $traccarDevice->id]);

        $this->device->create([
            'user_id' => '1',
            'traccar_device_id' => $traccarDevice->id,
            'icon_id' => 2,
            'name' => $name,
            'imei' => $imei,
            'fuel_measurement_id' => 1,
        ]);

        // 3 Device
        $name = $faker->name;
        $imei = $faker->randomNumber(10);

        $position = $this->traccarPosition->create([
            'device_id' => '1',
            'latitude' => '55.7108',
            'longitude' => '21.2018',
            'speed' => 60,
            'time' => date('d-m-Y  H:i:s'),
        ]);

        $traccarDevice = $this->traccarDevice->create([
            'name' => $name,
            'uniqueId' => $imei,
            'latestPosition_id' => $position->id,
        ]);
        $this->traccarPosition->update($position->id, ['device_id' => $traccarDevice->id]);

        $this->device->create([
            'user_id' => '1',
            'traccar_device_id' => $traccarDevice->id,
            'icon_id' => 3,
            'name' => $name,
            'imei' => $imei,
            'fuel_measurement_id' => 1,
        ]);
    }
}
