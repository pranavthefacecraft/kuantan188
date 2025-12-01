<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GoogleReview;
use Carbon\Carbon;

class GoogleReviewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            [
                'google_review_id' => 'Ahmad_Razak_' . time(),
                'place_id' => 'menara_kuantan_188_place_id',
                'author_name' => 'Ahmad Razak',
                'author_photo_url' => 'https://via.placeholder.com/50x50/007bff/ffffff?text=AR',
                'rating' => 5,
                'text' => 'Amazing experience at Menara Kuantan 188! The view from the top is breathtaking and the staff were very friendly and helpful. Definitely a must-visit attraction in Kuantan. Will come back with family soon!',
                'review_time' => Carbon::now()->subDays(2),
                'like_count' => 15,
                'reply_from_owner' => 'Thank you Ahmad for your wonderful review! We are delighted that you enjoyed the panoramic views and our service. We look forward to welcoming you and your family back soon!',
                'reply_time' => Carbon::now()->subDays(1),
                'is_active' => true
            ],
            [
                'google_review_id' => 'Siti_Aminah_' . (time() + 1),
                'place_id' => 'menara_kuantan_188_place_id',
                'author_name' => 'Siti Aminah',
                'author_photo_url' => 'https://via.placeholder.com/50x50/28a745/ffffff?text=SA',
                'rating' => 5,
                'text' => 'Perfect location for photography! The sunset view from Menara Kuantan 188 is absolutely stunning. Clean facilities and reasonable pricing. Highly recommended for tourists and locals alike.',
                'review_time' => Carbon::now()->subDays(5),
                'like_count' => 23,
                'is_active' => true
            ],
            [
                'google_review_id' => 'David_Wong_' . (time() + 2),
                'place_id' => 'menara_kuantan_188_place_id',
                'author_name' => 'David Wong',
                'author_photo_url' => 'https://via.placeholder.com/50x50/dc3545/ffffff?text=DW',
                'rating' => 4,
                'text' => 'Great experience overall. The observation deck offers wonderful views of Kuantan city. Ticketing process was smooth and efficient. Only minor issue was the waiting time during peak hours.',
                'review_time' => Carbon::now()->subWeek(),
                'like_count' => 8,
                'is_active' => true
            ],
            [
                'google_review_id' => 'Lisa_Tan_' . (time() + 3),
                'place_id' => 'menara_kuantan_188_place_id',
                'author_name' => 'Lisa Tan',
                'author_photo_url' => 'https://via.placeholder.com/50x50/6f42c1/ffffff?text=LT',
                'rating' => 5,
                'text' => 'Brought my family here for a weekend trip. Kids absolutely loved it! The interactive displays are educational and engaging. Great value for money and excellent customer service.',
                'review_time' => Carbon::now()->subDays(10),
                'like_count' => 19,
                'reply_from_owner' => 'We\'re so happy your family had a great time! Our interactive displays are designed to be both fun and educational. Thank you for choosing Menara Kuantan 188!',
                'reply_time' => Carbon::now()->subDays(9),
                'is_active' => true
            ],
            [
                'google_review_id' => 'Rahman_Abdullah_' . (time() + 4),
                'place_id' => 'menara_kuantan_188_place_id',
                'author_name' => 'Rahman Abdullah',
                'author_photo_url' => 'https://via.placeholder.com/50x50/fd7e14/ffffff?text=RA',
                'rating' => 4,
                'text' => 'Nice place to visit with good city views. The ticket prices are reasonable and the facilities are well-maintained. Would be even better with more parking spaces available.',
                'review_time' => Carbon::now()->subDays(15),
                'like_count' => 5,
                'is_active' => true
            ],
            [
                'google_review_id' => 'Michelle_Lim_' . (time() + 5),
                'place_id' => 'menara_kuantan_188_place_id',
                'author_name' => 'Michelle Lim',
                'author_photo_url' => 'https://via.placeholder.com/50x50/e83e8c/ffffff?text=ML',
                'rating' => 5,
                'text' => 'Romantic spot for couples! We visited during golden hour and the views were magical. Perfect for proposals or anniversary celebrations. The sky walk experience is thrilling yet safe.',
                'review_time' => Carbon::now()->subDays(20),
                'like_count' => 31,
                'is_active' => true
            ],
            [
                'google_review_id' => 'Hassan_Ibrahim_' . (time() + 6),
                'place_id' => 'menara_kuantan_188_place_id',
                'author_name' => 'Hassan Ibrahim',
                'author_photo_url' => 'https://via.placeholder.com/50x50/20c997/ffffff?text=HI',
                'rating' => 4,
                'text' => 'Good attraction for first-time visitors to Kuantan. The elevator ride is smooth and the panoramic views are impressive. Gift shop has nice souvenirs at fair prices.',
                'review_time' => Carbon::now()->subMonth(),
                'like_count' => 7,
                'is_active' => true
            ],
            [
                'google_review_id' => 'Sarah_Johnson_' . (time() + 7),
                'place_id' => 'menara_kuantan_188_place_id',
                'author_name' => 'Sarah Johnson',
                'author_photo_url' => 'https://via.placeholder.com/50x50/6610f2/ffffff?text=SJ',
                'rating' => 5,
                'text' => 'As a tourist from Australia, this was definitely a highlight of our Malaysia trip. The 360-degree views are incredible and the staff speak excellent English. Very professional operation!',
                'review_time' => Carbon::now()->subMonth()->subDays(5),
                'like_count' => 42,
                'reply_from_owner' => 'Thank you Sarah! We\'re thrilled that Menara Kuantan 188 was a highlight of your Malaysia trip. We hope you visit us again in the future!',
                'reply_time' => Carbon::now()->subMonth()->subDays(4),
                'is_active' => true
            ]
        ];

        foreach ($reviews as $review) {
            GoogleReview::create($review);
        }
    }
}
