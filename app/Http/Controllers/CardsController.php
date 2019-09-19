<?php

namespace App\Http\Controllers;


use App\Cards;
use Illuminate\Http\Request;


class CardsController extends Controller
{
    public function getAllCards() {
        $cards = Cards::orderBy('id', 'desc')->where('active', true)->take(3)->get();
        return response()->json($cards);
    }

    public function submitCard(Request $request) {
        $query = json_decode($request->getContent(), true);
        return response()->json($query);
    }
}