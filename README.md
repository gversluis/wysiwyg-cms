Introduction
------------
I wrote a tiny WYSIWYG CMS which allows you to login and edit pages after that.

Features
--------
- File system based. No seperate database
- No seperate CMS front-end but inline WYSIWYG editing
- Multi user
- Automatic backups

Usage
-----
1. Create HTML webpages (with valid HTML because it will be parsed)
2. Embed the content you want to edit between tags which can be selected by a CSS selector, i.e. <div class="edit">
3. Edit config.php and choose a selector (.edit in the example from step 2) and location for your users file
4. Copy devontitties.passwd.example to your users file and follow the instructions in the file to add your first user
5. Login with the new user
6. Go to the HTML webpages you created and edit them!

Limitations
-----------
At the moment of writing only a single block can be edited per page.

Dependencies
------------
[TinyMCE](https://github.com/tinymce/tinymce) - Used for inline editing
[CssXPath](https://github.com/PhpGt/CssXPath) - Convert CSS to Xpath so the same CSS selectors can be used in both JavaScript and PHP

