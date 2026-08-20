const { JSDOM } = require("jsdom");

const html = `
<!DOCTYPE html>
<html>
<body>
<form action='/like_handler.php' method='POST' style='margin: 0; display: inline-block;'>
    <button type='submit'>❤️ 2</button>
</form>
</body>
</html>
`;

const dom = new JSDOM(html);
const document = dom.window.document;

const forms = document.querySelectorAll('form[action="/like_handler.php"]');
console.log("Forms found: " + forms.length);
