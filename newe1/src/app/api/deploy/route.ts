import { NextRequest, NextResponse } from 'next/server';
import { spawn } from 'child_process';
import path from 'path';

// Автоопределение директорий относительно текущего процесса
function getDirectories() {
  // newe1 директория - где запущен Next.js
  const newe1Dir = process.cwd();
  // Корневая директория репозитория - на уровень выше
  const repoDir = path.resolve(newe1Dir, '..');
  return { repoDir, newe1Dir };
}

interface DeployRequest {
  branch: string;
}

function formatDate(): string {
  const now = new Date();
  const pad = (n: number) => n.toString().padStart(2, '0');

  return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
}

async function runCommand(command: string, args: string[], cwd: string): Promise<{ stdout: string; stderr: string }> {
  return new Promise((resolve, reject) => {
    const isWindows = process.platform === 'win32';

    let proc;
    if (isWindows) {
      // На Windows запускаем через cmd
      proc = spawn('cmd', ['/c', command, ...args], {
        cwd,
        env: { ...process.env, GIT_TERMINAL_PROMPT: '0' },
        shell: false
      });
    } else {
      // На Linux/Mac напрямую
      proc = spawn(command, args, {
        cwd,
        env: { ...process.env, GIT_TERMINAL_PROMPT: '0' },
        shell: true
      });
    }

    let stdout = '';
    let stderr = '';

    proc.stdout?.on('data', (data) => {
      stdout += data.toString();
    });

    proc.stderr?.on('data', (data) => {
      stderr += data.toString();
    });

    const timeout = setTimeout(() => {
      proc.kill();
      reject(new Error(`Command timed out: ${command} ${args.join(' ')}`));
    }, 120000);

    proc.on('close', (code) => {
      clearTimeout(timeout);
      if (code === 0 || code === null) {
        resolve({ stdout, stderr });
      } else {
        reject(new Error(`Command failed (code ${code}): ${command} ${args.join(' ')}\n${stderr || stdout}`));
      }
    });

    proc.on('error', (err) => {
      clearTimeout(timeout);
      reject(new Error(`Command error: ${command} ${args.join(' ')}\n${err.message}`));
    });
  });
}

export async function POST(request: NextRequest) {
  const { repoDir, newe1Dir } = getDirectories();
  const deployTime = formatDate();

  try {
    const body: DeployRequest = await request.json();
    const branch = body.branch || 'main';

    const logs: string[] = [];
    logs.push(`Deploy: ${deployTime}`);
    logs.push(`Branch: ${branch}`);
    logs.push(`Working directory: ${repoDir}`);
    logs.push(`Platform: ${process.platform}`);

    // 1. Git fetch - получаем информацию о ветках
    logs.push('\n=== Git Fetch ===');
    try {
      const fetchResult = await runCommand('git', ['fetch', 'origin'], repoDir);
      logs.push(fetchResult.stdout || fetchResult.stderr || 'Fetch completed');
    } catch (error) {
      logs.push('Fetch note: ' + (error as Error).message);
    }

    // 2. Git checkout - переключаемся на ветку
    logs.push('\n=== Git Checkout ===');
    try {
      // Сначала пробуем просто checkout
      const checkoutResult = await runCommand('git', ['checkout', branch], repoDir);
      logs.push(checkoutResult.stdout || checkoutResult.stderr || `Switched to ${branch}`);
    } catch {
      // Если не получилось, пробуем создать локальную ветку от remote
      try {
        const checkoutResult = await runCommand('git', ['checkout', '-b', branch, `origin/${branch}`], repoDir);
        logs.push(checkoutResult.stdout || checkoutResult.stderr || `Created and switched to ${branch}`);
      } catch (error) {
        logs.push('Checkout error: ' + (error as Error).message);
      }
    }

    // 3. Git pull - обновляем код
    logs.push('\n=== Git Pull ===');
    try {
      const pullResult = await runCommand('git', ['pull', 'origin', branch], repoDir);
      logs.push(pullResult.stdout || pullResult.stderr || 'Pull completed');
    } catch (error) {
      logs.push('Pull note: ' + (error as Error).message);
    }

    // 4. Показываем текущий коммит
    logs.push('\n=== Current Commit ===');
    try {
      const logResult = await runCommand('git', ['log', '-1', '--oneline'], repoDir);
      logs.push(logResult.stdout || 'No commits');
    } catch (error) {
      logs.push('Log error: ' + (error as Error).message);
    }

    // 5. Rebuild Next.js (в newe1 директории)
    logs.push('\n=== Rebuilding Next.js ===');
    try {
      const npmCmd = process.platform === 'win32' ? 'npm.cmd' : 'npm';
      const buildResult = await runCommand(npmCmd, ['run', 'build'], newe1Dir);
      logs.push('Build completed successfully');
      if (buildResult.stdout) {
        const buildLines = buildResult.stdout.split('\n').slice(-10);
        logs.push(buildLines.join('\n'));
      }
    } catch (error) {
      logs.push('Build note: ' + (error as Error).message);
    }

    // 6. Перезапуск Node.js сервера
    logs.push('\n=== Restarting Node.js ===');
    const isWindows = process.platform === 'win32';

    if (isWindows) {
      try {
        await runCommand('pm2', ['restart', 'newe1'], newe1Dir);
        logs.push('PM2 restart completed');
      } catch {
        try {
          await runCommand('pm2', ['restart', 'all'], newe1Dir);
          logs.push('PM2 restart all completed');
        } catch {
          logs.push('Note: Manual server restart may be required');
          logs.push('Run: pm2 restart all');
        }
      }
    } else {
      try {
        await runCommand('pm2', ['restart', 'newe1'], newe1Dir);
        logs.push('PM2 restart completed');
      } catch {
        try {
          await runCommand('systemctl', ['restart', 'newe1'], newe1Dir);
          logs.push('Systemctl restart completed');
        } catch {
          logs.push('Note: Manual server restart may be required');
        }
      }
    }

    return NextResponse.json({
      success: true,
      message: `Деплой ветки "${branch}" выполнен! (${deployTime})`,
      details: logs.join('\n'),
    });

  } catch (error) {
    return NextResponse.json({
      success: false,
      message: 'Ошибка при деплое',
      details: `Error: ${error instanceof Error ? error.message : 'Unknown error'}\nRepo dir: ${repoDir}\nTime: ${deployTime}`,
    }, { status: 500 });
  }
}

export async function GET() {
  const { repoDir, newe1Dir } = getDirectories();
  return NextResponse.json({
    status: 'Deploy service is running',
    platform: process.platform,
    repoDir,
    newe1Dir,
    usage: 'POST /api/deploy with { branch: "branch-name" }',
  });
}
