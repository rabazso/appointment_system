<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Review::insert([
            [
                'user_id' => 1,
                'appointment_id' => 1,
                'rating' => 5,
                'comment' => 'Nagyon elégedett vagyok a szolgáltatással!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'appointment_id' => 2,
                'rating' => 4,
                'comment' => 'Kedves kiszolgálás, ajánlom.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'appointment_id' => 3,
                'rating' => 3,
                'comment' => 'Átlagos élmény volt.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'appointment_id' => 4,
                'rating' => 5,
                'comment' => 'Minden tökéletes volt, biztosan visszatérek!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5,
                'appointment_id' => 5,
                'rating' => 2,
                'comment' => 'Nem teljesen azt kaptam, amire számítottam.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
