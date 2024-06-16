
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

programs.forEach((program, index) => {
  const programParams = { ...program, autoFocus: index === programs.length - 1 };

  execSync(`open 'vscode://open.in-terminal?config=${JSON.stringify(programParams)}'`);
});