<?php

namespace App\Http\Controllers;

use App\Models\Coordinate;
use Illuminate\Http\Request;

class CoordinateController extends Controller
{
    public function index(Request $request)
    {
        $initialMarkersData = Coordinate::all();
        $initialMarkers = [];
        foreach ($initialMarkersData as $data) {
            $p['id'] = $data->id;
            $p['lat'] = $data->latitude;
            $p['lng'] = $data->longitude;
            $p['draggable'] = true;
            array_push($initialMarkers, $p);
        }
        return view('welcome', compact('initialMarkers'));
    }

    public function ajax()
    {
        $initialMarkersData = Coordinate::all();
        $initialMarkers = [];
        foreach ($initialMarkersData as $data) {
            $p['id'] = $data->id;
            $p['lat'] = $data->latitude;
            $p['lng'] = $data->longitude;
            $p['draggable'] = true;
            array_push($initialMarkers, $p);
        }
        return response()->json($initialMarkers);
    }
    public function find(Request $request)
    {
        $initialMarkersData = Coordinate::all();
        // dd($initialMarkersData[$request['index']]);
        return response()->json($initialMarkersData[$request['index']]);
    }
    public function updateCoordinates(Request $request)
    {
        $data = $request->all();
        // Simpan data koordinat yang telah diperbarui ke dalam database atau lakukan tindakan lain sesuai kebutuhan Anda
        // Misalnya:
        $coordinate = Coordinate::find($data['id']);
        $coordinate->latitude = $data['latitude'];
        $coordinate->longitude = $data['longitude'];
        $coordinate->save();
        return response()->json(['message' => 'Koordinat telah diperbarui.']);
    }
    public function addCoordinate(Request $request)
    {
        // dd($request->all());
        // Validasi input
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Simpan koordinat baru ke database
        $coordinate = new Coordinate();
        $coordinate->latitude = $request->input('latitude');
        $coordinate->longitude = $request->input('longitude');
        $coordinate->draggable = true;
        $coordinate->save();

        return response()->json(['message' => 'Koordinat berhasil ditambahkan']);
    }
}
