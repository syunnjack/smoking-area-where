<?php

namespace Tests\Feature;

use App\Models\Spot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_top_page_links_to_prefecture_pages(): void
    {
        $this->spot();

        $this->get('/')
            ->assertOk()
            ->assertSee('都道府県から探す')
            ->assertSee('/area/miyagi', false)
            ->assertSee('OpenStreetMap');
    }

    public function test_prefecture_page_lists_its_spots(): void
    {
        $this->spot();

        $this->get('/area/miyagi')
            ->assertOk()
            ->assertSee('宮城県の喫煙所')
            ->assertSee('喫煙所（仙台市青葉区中央）');
    }

    public function test_prefecture_without_spots_is_not_found(): void
    {
        $this->spot();

        $this->get('/area/okinawa')->assertNotFound();
        $this->get('/area/atlantis')->assertNotFound();
    }

    public function test_spot_page_uses_the_display_name(): void
    {
        $spot = $this->spot();

        $this->get('/spots/'.$spot->id)
            ->assertOk()
            ->assertSee('喫煙所（仙台市青葉区中央）');
    }

    public function test_sitemap_lists_prefecture_pages(): void
    {
        $this->spot();

        $this->get('/sitemap.xml')->assertOk()->assertSee('/area/miyagi', false);
    }

    private function spot(): Spot
    {
        return Spot::create([
            'name' => '喫煙所',
            'area' => '宮城県',
            'city' => '仙台市青葉区',
            'town' => '中央',
            'lat' => 38.2601,
            'lng' => 140.8819,
            'source' => 'openstreetmap',
            'source_ref' => 'node/1',
        ]);
    }
}
