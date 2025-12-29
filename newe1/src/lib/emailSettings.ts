import { getPool } from '@/lib/db';

// Email setting keys
export const EMAIL_KEYS = {
  DIRECTOR: 'director_email',      // Почта для директора (форма обратной связи)
  SALES: 'sales_email',            // Отдел продаж интернет-магазина
  DESIGNERS: 'designers_email',    // Отдел работы с дизайнерами (+ франшиза)
  WHOLESALE: 'wholesale_email',    // Отдел оптовых продаж
  MARKETPLACE: 'marketplace_email', // Отдел по ЧИМ (маркетплейсы)
  PROCUREMENT: 'procurement_email', // Отдел закупок (поставщикам)
} as const;

export type EmailKey = typeof EMAIL_KEYS[keyof typeof EMAIL_KEYS];

/**
 * Get email address from database by key
 * Falls back to env variable if not found in database
 */
export async function getEmailByKey(key: EmailKey): Promise<string | null> {
  try {
    const pool = getPool();

    const result = await pool.query(`
      SELECT email FROM email_settings WHERE key = $1
    `, [key]);

    if (result.rows.length > 0 && result.rows[0].email) {
      return result.rows[0].email;
    }

    // Fallback to env variables for backwards compatibility
    const envFallbacks: Record<EmailKey, string | undefined> = {
      [EMAIL_KEYS.DIRECTOR]: process.env.DIR_MAIL,
      [EMAIL_KEYS.SALES]: process.env.SALE_MAIL,
      [EMAIL_KEYS.DESIGNERS]: process.env.DIZMAIL,
      [EMAIL_KEYS.WHOLESALE]: process.env.OPTMAIL,
      [EMAIL_KEYS.MARKETPLACE]: process.env.MARKETPLACE_MAIL,
      [EMAIL_KEYS.PROCUREMENT]: process.env.PROCUREMENT_MAIL,
    };

    return envFallbacks[key] || null;
  } catch (error) {
    console.error('Error fetching email setting:', error);

    // Fallback to env variables on error
    const envFallbacks: Record<EmailKey, string | undefined> = {
      [EMAIL_KEYS.DIRECTOR]: process.env.DIR_MAIL,
      [EMAIL_KEYS.SALES]: process.env.SALE_MAIL,
      [EMAIL_KEYS.DESIGNERS]: process.env.DIZMAIL,
      [EMAIL_KEYS.WHOLESALE]: process.env.OPTMAIL,
      [EMAIL_KEYS.MARKETPLACE]: process.env.MARKETPLACE_MAIL,
      [EMAIL_KEYS.PROCUREMENT]: process.env.PROCUREMENT_MAIL,
    };

    return envFallbacks[key] || null;
  }
}

/**
 * Get all email settings
 */
export async function getAllEmailSettings(): Promise<Record<EmailKey, string>> {
  const result: Record<string, string> = {};

  for (const key of Object.values(EMAIL_KEYS)) {
    const email = await getEmailByKey(key);
    result[key] = email || '';
  }

  return result as Record<EmailKey, string>;
}
