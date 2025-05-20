<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    public function index()
    {
        $artists = Artist::all();
        return response()->json($artists);
    }

    public function store(Request $request)
    {
        $artist = Artist::create($request->all());
        return response()->json($artist, 201);
    }
}
