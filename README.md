# Setup Instructions: Five Server & Jest in VS Code

## 1. Five Server (for Live HTML Preview)

Five Server is a VS Code extension that provides a live-reloading development server for static websites.

**To install:**
1. Open VS Code.
2. Go to the Extensions view.
3. Search for **"Five Server"**.
4. Click **Install** on the extension by "Yannick Scherer".
5. Open your `index.html` file.
6. Click the "Go Live" button in the status bar or right-click and select **"Open with Five Server"**.

---

## 2. Jest (for JavaScript Testing)

Jest is a popular testing framework for JavaScript and TypeScript.

**To install the extension (OPTIONAL):**
1. Open VS Code.
2. Go to the Extensions view.
3. Search for **"Jest"**.
4. Click **Install** on the extension by "Orta".
5. The extension will automatically detect and help you run your Jest tests.

**To install Jest in your project:**
1. Open the integrated terminal.
2. Run:
   ```
   npm install --save-dev jest
   ```

**To execute Jest:**
1. Open the integrated terminal.
2. Run:
   ```
   npx jest
   ```


---

Now you can preview your site live and run JavaScript tests directly from VS Code!
