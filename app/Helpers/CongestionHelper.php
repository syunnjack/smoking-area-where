<?php

namespace App\Helpers;

class CongestionHelper
{
    /**
     * 混雑度の数値に基づいてテキストを取得します。
     *
     * @param float|int|null $average_congestion
     * @return string
     */
    public static function getText($average_congestion)
    {
        {
        if ($average_congestion === null || !is_numeric($average_congestion)) {
            return '報告なし';
        }
        
        // 混雑度を3タイプに修正
        if ($average_congestion >= 2.5) {
            return '混雑';
        } elseif ($average_congestion >= 1.5) {
            return 'やや混雑';
        } else {
            return '空いている';
        }
    }
}
}