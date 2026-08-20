const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ args: ['--no-sandbox'] });
    const page = await browser.newPage();
    
    // Catch console logs
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('pageerror', err => console.log('PAGE ERROR:', err.message));
    
    // Login first
    await page.goto('http://127.0.0.1:8080/auth/login.php');
    await page.type('input[name="email"]', 'comment99@test.com');
    await page.type('input[name="password"]', 'pass');
    await Promise.all([
        page.waitForNavigation(),
        page.click('button[type="submit"]')
    ]);
    
    console.log("Logged in successfully. Going to index.");
    
    await page.goto('http://127.0.0.1:8080/index.php');
    
    // Find the first Like button
    const likeBtn = await page.$('form[action="/like_handler.php"] button');
    
    if (!likeBtn) {
        console.log("No like button found.");
        await browser.close();
        return;
    }
    
    const textBefore = await page.evaluate(el => el.innerHTML, likeBtn);
    console.log("Button text before click: " + textBefore);
    
    // Click and wait a bit for AJAX
    await likeBtn.click();
    await new Promise(r => setTimeout(r, 1000));
    
    const textAfter = await page.evaluate(el => el.innerHTML, likeBtn);
    console.log("Button text after click: " + textAfter);
    
    // Check if the page reloaded by checking the URL
    console.log("Current URL: " + page.url());
    
    await browser.close();
})();
