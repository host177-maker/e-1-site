import { NextRequest, NextResponse } from 'next/server';
import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);

// Корневая директория репозитория
const REPO_DIR = '/home/user/e-1-site';
// Директория Next.js приложения
const NEWE1_DIR = '/home/user/e-1-site/newe1';

interface DeployRequest {
  commitMessage?: string;
}

function formatDate(): string {
  const now = new Date();
  const pad = (n: number) => n.toString().padStart(2, '0');

  return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
}

async function runCommand(command: string, cwd: string): Promise<{ stdout: string; stderr: string }> {
  try {
    const result = await execAsync(command, {
      cwd,
      timeout: 120000, // 2 минуты таймаут
      env: { ...process.env, GIT_TERMINAL_PROMPT: '0' }
    });
    return result;
  } catch (error: unknown) {
    const execError = error as { stdout?: string; stderr?: string; message?: string };
    throw new Error(`Command failed: ${command}\n${execError.stderr || execError.message}`);
  }
}

export async function POST(request: NextRequest) {
  try {
    const body: DeployRequest = await request.json();
    const commitMessage = body.commitMessage || `Deploy: ${formatDate()}`;

    const logs: string[] = [];

    // 1. Git pull - скачиваем последние изменения
    logs.push('=== Git Pull ===');
    try {
      const pullResult = await runCommand('git pull origin main', REPO_DIR);
      logs.push(pullResult.stdout || 'Pull completed');
      if (pullResult.stderr) logs.push(pullResult.stderr);
    } catch (error) {
      // Если нет remote main, пробуем без указания ветки
      const pullResult = await runCommand('git pull', REPO_DIR);
      logs.push(pullResult.stdout || 'Pull completed');
    }

    // 2. Git add - добавляем все изменения
    logs.push('\n=== Git Add ===');
    const addResult = await runCommand('git add .', REPO_DIR);
    logs.push(addResult.stdout || 'All changes staged');

    // 3. Проверяем есть ли изменения для коммита
    logs.push('\n=== Git Status ===');
    const statusResult = await runCommand('git status --porcelain', REPO_DIR);

    if (statusResult.stdout.trim()) {
      // 4. Git commit
      logs.push('\n=== Git Commit ===');
      const escapedMessage = commitMessage.replace(/"/g, '\\"');
      const commitResult = await runCommand(`git commit -m "${escapedMessage}"`, REPO_DIR);
      logs.push(commitResult.stdout || 'Commit created');

      // 5. Git push
      logs.push('\n=== Git Push ===');
      try {
        const pushResult = await runCommand('git push', REPO_DIR);
        logs.push(pushResult.stdout || 'Push completed');
        if (pushResult.stderr) logs.push(pushResult.stderr);
      } catch (error) {
        logs.push('Push failed (may need manual push): ' + (error as Error).message);
      }
    } else {
      logs.push('No changes to commit');
    }

    // 6. Rebuild Next.js (в newe1 директории)
    logs.push('\n=== Rebuilding Next.js ===');
    try {
      const buildResult = await runCommand('npm run build', NEWE1_DIR);
      logs.push('Build completed successfully');
      if (buildResult.stdout) {
        // Показываем только последние строки билда
        const buildLines = buildResult.stdout.split('\n').slice(-10);
        logs.push(buildLines.join('\n'));
      }
    } catch (error) {
      logs.push('Build warning: ' + (error as Error).message);
    }

    // 7. Перезапуск Node.js сервера
    logs.push('\n=== Restarting Node.js ===');
    try {
      // Пробуем разные способы перезапуска
      // Способ 1: pm2
      try {
        await runCommand('pm2 restart newe1 || pm2 restart all', NEWE1_DIR);
        logs.push('PM2 restart completed');
      } catch {
        // Способ 2: systemctl
        try {
          await runCommand('sudo systemctl restart newe1 || sudo systemctl restart nextjs', NEWE1_DIR);
          logs.push('Systemctl restart completed');
        } catch {
          // Способ 3: Отправляем сигнал текущему процессу для graceful restart
          logs.push('Note: Manual server restart may be required');
        }
      }
    } catch (error) {
      logs.push('Restart note: ' + (error as Error).message);
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
      details: error instanceof Error ? error.message : 'Unknown error',
    }, { status: 500 });
  }
}

export async function GET() {
  return NextResponse.json({
    status: 'Deploy service is running',
    usage: 'POST /api/deploy with optional { commitMessage: "your message" }',
  });
}
