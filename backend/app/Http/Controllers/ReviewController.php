<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ReviewResource::collection(Review::with('user')->latest()->paginate(3));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReviewRequest $request)
    {
        $review = Review::create($request->safe()->merge(["user_id" => $request->user()->id])->toArray());
        return new ReviewResource($review);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id):JsonResponse
    {
        $review = Review::find($id);
        if(!$review){
            return response() ->json(["message" => "Review not found"], 404);
        }
        $review->delete();
        return response()->json(["message" => "Review deleted successfully"]);
    }
}
