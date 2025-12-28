import { getPool } from '@/lib/db';
import fs from 'fs/promises';
import path from 'path';

export type LeadType = 'franchise' | 'wholesale' | 'marketplace' | 'suppliers_materials' | 'suppliers_logistics';

export interface B2BLead {
  id: number;
  lead_type: LeadType;
  full_name: string;
  phone: string;
  email: string | null;
  city: string | null;
  company_name: string | null;
  website: string | null;
  proposal: string | null;
  file_name: string | null;
  file_path: string | null;
  created_at: string;
}

export interface CreateLeadData {
  lead_type: LeadType;
  full_name: string;
  phone: string;
  email?: string;
  city?: string;
  company_name?: string;
  website?: string;
  proposal?: string;
  file_name?: string;
  file_path?: string;
}

// Ensure the b2b_leads table exists
export async function ensureB2BLeadsTable(): Promise<void> {
  const pool = getPool();
  await pool.query(`
    CREATE TABLE IF NOT EXISTS b2b_leads (
      id SERIAL PRIMARY KEY,
      lead_type VARCHAR(50) NOT NULL,
      full_name VARCHAR(255) NOT NULL,
      phone VARCHAR(50) NOT NULL,
      email VARCHAR(255),
      city VARCHAR(255),
      company_name VARCHAR(255),
      website VARCHAR(500),
      proposal TEXT,
      file_name VARCHAR(255),
      file_path VARCHAR(500),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
  `);

  // Create index for faster searches
  await pool.query(`
    CREATE INDEX IF NOT EXISTS idx_b2b_leads_created_at ON b2b_leads(created_at DESC)
  `);
  await pool.query(`
    CREATE INDEX IF NOT EXISTS idx_b2b_leads_type ON b2b_leads(lead_type)
  `);
}

// Create a new B2B lead
export async function createB2BLead(data: CreateLeadData): Promise<number> {
  await ensureB2BLeadsTable();
  const pool = getPool();

  const result = await pool.query(`
    INSERT INTO b2b_leads (lead_type, full_name, phone, email, city, company_name, website, proposal, file_name, file_path)
    VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)
    RETURNING id
  `, [
    data.lead_type,
    data.full_name,
    data.phone,
    data.email || null,
    data.city || null,
    data.company_name || null,
    data.website || null,
    data.proposal || null,
    data.file_name || null,
    data.file_path || null,
  ]);

  return result.rows[0].id;
}

// Get all B2B leads with optional filtering
export async function getB2BLeads(options?: {
  search?: string;
  type?: LeadType;
  dateFrom?: string;
  dateTo?: string;
  limit?: number;
  offset?: number;
}): Promise<{ leads: B2BLead[]; total: number }> {
  await ensureB2BLeadsTable();
  const pool = getPool();

  let whereClause = 'WHERE 1=1';
  const params: (string | number)[] = [];
  let paramIndex = 1;

  if (options?.type) {
    whereClause += ` AND lead_type = $${paramIndex}`;
    params.push(options.type);
    paramIndex++;
  }

  if (options?.search) {
    const searchPattern = `%${options.search}%`;
    whereClause += ` AND (
      full_name ILIKE $${paramIndex} OR
      phone ILIKE $${paramIndex} OR
      email ILIKE $${paramIndex} OR
      city ILIKE $${paramIndex} OR
      company_name ILIKE $${paramIndex} OR
      proposal ILIKE $${paramIndex}
    )`;
    params.push(searchPattern);
    paramIndex++;
  }

  if (options?.dateFrom) {
    whereClause += ` AND created_at >= $${paramIndex}::date`;
    params.push(options.dateFrom);
    paramIndex++;
  }

  if (options?.dateTo) {
    whereClause += ` AND created_at < ($${paramIndex}::date + interval '1 day')`;
    params.push(options.dateTo);
    paramIndex++;
  }

  // Get total count
  const countResult = await pool.query(
    `SELECT COUNT(*) as total FROM b2b_leads ${whereClause}`,
    params
  );
  const total = parseInt(countResult.rows[0].total, 10);

  // Get leads with pagination
  let query = `SELECT * FROM b2b_leads ${whereClause} ORDER BY created_at DESC`;

  if (options?.limit) {
    query += ` LIMIT $${paramIndex}`;
    params.push(options.limit);
    paramIndex++;
  }

  if (options?.offset) {
    query += ` OFFSET $${paramIndex}`;
    params.push(options.offset);
  }

  const result = await pool.query(query, params);

  return {
    leads: result.rows,
    total,
  };
}

// Get counts by type
export async function getB2BLeadsCounts(): Promise<Record<string, number>> {
  await ensureB2BLeadsTable();
  const pool = getPool();

  const result = await pool.query(`
    SELECT lead_type, COUNT(*) as count
    FROM b2b_leads
    GROUP BY lead_type
  `);

  const counts: Record<string, number> = {
    total: 0,
    franchise: 0,
    wholesale: 0,
    marketplace: 0,
    suppliers_materials: 0,
    suppliers_logistics: 0,
  };

  for (const row of result.rows) {
    counts[row.lead_type] = parseInt(row.count, 10);
    counts.total += parseInt(row.count, 10);
  }

  return counts;
}

// Delete a single lead
export async function deleteB2BLead(id: number): Promise<boolean> {
  const pool = getPool();

  // First get the lead to check for file
  const leadResult = await pool.query('SELECT file_path FROM b2b_leads WHERE id = $1', [id]);

  if (leadResult.rows.length === 0) {
    return false;
  }

  const filePath = leadResult.rows[0].file_path;

  // Delete from database
  const result = await pool.query('DELETE FROM b2b_leads WHERE id = $1', [id]);

  // Delete file if exists
  if (filePath) {
    try {
      const fullPath = path.join(process.cwd(), 'public', filePath);
      await fs.unlink(fullPath);
    } catch (err) {
      console.error('Error deleting file:', err);
    }
  }

  return result.rowCount !== null && result.rowCount > 0;
}

// Delete multiple leads
export async function deleteB2BLeads(ids: number[]): Promise<number> {
  if (ids.length === 0) return 0;

  const pool = getPool();

  // First get all file paths
  const placeholders = ids.map((_, i) => `$${i + 1}`).join(',');
  const filesResult = await pool.query(
    `SELECT file_path FROM b2b_leads WHERE id IN (${placeholders}) AND file_path IS NOT NULL`,
    ids
  );

  // Delete from database
  const deleteResult = await pool.query(
    `DELETE FROM b2b_leads WHERE id IN (${placeholders})`,
    ids
  );

  // Delete files
  for (const row of filesResult.rows) {
    if (row.file_path) {
      try {
        const fullPath = path.join(process.cwd(), 'public', row.file_path);
        await fs.unlink(fullPath);
      } catch (err) {
        console.error('Error deleting file:', err);
      }
    }
  }

  return deleteResult.rowCount || 0;
}

// Save uploaded file and return the path
export async function saveUploadedFile(file: File): Promise<{ fileName: string; filePath: string }> {
  const uploadDir = path.join(process.cwd(), 'public', 'uploads', 'b2b');

  // Ensure directory exists
  await fs.mkdir(uploadDir, { recursive: true });

  // Generate unique filename
  const timestamp = Date.now();
  const randomStr = Math.random().toString(36).substring(2, 8);
  const ext = path.extname(file.name);
  const safeFileName = `${timestamp}_${randomStr}${ext}`;
  const fullPath = path.join(uploadDir, safeFileName);

  // Write file
  const bytes = await file.arrayBuffer();
  const buffer = Buffer.from(bytes);
  await fs.writeFile(fullPath, buffer);

  return {
    fileName: file.name,
    filePath: `/uploads/b2b/${safeFileName}`,
  };
}
