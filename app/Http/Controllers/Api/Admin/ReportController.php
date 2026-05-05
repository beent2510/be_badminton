<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function branchRevenue(Request $request)
    {
        $branchIds = null;
        if (auth()->check() && auth()->user()->role === 'branch_admin') {
            $branchIds = auth()->user()->branches()->pluck('id')->toArray();
        }

        $from = $request->input('from');
        $to = $request->input('to');

        $bookings = Booking::query()
            ->with(['items.court.branch', 'court.branch'])
            ->whereIn('status', ['confirmed', 'paid'])
            ->get();

        $totals = [];
        foreach ($bookings as $booking) {
            if ($booking->items && $booking->items->count() > 0) {
                foreach ($booking->items as $item) {
                    if ($from && $item->booking_date < $from) {
                        continue;
                    }
                    if ($to && $item->booking_date > $to) {
                        continue;
                    }

                    $branch = $item->court?->branch;
                    if (!$branch) {
                        continue;
                    }
                    if ($branchIds && !in_array($branch->id, $branchIds, true)) {
                        continue;
                    }
                    $branchId = $branch->id;
                    if (!isset($totals[$branchId])) {
                        $totals[$branchId] = [
                            'branch_id' => $branchId,
                            'branch_name' => $branch->name,
                            'total_revenue' => 0,
                        ];
                    }
                    $totals[$branchId]['total_revenue'] += (float) ($item->total_price ?? 0);
                }
            } elseif ($booking->court && $booking->court->branch) {
                if ($from && $booking->booking_date < $from) {
                    continue;
                }
                if ($to && $booking->booking_date > $to) {
                    continue;
                }
                $branch = $booking->court->branch;
                if ($branchIds && !in_array($branch->id, $branchIds, true)) {
                    continue;
                }
                $branchId = $branch->id;
                if (!isset($totals[$branchId])) {
                    $totals[$branchId] = [
                        'branch_id' => $branchId,
                        'branch_name' => $branch->name,
                        'total_revenue' => 0,
                    ];
                }
                $totals[$branchId]['total_revenue'] += (float) ($booking->final_price ?? $booking->total_price ?? 0);
            }
        }

        return response()->json(array_values($totals));
    }

    public function branchCustomerRevenue(Request $request)
    {
        $branchId = $request->input('branch_id');
        $branchIds = null;
        if (auth()->check() && auth()->user()->role === 'branch_admin') {
            $branchIds = auth()->user()->branches()->pluck('id')->toArray();
        }

        $from = $request->input('from');
        $to = $request->input('to');
        $bookings = Booking::query()
            ->with(['items.court.branch', 'court.branch', 'user'])
            ->whereIn('status', ['confirmed', 'paid'])
            ->get();

        $totals = [];
        foreach ($bookings as $booking) {
            $user = $booking->user;
            if (!$user) {
                continue;
            }

            if ($booking->items && $booking->items->count() > 0) {
                foreach ($booking->items as $item) {
                    if ($from && $item->booking_date < $from) {
                        continue;
                    }
                    if ($to && $item->booking_date > $to) {
                        continue;
                    }

                    $branch = $item->court?->branch;
                    if (!$branch) {
                        continue;
                    }
                    if ($branchIds && !in_array($branch->id, $branchIds, true)) {
                        continue;
                    }
                    if ($branchId && (int) $branchId !== (int) $branch->id) {
                        continue;
                    }
                    $key = $user->id . '-' . $branch->id;
                    if (!isset($totals[$key])) {
                        $totals[$key] = [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'branch_id' => $branch->id,
                            'branch_name' => $branch->name,
                            'total_revenue' => 0,
                        ];
                    }
                    $totals[$key]['total_revenue'] += (float) ($item->total_price ?? 0);
                }
            } elseif ($booking->court && $booking->court->branch) {
                if ($from && $booking->booking_date < $from) {
                    continue;
                }
                if ($to && $booking->booking_date > $to) {
                    continue;
                }
                $branch = $booking->court->branch;
                if ($branchIds && !in_array($branch->id, $branchIds, true)) {
                    continue;
                }
                if ($branchId && (int) $branchId !== (int) $branch->id) {
                    continue;
                }
                $key = $user->id . '-' . $branch->id;
                if (!isset($totals[$key])) {
                    $totals[$key] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->name,
                        'total_revenue' => 0,
                    ];
                }
                $totals[$key]['total_revenue'] += (float) ($booking->final_price ?? $booking->total_price ?? 0);
            }
        }

        return response()->json(array_values($totals));
    }
}
