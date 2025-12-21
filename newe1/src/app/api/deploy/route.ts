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
  commitMessage?: string;
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

  try {
    const body: DeployRequest = await request.json();
    const commitMessage = body.commitMessage || `Deploy: ${formatDate()}`;

    const logs: string[] = [];
    logs.push(`Working directory: ${repoDir}`);
    logs.push(`Platform: ${process.platform}`);

    // 1. Git pull - скачиваем последние изменения
    logs.push('\n=== Git Pull ===');
    try {
      const pullResult = await runCommand('git', ['pull'], repoDir);
      logs.push(pullResult.stdout || pullResult.stderr || 'Pull completed');
    } catch (error) {
      logs.push('Pull skipped: ' + (error as Error).message);
    }

    // 2. Git add - добавляем все изменения
    logs.push('\n=== Git Add ===');
    try {
      const addResult = await runCommand('git', ['add', '.'], repoDir);
      logs.push(addResult.stdout || 'All changes staged');
    } catch (error) {
      logs.push('Add error: ' + (error as Error).message);
    }

    // 3. Проверяем есть ли изменения для коммита
    logs.push('\n=== Git Status ===');
    let hasChanges = false;
    try {
      const statusResult = await runCommand('git', ['status', '--porcelain'], repoDir);
      logs.push(statusResult.stdout || 'No changes');
      hasChanges = statusResult.stdout.trim().length > 0;
    } catch (error) {
      logs.push('Status error: ' + (error as Error).message);
    }

    if (hasChanges) {
      // 4. Git commit
      logs.push('\n=== Git Commit ===');
      try {
        const commitResult = await runCommand('git', ['commit', '-m', commitMessage], repoDir);
        logs.push(commitResult.stdout || 'Commit created');
      } catch (error) {
        logs.push('Commit error: ' + (error as Error).message);
      }

      // 5. Git push
      logs.push('\n=== Git Push ===');
      try {
        const pushResult = await runCommand('git', ['push'], repoDir);
        logs.push(pushResult.stdout || pushResult.stderr || 'Push completed');
      } catch (error) {
        logs.push('Push note: ' + (error as Error).message);
      }
    } else {
      logs.push('No changes to commit');
    }

    // 6. Rebuild Next.js (в newe1 директории)
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

    // 7. Перезапуск Node.js сервера
    logs.push('\n=== Restarting Node.js ===');
    const isWindows = process.platform === 'win32';

    if (isWindows) {
      // На Windows - пробуем pm2
      try {
        await runCommand('pm2', ['restart', 'newe1'], newe1Dir);
        logs.push('PM2 restart completed');
      } catch {
        try {
          await runCommand('pm2', ['restart', 'all'], newe1Dir);
          logs.push('PM2 restart all completed');
        } catch {
          logs.push('Note: Manual server restart may be required');
          logs.push('Run: pm2 restart all OR restart the Node.js process');
        }
      }
    } else {
      // На Linux
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
      message: `Деплой выполнен! Коммит: "${commitMessage}"`,
      details: logs.join('\n'),
    });

  } catch (error) {
    return NextResponse.json({
      success: false,
      message: 'Ошибка при деплое',
      details: `Error: ${error instanceof Error ? error.message : 'Unknown error'}\nRepo dir: ${repoDir}`,
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
    usage: 'POST /api/deploy with optional { commitMessage: "your message" }',
  });
}
