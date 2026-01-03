import { NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// Функция для создания предложного падежа города
function toPrepositional(name: string): string {
  const trimmed = name.trim();

  // Особые случаи
  const exceptions: Record<string, string> = {
    'Москва': 'Москве',
    'Санкт-Петербург': 'Санкт-Петербурге',
    'Нижний Новгород': 'Нижнем Новгороде',
    'Ростов-на-Дону': 'Ростове-на-Дону',
    'Набережные Челны': 'Набережных Челнах',
    'Великий Новгород': 'Великом Новгороде',
    'Старый Оскол': 'Старом Осколе',
    'Нижний Тагил': 'Нижнем Тагиле',
    'Каменск-Уральский': 'Каменске-Уральском',
    'Орехово-Зуево': 'Орехово-Зуево',
    'Комсомольск-на-Амуре': 'Комсомольске-на-Амуре',
    'Петропавловск-Камчатский': 'Петропавловске-Камчатском',
    'Южно-Сахалинск': 'Южно-Сахалинске',
    'Усть-Илимск': 'Усть-Илимске',
    'Сочи': 'Сочи',
    'Грозный': 'Грозном',
  };

  if (exceptions[trimmed]) {
    return exceptions[trimmed];
  }

  // Правила для окончаний
  // Женский род на -а: -а -> -е (Москва -> Москве, Уфа -> Уфе)
  if (trimmed.endsWith('а') && !trimmed.endsWith('ья')) {
    // Проверка на -ка, -га, -ха -> -ке, -ге, -хе
    return trimmed.slice(0, -1) + 'е';
  }

  // Женский род на -я: -я -> -е (Казань имеет -ь, так что отдельно)
  if (trimmed.endsWith('я')) {
    return trimmed.slice(0, -1) + 'е';
  }

  // Города на -ь (женский род): -ь -> -и (Казань -> Казани, Пермь -> Перми, Тверь -> Твери)
  if (trimmed.endsWith('ь')) {
    return trimmed.slice(0, -1) + 'и';
  }

  // Города на -й: -й -> -е (Грозный -> Грозном — но это исключение выше)
  // Обычно -ий -> -ии, -ый/-ой -> -ом
  if (trimmed.endsWith('ий')) {
    return trimmed.slice(0, -2) + 'ии';
  }
  if (trimmed.endsWith('ый') || trimmed.endsWith('ой')) {
    return trimmed.slice(0, -2) + 'ом';
  }
  if (trimmed.endsWith('й')) {
    return trimmed.slice(0, -1) + 'е';
  }

  // Города на -о (средний род): не меняется в предложном (Иваново -> Иваново, Домодедово -> Домодедово)
  if (trimmed.endsWith('о') || trimmed.endsWith('е') || trimmed.endsWith('и')) {
    return trimmed;
  }

  // Мужской род на согласную: добавляем -е (Омск -> Омске, Краснодар -> Краснодаре)
  return trimmed + 'е';
}

// POST: Auto-fill prepositional names for cities without them
export async function POST() {
  try {
    const pool = getPool();

    // Get cities without prepositional name
    const citiesResult = await pool.query(
      `SELECT id, name FROM cities WHERE name_prepositional IS NULL OR name_prepositional = ''`
    );

    let updated = 0;

    for (const city of citiesResult.rows) {
      const prepositional = toPrepositional(city.name);
      await pool.query(
        `UPDATE cities SET name_prepositional = $1 WHERE id = $2`,
        [prepositional, city.id]
      );
      updated++;
    }

    return NextResponse.json({
      success: true,
      message: `Обновлено ${updated} городов`,
      updated,
    });
  } catch (error) {
    console.error('Error auto-filling prepositional names:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
