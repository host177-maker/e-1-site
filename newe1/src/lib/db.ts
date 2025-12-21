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

// Banners
export interface Banner {
  id: string;
  image: string;
  alt: string;
  link: string;
  order: number;
  isActive: boolean;
}

const defaultBanners: Banner[] = [
  { id: '1', image: '/images/banner-50.jpg', alt: '-50% на весь ассортимент - Новогоднее чудо с Е1!', link: '/sales', order: 1, isActive: true },
  { id: '2', image: '/images/banner-calculator.jpg', alt: 'Рассчитайте стоимость шкафа онлайн', link: '/calculator', order: 2, isActive: true },
  { id: '3', image: '/images/banner-new-year.jpg', alt: 'Новогодняя коллекция 2025', link: '/catalog/new-year', order: 3, isActive: true },
];

export function getBanners(): Banner[] {
  return readJsonFile<Banner[]>('banners.json', defaultBanners);
}

export function saveBanners(banners: Banner[]): void {
  writeJsonFile('banners.json', banners);
}

export function addBanner(banner: Omit<Banner, 'id'>): Banner {
  const banners = getBanners();
  const newBanner: Banner = { ...banner, id: crypto.randomUUID() };
  banners.push(newBanner);
  saveBanners(banners);
  return newBanner;
}

export function updateBanner(id: string, updates: Partial<Banner>): Banner | null {
  const banners = getBanners();
  const index = banners.findIndex(b => b.id === id);
  if (index === -1) return null;
  banners[index] = { ...banners[index], ...updates };
  saveBanners(banners);
  return banners[index];
}

export function deleteBanner(id: string): boolean {
  const banners = getBanners();
  const filtered = banners.filter(b => b.id !== id);
  if (filtered.length === banners.length) return false;
  saveBanners(filtered);
  return true;
}

// Footer Config
export interface FooterColumn {
  id: string;
  title: string;
  links: { label: string; url: string }[];
}

export interface FooterConfig {
  columns: FooterColumn[];
  copyright: string;
  companyDescription: string;
  legalText: string;
}

const defaultFooter: FooterConfig = {
  columns: [
    { id: '1', title: 'ДОСТАВКА', links: [
      { label: 'Доставка и сборка', url: '/service/delivery' },
      { label: 'Сроки изготовления', url: '/service/production-time' },
      { label: 'Путь заказа', url: '/service/order-path' },
    ]},
    { id: '2', title: 'ПОМОЩЬ В ВЫБОРЕ', links: [
      { label: 'Каталог', url: '/catalog' },
      { label: 'Серии шкафов', url: '/series' },
      { label: 'Калькулятор', url: '/calculator' },
      { label: 'Шкаф на заказ', url: '/custom' },
      { label: 'Подобрать двери', url: '/doors' },
      { label: 'Акции', url: '/sales' },
    ]},
    { id: '3', title: 'О КОМПАНИИ', links: [
      { label: 'О фабрике', url: '/about' },
      { label: 'Отзывы', url: '/reviews' },
      { label: 'Адреса салонов', url: '/stores' },
      { label: 'Вакансии', url: '/jobs' },
      { label: 'Контакты', url: '/contacts' },
    ]},
    { id: '4', title: 'ПОКУПКА И ГАРАНТИИ', links: [
      { label: 'Как купить', url: '/how-to-buy' },
      { label: 'Оплата', url: '/payment' },
      { label: 'Кредит и рассрочка', url: '/credit' },
      { label: 'Гарантии', url: '/warranty' },
    ]},
  ],
  copyright: '© 2007–2025 E-1.RU',
  companyDescription: 'Мебельная компания Е1 – шкафы купе в Москве',
  legalText: 'Информация на сайте носит справочный характер и не является публичной офертой.',
};

export function getFooter(): FooterConfig {
  return readJsonFile<FooterConfig>('footer.json', defaultFooter);
}

export function saveFooter(footer: FooterConfig): void {
  writeJsonFile('footer.json', footer);
}

// Messengers
export interface Messenger {
  id: string;
  name: string;
  url: string;
  icon: string;
  order: number;
  isActive: boolean;
  showInHeader: boolean;
  showInFooter: boolean;
}

const defaultMessengers: Messenger[] = [
  { id: '1', name: 'WhatsApp', url: 'https://wa.me/79384222111', icon: 'whatsapp', order: 1, isActive: true, showInHeader: true, showInFooter: true },
  { id: '2', name: 'Telegram', url: 'https://t.me/+79384222111', icon: 'telegram', order: 2, isActive: true, showInHeader: true, showInFooter: true },
  { id: '3', name: 'Max', url: 'https://max.ru/79384222111', icon: 'max', order: 3, isActive: true, showInHeader: true, showInFooter: true },
];

export function getMessengers(): Messenger[] {
  return readJsonFile<Messenger[]>('messengers.json', defaultMessengers);
}

export function saveMessengers(messengers: Messenger[]): void {
  writeJsonFile('messengers.json', messengers);
}

export function addMessenger(messenger: Omit<Messenger, 'id'>): Messenger {
  const messengers = getMessengers();
  const newMessenger: Messenger = { ...messenger, id: crypto.randomUUID() };
  messengers.push(newMessenger);
  saveMessengers(messengers);
  return newMessenger;
}

export function updateMessenger(id: string, updates: Partial<Messenger>): Messenger | null {
  const messengers = getMessengers();
  const index = messengers.findIndex(m => m.id === id);
  if (index === -1) return null;
  messengers[index] = { ...messengers[index], ...updates };
  saveMessengers(messengers);
  return messengers[index];
}

export function deleteMessenger(id: string): boolean {
  const messengers = getMessengers();
  const filtered = messengers.filter(m => m.id !== id);
  if (filtered.length === messengers.length) return false;
  saveMessengers(filtered);
  return true;
}

// Social Networks
export interface SocialNetwork {
  id: string;
  name: string;
  url: string;
  icon: string;
  order: number;
  isActive: boolean;
}

const defaultSocials: SocialNetwork[] = [
  { id: '1', name: 'ВКонтакте', url: 'https://vk.com/e_odin', icon: 'vk', order: 1, isActive: true },
  { id: '2', name: 'Rutube', url: 'https://rutube.ru/channel/e1shkafy', icon: 'rutube', order: 2, isActive: true },
  { id: '3', name: 'Одноклассники', url: 'https://ok.ru/e1shkafy', icon: 'ok', order: 3, isActive: true },
];

export function getSocials(): SocialNetwork[] {
  return readJsonFile<SocialNetwork[]>('socials.json', defaultSocials);
}

export function saveSocials(socials: SocialNetwork[]): void {
  writeJsonFile('socials.json', socials);
}

// Contacts (extended settings)
export interface ContactsConfig {
  phones: { number: string; label: string }[];
  workingHours: string;
  orderCallText: string;
  directorLinkText: string;
  directorLinkUrl: string;
}

const defaultContacts: ContactsConfig = {
  phones: [
    { number: '8-800-100-12-11', label: 'Бесплатный звонок' },
    { number: '8-938-422-21-11', label: 'Мобильный' },
  ],
  workingHours: 'с 07:00 до 20:00 мск',
  orderCallText: 'Заказать звонок',
  directorLinkText: 'Связь с директором',
  directorLinkUrl: '/contact-director',
};

export function getContacts(): ContactsConfig {
  return readJsonFile<ContactsConfig>('contacts.json', defaultContacts);
}

export function saveContacts(contacts: ContactsConfig): void {
  writeJsonFile('contacts.json', contacts);
}
