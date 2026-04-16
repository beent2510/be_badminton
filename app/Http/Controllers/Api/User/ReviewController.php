<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index(Request $request)
    {
        return $this->reviewService->search($request->all());
    }

    public function show($id)
    {
        return $this->reviewService->show($id);
    }

    /**
     * Chỉ user đã có booking confirmed/paid mới được review
     */
    public function store(Request $request)
    {
        $request->validate([
            'court_id'   => 'required|exists:courts,id',
            'booking_id' => 'required|exists:bookings,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', $user->id)
            ->where('court_id', $request->court_id)
            ->whereIn('status', ['confirmed', 'paid'])
            ->first();

        if (!$booking) {
            return response()->json(['error' => 'Bạn chưa có lịch đặt sân được xác nhận để đánh giá'], 403);
        }

        // Check already reviewed
        $existing = \App\Models\Review::where('user_id', $user->id)
            ->where('court_id', $request->court_id)
            ->where('booking_id', $booking->id)
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Bạn đã đánh giá sân này rồi'], 409);
        }

        $data = [
            'court_id'   => $request->court_id,
            'user_id'    => $user->id,
            'booking_id' => $booking->id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
            'is_visible' => true,
        ];

        $review = $this->reviewService->store($data);
        return response()->json($review, 201);
    }

    public function update(Request $request, $id)
    {
        $review = $this->reviewService->show($id);
        if (!$review) return response()->json(['error' => 'Review not found'], 404);
        return $this->reviewService->update($id, $request->only(['rating', 'comment']));
    }

    public function destroy($id)
    {
        $this->reviewService->destroy($id);
        return response()->json(['message' => 'Review deleted successfully']);
    }
}
