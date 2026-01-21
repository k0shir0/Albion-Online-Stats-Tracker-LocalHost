made by koshiro :)

ALBION DASHBOARD — LOCAL HOST GUIDE
-----------------------------------

1. INSTALL PHP (Windows easiest method)
   • Go to: https://windows.php.net/download/
   • Download the latest “Thread Safe x64” ZIP version.
   • Extract it to:  C:\php
   • Open a new CMD window and test:
       php --version
     You should see a version number.

   ✅ If you get “php not recognized”:
      • Open Windows search → “Edit system environment variables”.
      • Click “Environment Variables…”.
      • Under “System variables” find “Path” → Edit → Add:
          C:\php
      • Reopen CMD and try again.

2. ENABLE REQUIRED PHP EXTENSIONS
   • Open the file:  C:\php\php.ini  (use Notepad)
   • Press CTRL+F and find these lines:

       ;extension=curl
       ;extension=openssl

   • Remove the semicolons at the start so they look like:

       extension=curl
       extension=openssl

   • Save the file.

   ✅ These enable secure API requests (needed for Albion stats).

3. HOST LOCALLY
   • Place all project files (index.php, readme.txt) in one folder.
   • Open that folder in CMD or PowerShell.
   • Run:
       php -S localhost:8000
   • Open your browser and go to:
       http://localhost:8000

4. FIRST RUN SETUP
   • When you open the page, it will ask for your Albion Online Player ID.
   • You can get your Player ID easily using the Albion Online Tools Discord bot:
       Type the command:  /player
     and copy the ID it shows.
   • Paste your Player ID into the setup page and click Save.
   • (Optional) Place a picture named  pfp.png  or  pfp.jpg  in the same folder to use as your avatar.
   • After saving, a file named  cfg.json  will appear automatically — it stores your setup info.

5. HOW IT WORKS (brief)
   • The site pulls your public stats from the Albion Online GameInfo API.
   • It saves data in the local  /cache  folder for faster reloads.
   • All code runs locally — no data leaves your PC.
   • The fame boxes are color-coded and animated with your personal pfp.

6. RESETTING CONFIG
   • To reset everything, delete the file:
       cfg.json
   • Then refresh your browser to go through setup again.

7. TROUBLESHOOTING
   • If you see “Call to undefined function curl_init()”:
       → cURL isn’t enabled. Double-check Step 2 above.
   • If you see SSL or certificate errors:
       → They are safe to ignore for local use (the script bypasses SSL checks).
   • If the page shows “No kills found”:
       → The API may be down or your player ID is invalid.

Enjoy your local Albion Dashboard :)
