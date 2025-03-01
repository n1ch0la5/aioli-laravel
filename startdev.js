
import { execSync } from 'child_process';

const programs = [
  {
    name: 'server',
    color: 'blue',
    command: 'npm run dev',
  },
  {
    name: 'reverb',
    color: 'magenta',
    command: 'php artisan reverb:start --debug',
  },
  {
    name: 'horizon',
    color: 'yellow',
    command: 'php artisan horizon',
  },
  {
    name: 'pulse',
    color: 'green',
    command: 'php artisan pulse:check',
  },
];

// Detect platform
const isWindows = process.platform === 'win32';
const openCommand = isWindows ? 'start' : 'open';

programs.forEach((program, index) => {
  const programParams = { ...program, autoFocus: index === programs.length - 1 };
  
  if (isWindows) {
    // Windows needs quotes around the URL but not single quotes around the whole command
    execSync(`${openCommand} "vscode://open.in-terminal?config=${JSON.stringify(programParams)}"`);
  } else {
    // macOS needs single quotes around the whole URL
    execSync(`${openCommand} 'vscode://open.in-terminal?config=${JSON.stringify(programParams)}'`);
  }
});