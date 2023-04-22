<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Facades\Repositories\DeviceRepo;
use Facades\Repositories\UserRepo;
use Illuminate\Support\Facades\Input;
use Tobuli\Entities\File\DeviceMedia;


class DeviceMediaController extends Controller
{
    /**
     * Create view of media.
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $search_input = Input::all();

        $deviceCollection = DeviceRepo::searchAndPaginateSimple($search_input, 'name', 'asc', 15, [$this->user->id]);

        return view('front::DeviceMedia.create')->with(compact('deviceCollection'));
    }


    public function getImages($device_id)
    {
        try {
            $device = UserRepo::getDevice($this->user->id, $device_id);

            $this->checkException('camera', 'view');
            $this->checkException('devices', 'show', $device);

            $images = DeviceMedia::setEntity($device)->orderByDate('desc')->paginate(15);

            if (!$this->api)
                return view('front::DeviceMedia.images', ['images' => $images, 'deviceId' => $device_id]);

            return response()->json(['success' => true, 'data' => $images]);

        } catch (\Exception $e) {
            if (!$this->api)
                return view('front::DeviceMedia.images', ['images' => []]);

            return response()->json(['success' => false]);
        }
    }


    public function getImage($filename, $device_id)
    {
        try {
            $device = UserRepo::getDevice($this->user->id, $device_id);

            $this->checkException('devices', 'show', $device);
            $this->checkException('camera', 'view');

            $image = DeviceMedia::setEntity($device)->find($filename);

            $item = $this->objectForMapDisplay($device, $image);

            if (!$this->api)
                return view('front::DeviceMedia.image', ['image' => $image, 'item' => $item]);

            return response()->json(['success' => true, 'item' => $item, 'image' => $image->toArray()]);

        } catch (\Exception $e) {
            return view('front::DeviceMedia.image', ['image' => null]);
        }
    }


    public function deleteImage($filename, $device_id)
    {
        $device = UserRepo::getDevice($this->user->id, $device_id);

        $this->checkException('devices', 'remove', $device);
        $this->checkException('camera', 'remove');

        $image = DeviceMedia::setEntity($device)->find($filename);

        if ($image->delete())
            return response()->json(['success' => true]);

        return response()->json(['success' => false]);
    }


    public function downloadFile($filename, $device_id)
    {
        $device = UserRepo::getDevice($this->user->id, $device_id);

        $this->checkException('devices', 'show', $device);
        $this->checkException('camera', 'view');

        $file = DeviceMedia::setEntity($device)->find($filename);

        return response()->download($file->path);
    }


    private function objectForMapDisplay($device, $image)
    {
        $closest_position = $device->positions()
            ->orderByRaw("abs(TIMESTAMPDIFF(second, time, '" . $image->created_at . "')) DESC")
            ->first();

        $tail_collection = $device->positions()
            ->where('id', '<', $closest_position->id)
            ->where('distance', '>', 0.02)
            ->take(10)->orderBy('id', 'DESC')->get();

        $tail_coords = [];
        foreach ($tail_collection as $tail)
            $tail_coords[] = ['lat' => (string)$tail->latitude, 'lng' => (string)$tail->longitude];

        $item = new \stdClass();
        $item->org_id = $device->id;
        $item->id = null;
        $item->tail = $tail_coords;
        $item->tail_color = $device->tail_color;
        $item->name = $device->name;
        $item->speed = $closest_position->speed;
        $item->course = $closest_position->course;
        $item->lat = (string)$closest_position->latitude;
        $item->lng = (string)$closest_position->longitude;
        $item->altitude = $device->altitude;
        $item->protocol = $device->getProtocol();
        $item->time = $device->time;
        $item->timestamp = $device->timestamp;
        $item->acktimestamp = $device->acktimestamp;

        $item->online = $device->getStatus();

        return $item;
    }
}
