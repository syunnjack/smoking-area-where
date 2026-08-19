"""OpenStreetMap の喫煙所データに、市区町村名を付ける。

出典: OpenStreetMap contributors（ODbL 1.0） https://www.openstreetmap.org/copyright

OSMの喫煙所はほとんどが名前「喫煙所」だけで、住所も入っていない。そのままだと
900件近いページがすべて同じ見出しになり、利用者にも検索エンジンにも区別できない。
そこで、座標から国土地理院の逆ジオコーダで市区町村と町名を求め、
「喫煙所（仙台市青葉区中央）」のように場所が分かる表示名を作る。

推測はしない。逆ジオコーダで得られた行政区画名だけを足す。

使い方: python scripts/build-spot-data.py
  → database/data/spots-osm.json を、市区町村つきで書き直す
"""
import json
import re
import time
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
CACHE = ROOT / 'scripts' / '.cache'
DATA = ROOT / 'database' / 'data' / 'spots-osm.json'

GSI_REVERSE = 'https://mreversegeocoder.gsi.go.jp/reverse-geocoder/LonLatToAddress?lat={}&lon={}'
GSI_MUNI = 'https://maps.gsi.go.jp/js/muni.js'
UA = 'kitsuenjo-doko-data/1.0 (+https://kitsuenjo-doko.jp)'
DELAY = 1.0


def get(url: str, timeout: int = 30) -> str:
    request = urllib.request.Request(url, headers={'User-Agent': UA})
    with urllib.request.urlopen(request, timeout=timeout) as response:
        return response.read().decode('utf-8', 'replace')


def municipalities() -> dict[str, tuple[str, str]]:
    cache = CACHE / 'muni.json'
    if cache.exists():
        return {key: tuple(value) for key, value in json.loads(cache.read_text(encoding='utf-8')).items()}

    table = {}
    for code, value in re.findall(r'GSI\.MUNI_ARRAY\["(\d+)"\]\s*=\s*\'([^\']+)\'', get(GSI_MUNI)):
        parts = value.split(',')
        if len(parts) >= 4:
            table[str(int(code))] = (parts[1], parts[3].replace('　', ''))

    CACHE.mkdir(exist_ok=True)
    cache.write_text(json.dumps(table, ensure_ascii=False), encoding='utf-8')
    return table


def main() -> None:
    CACHE.mkdir(exist_ok=True)
    spots = json.loads(DATA.read_text(encoding='utf-8'))
    muni = municipalities()

    cache_path = CACHE / 'reverse.json'
    reverse = json.loads(cache_path.read_text(encoding='utf-8')) if cache_path.exists() else {}

    updated = 0
    for index, spot in enumerate(spots, 1):
        key = f"{spot['lat']:.6f},{spot['lng']:.6f}"

        if key not in reverse:
            try:
                found = json.loads(get(GSI_REVERSE.format(spot['lat'], spot['lng'])))
                results = found.get('results') or {}
                reverse[key] = [results.get('muniCd'), results.get('lv01Nm')]
            except Exception as error:
                print(f'  逆ジオコード失敗 {key} {error}', flush=True)
                reverse[key] = [None, None]
            time.sleep(DELAY)
            if index % 50 == 0:
                cache_path.write_text(json.dumps(reverse, ensure_ascii=False), encoding='utf-8')
                print(f'  {index}/{len(spots)}', flush=True)

        code, town = reverse.get(key) or (None, None)
        if not code:
            continue

        prefecture, city = muni.get(str(int(code)), (None, None))
        if not city:
            continue

        spot['a'] = spot.get('a') or prefecture   # 都道府県（元データにあればそのまま）
        spot['c'] = city                          # 市区町村
        spot['tn'] = town or None                 # 町名（丁目まで）。't' はOSMの種別で使用済み
        updated += 1

    cache_path.write_text(json.dumps(reverse, ensure_ascii=False), encoding='utf-8')
    DATA.write_text(json.dumps(spots, ensure_ascii=False), encoding='utf-8')

    print(f'{len(spots)}件のうち{updated}件に市区町村を付けました')


main()
