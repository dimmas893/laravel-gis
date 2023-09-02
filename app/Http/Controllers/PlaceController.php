<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlaceResource;
use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        $initialMarkers = [
            [
                'position' => [
                    'lat' => -7.027954,
                    'lng' =>  111.519207
                ],
                'draggable' => true
            ],
            [
                'position' => [
                    'lat' => -7.027947,
                    'lng' =>  111.519362
                ],
                'draggable' => true
            ]
        ];
        return view('welcome', compact('initialMarkers'));
    }
}
