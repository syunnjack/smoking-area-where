<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spot extends Model
{
    use HasFactory;

    /** 都道府県ページのURLに使うローマ字。 */
    public const AREA_SLUGS = [
        '北海道' => 'hokkaido', '青森県' => 'aomori', '岩手県' => 'iwate', '宮城県' => 'miyagi',
        '秋田県' => 'akita', '山形県' => 'yamagata', '福島県' => 'fukushima', '茨城県' => 'ibaraki',
        '栃木県' => 'tochigi', '群馬県' => 'gunma', '埼玉県' => 'saitama', '千葉県' => 'chiba',
        '東京都' => 'tokyo', '神奈川県' => 'kanagawa', '新潟県' => 'niigata', '富山県' => 'toyama',
        '石川県' => 'ishikawa', '福井県' => 'fukui', '山梨県' => 'yamanashi', '長野県' => 'nagano',
        '岐阜県' => 'gifu', '静岡県' => 'shizuoka', '愛知県' => 'aichi', '三重県' => 'mie',
        '滋賀県' => 'shiga', '京都府' => 'kyoto', '大阪府' => 'osaka', '兵庫県' => 'hyogo',
        '奈良県' => 'nara', '和歌山県' => 'wakayama', '鳥取県' => 'tottori', '島根県' => 'shimane',
        '岡山県' => 'okayama', '広島県' => 'hiroshima', '山口県' => 'yamaguchi', '徳島県' => 'tokushima',
        '香川県' => 'kagawa', '愛媛県' => 'ehime', '高知県' => 'kochi', '福岡県' => 'fukuoka',
        '佐賀県' => 'saga', '長崎県' => 'nagasaki', '熊本県' => 'kumamoto', '大分県' => 'oita',
        '宮崎県' => 'miyazaki', '鹿児島県' => 'kagoshima', '沖縄県' => 'okinawa',
    ];

    public static function slugForArea(?string $area): ?string
    {
        return $area === null ? null : (self::AREA_SLUGS[$area] ?? null);
    }

    public static function areaForSlug(string $slug): ?string
    {
        return array_search($slug, self::AREA_SLUGS, true) ?: null;
    }

    public function getAreaSlugAttribute(): ?string
    {
        return self::slugForArea($this->area);
    }

    /**
     * 一覧や見出しに出す名前。
     *
     * OpenStreetMap の喫煙所はほとんどが「喫煙所」という名前だけなので、
     * そのままでは区別が付かない。市区町村と町名を添えて場所が分かるようにする。
     */
    public function getDisplayNameAttribute(): string
    {
        $place = trim((string) $this->city.($this->town ? $this->town : ''));

        if ($place === '' || str_contains($this->name, $place)) {
            return $this->name;
        }

        return $this->name.'（'.$place.'）';
    }

    protected $fillable = [
        'name',
        'description',
        'address',
        'facility_type',
        'opening_hours',
        'lat',
        'lng',
        'area',
        'city',
        'town',
        'congestion',
        'congestion_reports',
        'average_congestion',
        'views',
        'likes_count',
        'source',
        'source_ref',
    ];

    protected $casts = [
        'congestion_reports' => 'array',
        'average_congestion' => 'float',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}