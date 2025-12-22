const { spawn } = require('child_process');
const path = require('path');

const isWindows = process.platform === 'win32';
const npm = isWindows ? 'npm.cmd' : 'npm';

const child = spawn(npm, ['run', 'start'], {
  cwd: __dirname,
  stdio: 'inherit',
  shell: isWindows
});

child.on('error', (err) => {
  console.error('Failed to start:', err);
  process.exit(1);
});

child.on('close', (code) => {
  process.exit(code);
});
