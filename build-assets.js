const fs = require('fs');
const path = require('path');

// Create build directory
const buildDir = path.join(__dirname, 'public', 'build');
if (!fs.existsSync(buildDir)) {
    fs.mkdirSync(buildDir, { recursive: true });
}

// Create assets directory
const assetsDir = path.join(buildDir, 'assets');
if (!fs.existsSync(assetsDir)) {
    fs.mkdirSync(assetsDir, { recursive: true });
}

// Read the CSS file
const cssContent = fs.readFileSync(path.join(__dirname, 'resources', 'css', 'app.css'), 'utf8');

// Read the JS file
const jsContent = fs.readFileSync(path.join(__dirname, 'resources', 'js', 'app.js'), 'utf8');

// Create simple compiled versions
const compiledCSS = `
@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
/* Tailwind CSS will be loaded via CDN in the template */
`;

const compiledJS = jsContent;

// Write compiled files
fs.writeFileSync(path.join(assetsDir, 'app.css'), compiledCSS);
fs.writeFileSync(path.join(assetsDir, 'app.js'), compiledJS);

// Create manifest file
const manifest = {
    "resources/css/app.css": {
        "file": "assets/app.css",
        "isEntry": true,
        "src": "resources/css/app.css"
    },
    "resources/js/app.js": {
        "file": "assets/app.js",
        "isEntry": true,
        "src": "resources/js/app.js"
    }
};

fs.writeFileSync(path.join(buildDir, 'manifest.json'), JSON.stringify(manifest, null, 2));

console.log('Build assets created successfully!');
console.log('Files created:');
console.log('- public/build/assets/app.css');
console.log('- public/build/assets/app.js');
console.log('- public/build/manifest.json');