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
            ->take(5)
            ->get();

        $comments = [
            ['rating' => 5, 'comment' => 'I am very satisfied with the service.'],
            ['rating' => 4, 'comment' => 'Friendly service.'],
            ['rating' => 5, 'comment' => 'Precise work and a great atmosphere.'],
            ['rating' => 4, 'comment' => 'Quick and clean haircut.'],
            ['rating' => 3, 'comment' => 'It was okay.'],
        ];

        foreach ($appointments as $index => $appointment) {
            $reviewData = $comments[array_rand($comments)];

            Review::create([
                'appointment_id' => $appointment->id,
                'customer_id' => $appointment->customer_id,
                'rating' => $reviewData['rating'],
                'comment' => $reviewData['comment'],
                'is_visible' => true,
            ]);
        }
    }
}
