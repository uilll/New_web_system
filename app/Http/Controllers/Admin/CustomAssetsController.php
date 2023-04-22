<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class CustomAssetsController extends Controller
{
    private $assetContent = '';

    public function getCustomAsset($asset)
    {
        $assetFile = $this->whichAssetFile($asset);

        if (File::exists($assetFile)) {
            $this->assetContent = File::get($assetFile);
        }

        return view('admin::CustomAssets.' . $asset )->with($asset, $this->assetContent);
    }

    public function setCustomAsset(Request $request, $asset)
    {
        $assetFile = $this->whichAssetFile($asset);

        $this->checkIfDirectoryExists();

        if ($request->has($asset)) {
            $this->assetContent = $request->input($asset);
        }

        File::put($assetFile, $this->assetContent);
        return view('admin::CustomAssets.' . $asset )->with($asset, $this->assetContent);
    }

    public function whichAssetFile($asset)
    {
        if ($asset === 'js') {
            return storage_path('custom/js.js');
        } elseif ($asset === 'css') {
            return storage_path('custom/css.css');
        } else {
            throw new RouteNotFoundException();
        }
    }

    public function checkIfDirectoryExists()
    {
        if (!File::isDirectory(storage_path('custom'))) {
            File::makeDirectory(storage_path('custom'));
        }
    }
}

