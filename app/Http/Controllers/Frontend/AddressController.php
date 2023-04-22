<?php
/**
 * Created by PhpStorm.
 * User: antanas
 * Date: 18.3.12
 * Time: 17.44
 */

namespace App\Http\Controllers\Frontend;


use App\Http\Controllers\Controller;

class AddressController extends Controller
{
    public function autocomplete()
    {
        $locations = \Facades\GeoLocation::listByAddress(request()->input('q'));         
        return response()->json(
            array_map(function($location){ return $location->toArray();}, $locations)
        );
    }
}