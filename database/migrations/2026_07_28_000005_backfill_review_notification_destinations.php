<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $notifications = DB::table('report_notifications')
            ->where('kind', 'review_received')
            ->where(function ($query) {
                $query->whereNull('action_url')
                    ->orWhere('action_url', 'like', '%#avaliacoes');
            })
            ->get();

        foreach ($notifications as $notification) {
            $createdAt = Carbon::parse($notification->created_at);
            $matches = DB::table('reviews')
                ->join('ads', 'ads.id', '=', 'reviews.ad_id')
                ->where('ads.user_id', $notification->user_id)
                ->whereBetween('reviews.created_at', [
                    $createdAt->copy()->subSeconds(10),
                    $createdAt->copy()->addSeconds(10),
                ])
                ->select('reviews.id', 'ads.slug')
                ->get();

            if ($matches->count() !== 1) {
                continue;
            }

            $review = $matches->first();
            DB::table('report_notifications')
                ->where('id', $notification->id)
                ->update([
                    'action_url' => '/prestador/' . $review->slug . '#avaliacao-' . $review->id,
                ]);
        }
    }

    public function down(): void
    {
        DB::table('report_notifications')
            ->where('kind', 'review_received')
            ->where('action_url', 'like', '%#avaliacao-%')
            ->update(['action_url' => null]);
    }
};
