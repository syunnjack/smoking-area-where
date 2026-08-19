<?php

namespace Database\Seeders;

use App\Models\Spot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * OpenStreetMap から取り出した喫煙所を取り込む。
 *
 * データは database/data/spots-osm.json に置いてある。
 * 出典は OpenStreetMap（ODbL 1.0）。表示側に「© OpenStreetMap contributors」が要る。
 *
 * 実在する場所だけを入れる。説明文など元データに無いものは補わない。
 * 混雑度は利用者の投稿でしか埋まらない項目なので、空のままにする。
 */
class OsmSpotSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/spots-osm.json');

        if (! is_file($path)) {
            $this->command?->warn("データファイルがありません: {$path}");

            return;
        }

        $rows = json_decode(file_get_contents($path), true);

        if (! is_array($rows)) {
            $this->command?->error('データファイルを読めませんでした。');

            return;
        }

        $before = Spot::where('source', 'openstreetmap')->count();
        $now = now();
        $imported = 0;

        // SQLite は1文あたりのプレースホルダ数に上限がある（古いビルドは999）。
        $columns = 13;
        $chunkSize = max(1, intdiv(900, $columns));

        // キーは短縮してある:
        //   t=種別(node/way) i=OSMのID n=名前 f=屋内/屋外 a=都道府県
        //   c=市区町村 tn=町名（scripts/build-spot-data.py が座標から付ける）
        //   ad=住所 h=利用時間 lat/lng=座標
        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            $records = [];

            foreach ($chunk as $row) {
                if (empty($row['n']) || ! isset($row['lat'], $row['lng'])) {
                    continue;
                }

                $records[] = [
                    'source' => 'openstreetmap',
                    'source_ref' => "{$row['t']}/{$row['i']}",
                    'name' => $row['n'],
                    'facility_type' => $row['f'] ?? null,
                    'area' => $row['a'] ?? null,
                    'city' => $row['c'] ?? null,
                    'town' => $row['tn'] ?? null,
                    'address' => $row['ad'] ?? null,
                    'opening_hours' => $row['h'] ?? null,
                    'lat' => (string) $row['lat'],
                    'lng' => (string) $row['lng'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($records === []) {
                continue;
            }

            DB::transaction(function () use ($records) {
                Spot::upsert(
                    $records,
                    ['source', 'source_ref'],
                    ['name', 'facility_type', 'area', 'city', 'town', 'address', 'opening_hours', 'lat', 'lng', 'updated_at'],
                );
            });

            $imported += count($records);
        }

        $after = Spot::where('source', 'openstreetmap')->count();

        $this->command?->info(
            "OpenStreetMap の {$imported} 件を取り込みました（喫煙所 {$before} → {$after}）。"
        );
    }
}
