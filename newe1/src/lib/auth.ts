import { cookies } from 'next/headers';
import { getPool } from './db';
import crypto from 'crypto';

const SESSION_COOKIE_NAME = 'e1admin_session';
const SESSION_EXPIRY_HOURS = 24;

export interface AdminUser {
  id: number;
  username: string;
  is_active: boolean;
  created_at: Date;
  updated_at: Date;
  last_login: Date | null;
  created_by: number | null;
}

interface SessionPayload {
  userId: number;
  username: string;
  exp: number;
}

// Hash password using PBKDF2
export function hashPassword(password: string): string {
  const salt = crypto.randomBytes(16).toString('hex');
  const hash = crypto.pbkdf2Sync(password, salt, 100000, 64, 'sha512').toString('hex');
  return `${salt}:${hash}`;
}

// Verify password against stored hash
export function verifyPassword(password: string, storedHash: string): boolean {
  const [salt, hash] = storedHash.split(':');
  if (!salt || !hash) return false;
  const verifyHash = crypto.pbkdf2Sync(password, salt, 100000, 64, 'sha512').toString('hex');
  return hash === verifyHash;
}

// Get or derive session encryption key
function getSessionKey(): Buffer {
  const secret = process.env.SESSION_SECRET || 'default-secret-change-in-production-please';
  return crypto.scryptSync(secret, 'salt', 32);
}

// Create session token
export function createSessionToken(userId: number, username: string): string {
  const payload: SessionPayload = {
    userId,
    username,
    exp: Date.now() + SESSION_EXPIRY_HOURS * 60 * 60 * 1000,
  };
  const data = JSON.stringify(payload);
  const key = getSessionKey();
  const iv = crypto.randomBytes(16);
  const cipher = crypto.createCipheriv('aes-256-cbc', key, iv);
  let encrypted = cipher.update(data, 'utf8', 'hex');
  encrypted += cipher.final('hex');
  return iv.toString('hex') + ':' + encrypted;
}

// Verify and decode session token
export function verifySessionToken(token: string): SessionPayload | null {
  try {
    const [ivHex, encrypted] = token.split(':');
    if (!ivHex || !encrypted) return null;

    const key = getSessionKey();
    const iv = Buffer.from(ivHex, 'hex');
    const decipher = crypto.createDecipheriv('aes-256-cbc', key, iv);
    let decrypted = decipher.update(encrypted, 'hex', 'utf8');
    decrypted += decipher.final('utf8');

    const payload: SessionPayload = JSON.parse(decrypted);

    if (payload.exp < Date.now()) {
      return null;
    }

    return payload;
  } catch {
    return null;
  }
}

// Ensure admin_users table exists - call this before any DB operations
async function ensureTable(): Promise<void> {
  const pool = getPool();
  await pool.query(`
    CREATE TABLE IF NOT EXISTS admin_users (
      id SERIAL PRIMARY KEY,
      username VARCHAR(100) UNIQUE NOT NULL,
      password_hash VARCHAR(255) NOT NULL,
      is_active BOOLEAN DEFAULT true,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      last_login TIMESTAMP WITH TIME ZONE,
      created_by INTEGER REFERENCES admin_users(id)
    )
  `);
}

// Get current admin session from cookies
export async function getAdminSession(): Promise<SessionPayload | null> {
  try {
    const cookieStore = await cookies();
    const sessionCookie = cookieStore.get(SESSION_COOKIE_NAME);
    if (!sessionCookie) return null;
    return verifySessionToken(sessionCookie.value);
  } catch {
    return null;
  }
}

// Get current admin user from session
export async function getCurrentAdmin(): Promise<AdminUser | null> {
  try {
    const session = await getAdminSession();
    if (!session) return null;

    await ensureTable();

    const pool = getPool();
    const result = await pool.query(
      `SELECT id, username, is_active, created_at, updated_at, last_login, created_by
       FROM admin_users WHERE id = $1 AND is_active = true`,
      [session.userId]
    );
    return result.rows[0] || null;
  } catch (error) {
    console.error('Error getting current admin:', error);
    return null;
  }
}

// Login admin - main entry point
export async function loginAdmin(
  username: string,
  password: string
): Promise<{ success: boolean; token?: string; error?: string }> {
  try {
    // Step 1: Ensure table exists
    await ensureTable();

    const pool = getPool();

    // Step 2: Try to find user in database
    const result = await pool.query(
      `SELECT id, username, password_hash, is_active FROM admin_users WHERE username = $1`,
      [username]
    );

    // Step 3: If user found in DB
    if (result.rows.length > 0) {
      const user = result.rows[0];

      if (!user.is_active) {
        return { success: false, error: 'Учётная запись деактивирована' };
      }

      if (!verifyPassword(password, user.password_hash)) {
        return { success: false, error: 'Неверное имя пользователя или пароль' };
      }

      // Update last login
      await pool.query(`UPDATE admin_users SET last_login = NOW() WHERE id = $1`, [user.id]);

      const token = createSessionToken(user.id, user.username);
      return { success: true, token };
    }

    // Step 4: User not in DB - check ENV credentials
    const envUsername = process.env.ADMINNAME;
    const envPassword = process.env.ADMINPASS;

    if (!envUsername || !envPassword) {
      return { success: false, error: 'Неверное имя пользователя или пароль' };
    }

    if (username !== envUsername || password !== envPassword) {
      return { success: false, error: 'Неверное имя пользователя или пароль' };
    }

    // Step 5: Create admin from ENV credentials
    const passwordHash = hashPassword(password);
    const insertResult = await pool.query(
      `INSERT INTO admin_users (username, password_hash, is_active, last_login)
       VALUES ($1, $2, true, NOW())
       RETURNING id, username`,
      [username, passwordHash]
    );

    const newUser = insertResult.rows[0];
    const token = createSessionToken(newUser.id, newUser.username);
    return { success: true, token };
  } catch (error) {
    console.error('Login error:', error);
    return { success: false, error: 'Ошибка авторизации' };
  }
}

export { SESSION_COOKIE_NAME };
