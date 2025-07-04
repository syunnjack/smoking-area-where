<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Spot;



class SpotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Spot::create([
        'name' => '名古屋駅南口 喫煙所',
        'description' => '太閤通口の駅ビル内、スタバ横にある屋内喫煙所。',
        'lat' => 35.1709156,
        'lng' => 136.8815379,
    ]);
    
    // 栄、金山、今池、鶴舞も同様に追加


    }
}
