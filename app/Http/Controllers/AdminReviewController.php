<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class AdminReviewController extends Controller
{
    protected function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
    }

    public function destroy($id)
    {
        $this->ensureAdmin();

        $review = Review::findOrFail($id);
        $description = "Removed a {$review->rating}-star review by " . ($review->user->name ?? 'Unknown');
        $review->delete();

        AdminActivityLog::record('review_removed', $description);

        return redirect()->back()->with('success', 'Review removed.');
    }
}
