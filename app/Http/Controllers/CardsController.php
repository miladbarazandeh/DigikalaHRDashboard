<?php

namespace App\Http\Controllers;


use App\Cards;
use Illuminate\Http\Request;


class CardsController extends Controller
{
    public function getAllCards() {
        $cards = Cards::where('active', true)->take(3)->get(['title', 'url', 'text', 'active']);
        return response()->json($cards);
    }

    public function submitCard(Request $request)
    {
        $baseUrl = 'uploads/cards/';

        try{
            $title = $request->get('title');
            $desc = $request->get('description');
            $url = $request->get('url');
            $id = $request->get('card_id');
            $show = $request->get('show');


            if ($request->has('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $fileName = time().'.'.$extension;
                $file->move($baseUrl, $fileName);
            }


            if(!in_array($id, [1, 2, 3])) {
                return response()->json(['message'=>'آیدی درست نیست.'], 400);
            }

            $card = Cards::find($id);

            if (!$card) {
                $newCard = new Cards(
                    [
                        'id' => $id,
                        'title'=>$title,
                        'text' => $desc,
                        'url' => $url,
                        'image' => isset($fileName) ? '/'.$baseUrl.$fileName : null,
                        'active'=>$show == 'true'? 1:0
                    ]
                );

                $newCard->save();
            } else {
                $card->update(
                    [
                        'title'=>$title,
                        'text' => $desc,
                        'url' => $url,
                        'active'=>$show =='true'? 1:0,
                        'image' => isset($fileName) ? '/'.$baseUrl.$fileName : null
                    ]
                );
            }

            return response()->json(['message'=>'کارت ذخیره شد.']);

        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage()], 400);
        }
    }
}