/**
 * Скрипт инициализации первого администратора
 * Запуск: npx tsx scripts/init-admin.ts
 */

import fs from 'fs';
import path from 'path';
import crypto from 'crypto';

const DATA_DIR = path.join(process.cwd(), 'data');
const USERS_FILE = path.join(DATA_DIR, 'users.json');

interface User {
  id: string;
  username: string;
  password: string;
  role: 'admin';
  createdAt: string;
  createdBy?: string;
}

async function hashPassword(password: string): Promise<string> {
  const encoder = new TextEncoder();
  const data = encoder.encode(password + 'e1-salt-2024');
  const hashBuffer = await crypto.subtle.digest('SHA-256', data);
  const hashArray = Array.from(new Uint8Array(hashBuffer));
  return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

async function main() {
  // Создаём директорию data если её нет
  if (!fs.existsSync(DATA_DIR)) {
    fs.mkdirSync(DATA_DIR, { recursive: true });
    console.log('✓ Создана директория data/');
  }

  // Проверяем существующих пользователей
  let users: User[] = [];
  if (fs.existsSync(USERS_FILE)) {
    try {
      users = JSON.parse(fs.readFileSync(USERS_FILE, 'utf-8'));
      console.log(`ℹ Найдено пользователей: ${users.length}`);
    } catch {
      console.log('⚠ Файл users.json повреждён, будет перезаписан');
    }
  }

  // Проверяем есть ли уже пользователь linevich
  const existingUser = users.find(u => u.username === 'linevich');
  if (existingUser) {
    console.log('ℹ Пользователь linevich уже существует');
    return;
  }

  // Создаём первого администратора
  const hashedPassword = await hashPassword('admin123');
  const newUser: User = {
    id: crypto.randomUUID(),
    username: 'linevich',
    password: hashedPassword,
    role: 'admin',
    createdAt: new Date().toISOString(),
    createdBy: 'system',
  };

  users.push(newUser);
  fs.writeFileSync(USERS_FILE, JSON.stringify(users, null, 2), 'utf-8');

  console.log('✓ Создан администратор:');
  console.log('  Логин: linevich');
  console.log('  Пароль: admin123');
  console.log('');
  console.log('Вход в админку: /adminka');
}

main().catch(console.error);
