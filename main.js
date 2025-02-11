const { app, BrowserWindow } = require('electron');
const path = require('path');
const exec = require('child_process').exec;

// Create the browser window
function createWindow () {
  const win = new BrowserWindow({
    width: 800,
    height: 600,
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      nodeIntegration: true,
      contextIsolation: false
    }
  });

  // Load the local web application
  win.loadURL('http://localhost');  // Adjust URL if needed
}

// Start the local PHP server
app.whenReady().then(() => {
  exec('path_to_xampp\\php\\php.exe -S localhost:80 -t path_to_xampp\\htdocs\\R&WInvoice', (err, stdout, stderr) => {
    if (err) {
      console.error(`Error starting PHP server: ${err}`);
      return;
    }
    console.log(`PHP server output: ${stdout}`);
  });

  createWindow();

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    }
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});
