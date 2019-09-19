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

        try{
            $title = $request->get('title');
            $desc = $request->get('description');
            $file = $request->get('file');
            $url = $request->get('url');
            $id = $request->get('card_id');
            $show = $request->get('show');


            if(!in_array($id, [0, 1, 2])) {
                return response()->json(['message'=>'آیدی درست نیست.'], 400);
            }

            $card = Cards::find($id);

            if (!$card->get()) {
                $newCard = new Cards(
                    [
                        'id' => $id,
                        'title'=>$title,
                        'text' => $desc,
                        'url' => $url,
                        'active'=>$show
                    ]
                );

                $newCard->save();
            } else {
                $card->update(
                    [
                        'title'=>$title,
                        'text' => $desc,
                        'url' => $url,
                        'active'=>$show
                    ]
                );
            }

            return response()->json(['message'=>'کارت ذخیره شد.']);

        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage()], 400);
        }
    }
}