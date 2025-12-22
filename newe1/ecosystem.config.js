module.exports = {
  apps: [{
    name: 'newe1',
    script: 'node_modules/next/dist/bin/next',
    args: 'start',
    interpreter: 'node',
    cwd: __dirname,
    env: {
      NODE_ENV: 'production',
      PORT: 3000
    },
    // Автоперезапуск при падении
    autorestart: true,
    max_restarts: 10,
    // Логи
    error_file: 'logs/error.log',
    out_file: 'logs/out.log',
    merge_logs: true,
    // Ожидание перед рестартом
    restart_delay: 1000
  }]
}
