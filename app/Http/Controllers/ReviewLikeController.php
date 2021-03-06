<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewLike;
use App\Notifications\ReviewLiked;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReviewLikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Review $review)
    {
       if (!$review->hasBeenlikedBy(auth()->user())) {
           $like = $review->likes()->create([
                    'user_id' => auth()->user()->id
                ]);

            $review->user->notify(new ReviewLiked($like));

            return response()->json(['message' => 'Review successfully liked'], Response::HTTP_CREATED);
       }

       return response()->json(['errors' => 'Review already liked by user'], Response::HTTP_UNPROCESSABLE_ENTITY);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReviewLike  $reviewLike
     * @return \Illuminate\Http\Response
     */
    public function destroy(Review $review)
    {
        $review->likes()->where('user_id', auth()->user()->id)->delete();

        return response([], Response::HTTP_NO_CONTENT);
    }
}
