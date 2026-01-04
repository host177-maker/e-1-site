import { NextResponse } from 'next/server';
import { getCurrentAdmin } from '@/lib/auth';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

export async function GET() {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    const pool = getPool();

    // Check which tables exist
    const tableCheck = await pool.query(`
      SELECT table_name FROM information_schema.tables
      WHERE table_schema = 'public' AND table_name IN (
        'reviews', 'promotions', 'banners', 'regions', 'cities', 'salons',
        'admin_users', 'cart_orders', 'warehouses'
      )
    `);
    const existingTables = new Set(tableCheck.rows.map(r => r.table_name));
    const has = (name: string) => existingTables.has(name);

    // Default empty results
    const emptyCount = { rows: [{ total: 0, active: 0 }] };
    const emptyReviews = { rows: [{ active: 0, pending: 0, total: 0 }] };
    const emptyPromotions = { rows: [{ active_count: 0, nearest_end_date: null, farthest_end_date: null }] };
    const emptyBanners = { rows: [{ active: 0, inactive: 0 }] };
    const emptyOrders = { rows: [{ today_count: 0, today_sum: 0, yesterday_count: 0, yesterday_sum: 0, week_count: 0, week_sum: 0, month_count: 0, month_sum: 0 }] };
    const emptyUsers = { rows: [{ total: 0 }] };
    const emptyInactiveUsers = { rows: [] };

    // Execute all queries in parallel
    const [
      reviewsResult,
      promotionsResult,
      bannersResult,
      regionsResult,
      citiesResult,
      salonsResult,
      usersResult,
      inactiveUsersResult,
      ordersResult,
      warehousesResult,
      citiesWithoutWarehousesResult
    ] = await Promise.all([
      // Reviews counts
      has('reviews')
        ? pool.query(`
            SELECT
              COUNT(*) FILTER (WHERE is_active = true AND (is_rejected = false OR is_rejected IS NULL)) as active,
              COUNT(*) FILTER (WHERE is_active = false AND (is_rejected = false OR is_rejected IS NULL)) as pending,
              COUNT(*) as total
            FROM reviews
          `)
        : Promise.resolve(emptyReviews),

      // Promotions: active count, nearest end date, farthest end date
      has('promotions')
        ? pool.query(`
            SELECT
              COUNT(*) FILTER (WHERE is_active = true AND end_date >= CURRENT_DATE) as active_count,
              MIN(end_date) FILTER (WHERE is_active = true AND end_date >= CURRENT_DATE) as nearest_end_date,
              MAX(end_date) FILTER (WHERE is_active = true AND end_date >= CURRENT_DATE) as farthest_end_date
            FROM promotions
          `)
        : Promise.resolve(emptyPromotions),

      // Banners: active/inactive counts
      has('banners')
        ? pool.query(`
            SELECT
              COUNT(*) FILTER (WHERE is_active = true) as active,
              COUNT(*) FILTER (WHERE is_active = false) as inactive
            FROM banners
          `)
        : Promise.resolve(emptyBanners),

      // Regions: total and active
      has('regions')
        ? pool.query(`
            SELECT
              COUNT(*) as total,
              COUNT(*) FILTER (WHERE is_active = true) as active
            FROM regions
          `)
        : Promise.resolve(emptyCount),

      // Cities: total and active
      has('cities')
        ? pool.query(`
            SELECT
              COUNT(*) as total,
              COUNT(*) FILTER (WHERE is_active = true) as active
            FROM cities
          `)
        : Promise.resolve(emptyCount),

      // Salons: total and active
      has('salons')
        ? pool.query(`
            SELECT
              COUNT(*) as total,
              COUNT(*) FILTER (WHERE is_active = true) as active
            FROM salons
          `)
        : Promise.resolve(emptyCount),

      // Total admin users count
      has('admin_users')
        ? pool.query(`SELECT COUNT(*) as total FROM admin_users`)
        : Promise.resolve(emptyUsers),

      // Users who haven't logged in for 3+ months
      has('admin_users')
        ? pool.query(`
            SELECT id, username, last_login, created_at
            FROM admin_users
            WHERE last_login IS NULL
               OR last_login < NOW() - INTERVAL '3 months'
            ORDER BY last_login ASC NULLS FIRST
          `)
        : Promise.resolve(emptyInactiveUsers),

      // Orders statistics (only if table exists)
      has('cart_orders')
        ? pool.query(`
            SELECT
              COUNT(*) FILTER (WHERE created_at::date = CURRENT_DATE) as today_count,
              COALESCE(SUM(total_price) FILTER (WHERE created_at::date = CURRENT_DATE), 0) as today_sum,
              COUNT(*) FILTER (WHERE created_at::date = CURRENT_DATE - INTERVAL '1 day') as yesterday_count,
              COALESCE(SUM(total_price) FILTER (WHERE created_at::date = CURRENT_DATE - INTERVAL '1 day'), 0) as yesterday_sum,
              COUNT(*) FILTER (WHERE created_at >= CURRENT_DATE - INTERVAL '7 days') as week_count,
              COALESCE(SUM(total_price) FILTER (WHERE created_at >= CURRENT_DATE - INTERVAL '7 days'), 0) as week_sum,
              COUNT(*) FILTER (WHERE created_at >= CURRENT_DATE - INTERVAL '30 days') as month_count,
              COALESCE(SUM(total_price) FILTER (WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'), 0) as month_sum
            FROM cart_orders
          `)
        : Promise.resolve(emptyOrders),

      // Warehouses count (only if table exists)
      has('warehouses')
        ? pool.query(`
            SELECT
              COUNT(*) as total,
              COUNT(*) FILTER (WHERE is_active = true) as active
            FROM warehouses
          `)
        : Promise.resolve(emptyCount),

      // Cities without warehouses (only if warehouses table exists)
      has('warehouses') && has('cities')
        ? pool.query(`
            SELECT COUNT(*) as count
            FROM cities c
            WHERE c.is_active = true
              AND (c.warehouse_id IS NULL OR NOT EXISTS (
                SELECT 1 FROM warehouses w
                WHERE w.id = c.warehouse_id AND w.is_active = true
              ))
          `)
        : Promise.resolve({ rows: [{ count: 0 }] })
    ]);

    // Calculate days until promotion ends
    const now = new Date();
    const nearestEndDate = promotionsResult.rows[0].nearest_end_date;
    const farthestEndDate = promotionsResult.rows[0].farthest_end_date;

    let daysToNearest = null;
    let daysToFarthest = null;

    if (nearestEndDate) {
      const nearest = new Date(nearestEndDate);
      daysToNearest = Math.ceil((nearest.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    }

    if (farthestEndDate) {
      const farthest = new Date(farthestEndDate);
      daysToFarthest = Math.ceil((farthest.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    }

    return NextResponse.json({
      success: true,
      data: {
        reviews: {
          active: parseInt(reviewsResult.rows[0].active) || 0,
          pending: parseInt(reviewsResult.rows[0].pending) || 0,
          total: parseInt(reviewsResult.rows[0].total) || 0,
        },
        promotions: {
          active: parseInt(promotionsResult.rows[0].active_count) || 0,
          daysToNearest,
          daysToFarthest,
        },
        banners: {
          active: parseInt(bannersResult.rows[0].active) || 0,
          inactive: parseInt(bannersResult.rows[0].inactive) || 0,
        },
        regions: {
          total: parseInt(regionsResult.rows[0].total) || 0,
          active: parseInt(regionsResult.rows[0].active) || 0,
        },
        cities: {
          total: parseInt(citiesResult.rows[0].total) || 0,
          active: parseInt(citiesResult.rows[0].active) || 0,
        },
        salons: {
          total: parseInt(salonsResult.rows[0].total) || 0,
          active: parseInt(salonsResult.rows[0].active) || 0,
        },
        users: {
          total: parseInt(usersResult.rows[0].total) || 0,
          inactive: inactiveUsersResult.rows,
        },
        orders: {
          today: {
            count: parseInt(ordersResult.rows[0].today_count) || 0,
            sum: parseFloat(ordersResult.rows[0].today_sum) || 0,
          },
          yesterday: {
            count: parseInt(ordersResult.rows[0].yesterday_count) || 0,
            sum: parseFloat(ordersResult.rows[0].yesterday_sum) || 0,
          },
          week: {
            count: parseInt(ordersResult.rows[0].week_count) || 0,
            sum: parseFloat(ordersResult.rows[0].week_sum) || 0,
          },
          month: {
            count: parseInt(ordersResult.rows[0].month_count) || 0,
            sum: parseFloat(ordersResult.rows[0].month_sum) || 0,
          },
        },
        warehouses: {
          total: parseInt(warehousesResult.rows[0].total) || 0,
          active: parseInt(warehousesResult.rows[0].active) || 0,
        },
        citiesWithoutWarehouses: parseInt(citiesWithoutWarehousesResult.rows[0].count) || 0,
      },
    });
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
