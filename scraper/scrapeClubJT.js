const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();

  await page.goto('https://www.clubjt.jp/place/spot/pref-23/', { waitUntil: 'networkidle2' });

  const data = await page.evaluate(() => {
    const results = [];
    document.querySelectorAll('.spot-list li').forEach(li => {
      const name = li.querySelector('h3')?.innerText.trim() ?? '不明';
      const addr = li.querySelector('.spot-address')?.innerText.trim() ?? '';
      const lat = li.dataset.lat || '';
      const lng = li.dataset.lng || '';
      results.push({ name, addr, lat, lng });
    });
    return results;
  });

  const csv = 'name,address,lat,lng\n' + data.map(d =>
    `"${d.name}","${d.addr}",${d.lat},${d.lng}`
  ).join('\n');

  fs.writeFileSync('smoking_aichi.csv', csv);
  console.log(`✅ ${data.length} 件保存しました`);
  await browser.close();
})();
