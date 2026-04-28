<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $appointments = Appointment::query()
            ->where('status', 'completed')
            ->inRandomOrder()
            ->take(25)
            ->get();

        $comments = [
            ['rating' => 5, 'comment' => 'I am very satisfied with the service.'],
            ['rating' => 4, 'comment' => 'Friendly service.'],
            ['rating' => 5, 'comment' => 'Precise work and a great atmosphere.'],
            ['rating' => 4, 'comment' => 'Quick and clean haircut.'],
            ['rating' => 3, 'comment' => 'It was okay.'],
            ['rating' => 5, 'comment' => 'The fade was exactly what I asked for.'],
            ['rating' => 5, 'comment' => 'Great attention to detail and a relaxed visit.'],
            ['rating' => 4, 'comment' => 'Nice cut and helpful styling advice.'],
            ['rating' => 2, 'comment' => 'The appointment started later than expected.'],
            ['rating' => 5, 'comment' => 'Best beard trim I have had in a while.'],
            ['rating' => 4, 'comment' => 'Good result and the booking was easy.'],
            ['rating' => 3, 'comment' => 'Solid haircut, but the finish could be cleaner.'],
        ];

        foreach ($appointments as $index => $appointment) {
            $reviewData = $comments[$index % count($comments)];

            Review::create([
                'appointment_id' => $appointment->id,
                'customer_id' => $appointment->customer_id,
                'rating' => $reviewData['rating'],
                'comment' => $reviewData['comment'],
                'is_visible' => $index % 7 !== 0,
            ]);
        }
    }
}
