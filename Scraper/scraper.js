 const puppeteer = require('puppeteer');

    (async function main() {
      if (process.argv.length < 3) {
         console.error("Usage: node scraper.js url\n");
         return;
      }

      try {
        const browser = await puppeteer.launch();
        const [page] = await browser.pages();

        await page.goto(process.argv[2], { waitUntil: 'networkidle0' });
        const data = await page.evaluate(() => document.querySelector('*').outerHTML);

        console.log(data);

        await browser.close();
      } catch (err) {
        console.error(err);
      }
    })();
