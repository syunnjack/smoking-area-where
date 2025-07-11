// scraper/scrapeAichi.js
const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();

  // 愛知県の喫煙所一覧ページ（例：kitsuennavi.jp）
  await page.goto('https://kitsuennavi.jp/area/aichi/', { waitUntil: 'networkidle2' });

  const data = await page.evaluate(() => {
    const results = [];
    document.querySelectorAll('.shoplist li').forEach(li => {
      const name = li.querySelector('h3')?.textContent?.trim() ?? '名称不明';
      const lat = li.dataset.lat;
      const lng = li.dataset.lng;
      const desc = li.querySelector('p')?.textContent?.trim() ?? '詳細不明';
      if (lat && lng) {
        results.push({ name, lat, lng, description: desc });
      }
    });
    return results;
  });

  // CSV形式で保存
  const csv = 'name,lat,lng,description\n' + data.map(d =>
    `"${d.name}",${d.lat},${d.lng},"${d.description}"`
  ).join('\n');

  fs.writeFileSync('smoking_aichi.csv', csv);
  console.log(`✅ ${data.length} 件を保存しました`);
  await browser.close();
})();
