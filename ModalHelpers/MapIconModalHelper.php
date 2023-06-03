<?php

namespace ModalHelpers;

use Facades\Repositories\MapIconRepo;
use Facades\Repositories\UserMapIconRepo;
use Facades\Validators\UserMapIconFormValidator;
use Tobuli\Exceptions\ValidationException;

class MapIconModalHelper extends ModalHelper
{
    public function get()
    {
        try {
            $this->checkException('poi', 'view');

            $mapIcons = UserMapIconRepo::whereUserId($this->user->id);
        } catch (\Exception $e) {
            $mapIcons = [];
        }

        return compact('mapIcons');
    }

    public function getIcons()
    {
        $mapIcons = MapIconRepo::all();

        return $mapIcons;
    }

    public function iconsList()
    {
        $mapIcons = MapIconRepo::all();

        return view('front::MapIcons._list', compact('mapIcons'));
    }

    public function create()
    {
        $this->checkException('poi', 'store');

        $this->validate('create');

        UserMapIconRepo::create($this->data + ['user_id' => $this->user->id]);

        return ['status' => 1];
    }

    public function edit()
    {
        $item = UserMapIconRepo::find($this->data['id']);

        $this->checkException('poi', 'update', $item);

        $this->validate('update');

        UserMapIconRepo::update($item->id, $this->data);

        return ['status' => 1];
    }

    private function validate($type)
    {
        // Limited acc
        if (isLimited($this->user, 'poi')) {
            throw new ValidationException(['id' => trans('front.limited_acc')]);
        }

        UserMapIconFormValidator::validate($type, $this->data);
    }

    public function changeActive()
    {
        $id = array_key_exists('map_icon_id', $this->data) ? $this->data['map_icon_id'] : $this->data['id'];

        $item = UserMapIconRepo::find($id);

        $this->checkException('poi', 'active', $item);

        UserMapIconRepo::update($item->id, ['active' => ($this->data['active'] == 'true')]);

        return ['status' => 1];
    }

    public function destroy()
    {
        $id = array_key_exists('map_icon_id', $this->data) ? $this->data['map_icon_id'] : $this->data['id'];

        $item = UserMapIconRepo::find($id);

        $this->checkException('poi', 'remove', $item);

        UserMapIconRepo::delete($id);

        return ['status' => 1];
    }

    public function import($content = null, $map_icon_id = null)
    {
        $this->checkException('poi', 'store');

        if (is_null($content)) {
            $content = $this->data['content'];
        }

        if (is_null($map_icon_id)) {
            $map_icon_id = $this->data['map_icon_id'];
        }

        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($content);

        if (! $xml) {
            return ['status' => 0, 'error' => trans('front.unsupported_format')];
        }

        $icon_count = 0;
        $icon_exists_count = 0;

        try {
            // KML
            if ($xml) {
                $xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');

                $folders = $xml->xpath('//kml:Folder');

                if ($folders) {
                    foreach ($folders as $folder) {
                        foreach ($folder->Placemark as $mark) {
                            $mark = json_decode(json_encode($mark), true);

                            if (empty($mark['name'])) {
                                continue;
                            }

                            if (empty($mark['Point']['coordinates'])) {
                                continue;
                            }

                            [$lng, $lat, $unknow] = explode(',', $mark['Point']['coordinates']);
                            $coordinates = ['lat' => $lat, 'lng' => $lng];

                            $item = UserMapIconRepo::findWhere(['coordinates' => json_encode($coordinates), 'user_id' => $this->user->id]);
                            if (empty($item)) {
                                $icon_count++;
                                UserMapIconRepo::create([
                                    'active' => 0,
                                    'user_id' => $this->user->id,
                                    'map_icon_id' => $map_icon_id,
                                    'name' => $mark['name'],
                                    'description' => empty($mark['description']) ? '' : $mark['description'],
                                    'coordinates' => json_encode($coordinates),
                                ]);
                            } else {
                                $icon_exists_count++;
                            }
                        }
                    }
                } else {
                    foreach ($xml->xpath('//kml:Placemark') as $mark) {
                        $mark = json_decode(json_encode($mark), true);

                        if (empty($mark['name'])) {
                            continue;
                        }

                        if (empty($mark['Point']['coordinates'])) {
                            continue;
                        }

                        [$lng, $lat, $unknow] = explode(',', $mark['Point']['coordinates']);
                        $coordinates = ['lat' => $lat, 'lng' => $lng];

                        $item = UserMapIconRepo::findWhere(['coordinates' => json_encode($coordinates), 'user_id' => $this->user->id]);
                        if (empty($item)) {
                            $icon_count++;
                            UserMapIconRepo::create([
                                'active' => 0,
                                'user_id' => $this->user->id,
                                'map_icon_id' => $map_icon_id,
                                'name' => $mark['name'],
                                'description' => empty($mark['description']) ? '' : $mark['description'],
                                'coordinates' => json_encode($coordinates),
                            ]);
                        } else {
                            $icon_exists_count++;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return ['status' => 0, 'error' => trans('front.unsupported_format')];
        }

        return array_merge(['status' => 1, 'message' => strtr(trans('front.imported_map_icon'), [':count' => $icon_count])]);
    }
}
