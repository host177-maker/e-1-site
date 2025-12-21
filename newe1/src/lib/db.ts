import fs from 'fs';
import path from 'path';
import { User } from './auth';

const DATA_DIR = path.join(process.cwd(), 'data');

// Ensure data directory exists
function ensureDataDir() {
  if (!fs.existsSync(DATA_DIR)) {
    fs.mkdirSync(DATA_DIR, { recursive: true });
  }
}

// Generic read/write functions
function readJsonFile<T>(filename: string, defaultValue: T): T {
  ensureDataDir();
  const filePath = path.join(DATA_DIR, filename);
  try {
    if (fs.existsSync(filePath)) {
      const data = fs.readFileSync(filePath, 'utf-8');
      return JSON.parse(data);
    }
  } catch (error) {
    console.error(`Error reading ${filename}:`, error);
  }
  return defaultValue;
}

function writeJsonFile<T>(filename: string, data: T): void {
  ensureDataDir();
  const filePath = path.join(DATA_DIR, filename);
  fs.writeFileSync(filePath, JSON.stringify(data, null, 2), 'utf-8');
}

// Users
export function getUsers(): User[] {
  return readJsonFile<User[]>('users.json', []);
}

export function saveUsers(users: User[]): void {
  writeJsonFile('users.json', users);
}

export function getUserByUsername(username: string): User | undefined {
  const users = getUsers();
  return users.find(u => u.username === username);
}

export function getUserById(id: string): User | undefined {
  const users = getUsers();
  return users.find(u => u.id === id);
}

export function createUser(user: Omit<User, 'id' | 'createdAt'>): User {
  const users = getUsers();
  const newUser: User = {
    ...user,
    id: crypto.randomUUID(),
    createdAt: new Date().toISOString(),
  };
  users.push(newUser);
  saveUsers(users);
  return newUser;
}

export function updateUser(id: string, updates: Partial<User>): User | null {
  const users = getUsers();
  const index = users.findIndex(u => u.id === id);
  if (index === -1) return null;
  users[index] = { ...users[index], ...updates };
  saveUsers(users);
  return users[index];
}

export function deleteUser(id: string): boolean {
  const users = getUsers();
  const filtered = users.filter(u => u.id !== id);
  if (filtered.length === users.length) return false;
  saveUsers(filtered);
  return true;
}

// Pages
export interface Page {
  id: string;
  slug: string;
  title: string;
  content: string;
  metaDescription?: string;
  metaKeywords?: string;
  isPublished: boolean;
  createdAt: string;
  updatedAt: string;
  createdBy: string;
}

export function getPages(): Page[] {
  return readJsonFile<Page[]>('pages.json', []);
}

export function savePages(pages: Page[]): void {
  writeJsonFile('pages.json', pages);
}

export function getPageById(id: string): Page | undefined {
  return getPages().find(p => p.id === id);
}

export function getPageBySlug(slug: string): Page | undefined {
  return getPages().find(p => p.slug === slug);
}

export function createPage(page: Omit<Page, 'id' | 'createdAt' | 'updatedAt'>): Page {
  const pages = getPages();
  const now = new Date().toISOString();
  const newPage: Page = {
    ...page,
    id: crypto.randomUUID(),
    createdAt: now,
    updatedAt: now,
  };
  pages.push(newPage);
  savePages(pages);
  return newPage;
}

export function updatePage(id: string, updates: Partial<Page>): Page | null {
  const pages = getPages();
  const index = pages.findIndex(p => p.id === id);
  if (index === -1) return null;
  pages[index] = {
    ...pages[index],
    ...updates,
    updatedAt: new Date().toISOString()
  };
  savePages(pages);
  return pages[index];
}

export function deletePage(id: string): boolean {
  const pages = getPages();
  const filtered = pages.filter(p => p.id !== id);
  if (filtered.length === pages.length) return false;
  savePages(filtered);
  return true;
}

// Menu
export interface MenuItem {
  id: string;
  title: string;
  url: string;
  icon?: string;
  parentId?: string;
  order: number;
  isVisible: boolean;
}

export interface MenuConfig {
  topMenu: MenuItem[];
  mainMenu: MenuItem[];
  footerMenu: MenuItem[];
}

const defaultMenu: MenuConfig = {
  topMenu: [
    { id: '1', title: 'КАТАЛОГ', url: '/catalog', order: 1, isVisible: true },
    { id: '2', title: 'АКЦИИ', url: '/sales', icon: 'lightning', order: 2, isVisible: true },
    { id: '3', title: 'СЕРИИ', url: '/series', order: 3, isVisible: true },
    { id: '4', title: 'ШКАФЫ НА ЗАКАЗ', url: '/custom', order: 4, isVisible: true },
    { id: '5', title: 'ГАРДЕРОБНЫЕ', url: '/wardrobes', order: 5, isVisible: true },
    { id: '6', title: 'ПОКУПАТЕЛЮ', url: '/service', order: 6, isVisible: true },
    { id: '7', title: 'ОТЗЫВЫ', url: '/reviews', order: 7, isVisible: true },
    { id: '8', title: 'АДРЕСА САЛОНОВ', url: '/stores', order: 8, isVisible: true },
    { id: '9', title: 'ГЕОГРАФИЯ ДОСТАВКИ', url: '/service/delivery', order: 9, isVisible: true },
  ],
  mainMenu: [],
  footerMenu: [],
};

export function getMenu(): MenuConfig {
  return readJsonFile<MenuConfig>('menu.json', defaultMenu);
}

export function saveMenu(menu: MenuConfig): void {
  writeJsonFile('menu.json', menu);
}

export function updateMenuItem(menuType: keyof MenuConfig, id: string, updates: Partial<MenuItem>): MenuItem | null {
  const menu = getMenu();
  const items = menu[menuType];
  const index = items.findIndex(item => item.id === id);
  if (index === -1) return null;
  items[index] = { ...items[index], ...updates };
  saveMenu(menu);
  return items[index];
}

export function addMenuItem(menuType: keyof MenuConfig, item: Omit<MenuItem, 'id'>): MenuItem {
  const menu = getMenu();
  const newItem: MenuItem = {
    ...item,
    id: crypto.randomUUID(),
  };
  menu[menuType].push(newItem);
  saveMenu(menu);
  return newItem;
}

export function deleteMenuItem(menuType: keyof MenuConfig, id: string): boolean {
  const menu = getMenu();
  const items = menu[menuType];
  const filtered = items.filter(item => item.id !== id);
  if (filtered.length === items.length) return false;
  menu[menuType] = filtered;
  saveMenu(menu);
  return true;
}

// Settings
export interface SiteSettings {
  siteName: string;
  phone: string;
  email: string;
  workingHours: string;
  address: string;
  socialLinks: {
    whatsapp?: string;
    telegram?: string;
    vk?: string;
  };
}

const defaultSettings: SiteSettings = {
  siteName: 'E-1.RU',
  phone: '8-800-100-12-11',
  email: 'info@e-1.ru',
  workingHours: '7:00-20:00',
  address: '',
  socialLinks: {},
};

export function getSettings(): SiteSettings {
  return readJsonFile<SiteSettings>('settings.json', defaultSettings);
}

export function saveSettings(settings: SiteSettings): void {
  writeJsonFile('settings.json', settings);
}
