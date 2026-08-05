<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * The caller's own submitted reviews — for a mobile "my reviews" list.
     */
    public function mine()
    {
        return response()->json(
            Review::where('user_id', Auth::id())->latest()->get()
        );
    }

    /**
     * A responder's reviews + average rating, for the mobile app to show
     * before/after a mission (rating badge on the mission/alert screens).
     */
    public function forResponder($responderId)
    {
        $reviews = Review::where('responder_id', $responderId)
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json([
            'average_rating' => round($reviews->avg('rating') ?? 0, 1),
            'count' => $reviews->count(),
            'reviews' => $reviews,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'responder_id' => 'nullable|exists:responders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = \App\Models\Review::create([
            'user_id' => auth()->id() ?? (\App\Domains\Users\Models\User::first()->id ?? 1), // fallback if not authenticated but route might require auth
            'responder_id' => $validated['responder_id'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Review submitted successfully', 'review' => $review], 201);
        }

        return back()->with('success', 'Review submitted successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        //
    }
}
