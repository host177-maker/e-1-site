'use client';

import { useEffect, useState } from 'react';

interface MenuItem {
  id: string;
  title: string;
  url: string;
  icon?: string;
  order: number;
  isVisible: boolean;
}

interface MenuConfig {
  topMenu: MenuItem[];
  mainMenu: MenuItem[];
  footerMenu: MenuItem[];
}

type MenuType = keyof MenuConfig;

export default function MenuPage() {
  const [menu, setMenu] = useState<MenuConfig | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [activeTab, setActiveTab] = useState<MenuType>('topMenu');
  const [editItem, setEditItem] = useState<MenuItem | null>(null);
  const [newItem, setNewItem] = useState<Partial<MenuItem>>({
    title: '',
    url: '',
    icon: '',
    order: 0,
    isVisible: true,
  });
  const [showNewForm, setShowNewForm] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const fetchMenu = async () => {
    try {
      const res = await fetch('/api/admin/menu');
      const data = await res.json();
      setMenu(data.menu);
    } catch (error) {
      console.error('Error fetching menu:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchMenu();
  }, []);

  const handleSaveItem = async (item: MenuItem) => {
    setError('');
    setSuccess('');
    setSaving(true);

    try {
      const res = await fetch('/api/admin/menu', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          menuType: activeTab,
          ...item,
        }),
      });

      if (!res.ok) {
        const data = await res.json();
        setError(data.error || 'Ошибка сохранения');
        return;
      }

      setSuccess('Сохранено');
      setEditItem(null);
      fetchMenu();
    } catch {
      setError('Ошибка соединения');
    } finally {
      setSaving(false);
    }
  };

  const handleAddItem = async () => {
    if (!newItem.title || !newItem.url) {
      setError('Заполните название и URL');
      return;
    }

    setError('');
    setSaving(true);

    try {
      const res = await fetch('/api/admin/menu', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          menuType: activeTab,
          ...newItem,
          order: menu?.[activeTab]?.length || 0,
        }),
      });

      if (!res.ok) {
        const data = await res.json();
        setError(data.error || 'Ошибка добавления');
        return;
      }

      setSuccess('Добавлено');
      setNewItem({ title: '', url: '', icon: '', order: 0, isVisible: true });
      setShowNewForm(false);
      fetchMenu();
    } catch {
      setError('Ошибка соединения');
    } finally {
      setSaving(false);
    }
  };

  const handleDeleteItem = async (id: string) => {
    if (!confirm('Удалить этот пункт меню?')) return;

    try {
      const res = await fetch('/api/admin/menu', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ menuType: activeTab, id }),
      });

      if (res.ok) {
        fetchMenu();
      }
    } catch (error) {
      console.error('Error deleting item:', error);
    }
  };

  const handleToggleVisibility = async (item: MenuItem) => {
    await handleSaveItem({ ...item, isVisible: !item.isVisible });
  };

  const tabLabels: Record<MenuType, string> = {
    topMenu: 'Верхнее меню',
    mainMenu: 'Главное меню',
    footerMenu: 'Меню в футере',
  };

  if (loading) {
    return <div className="text-gray-600">Загрузка...</div>;
  }

  const currentItems = menu?.[activeTab] || [];

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-800 mb-6">Управление меню</h1>

      {error && (
        <div className="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
          {error}
        </div>
      )}

      {success && (
        <div className="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
          {success}
        </div>
      )}

      {/* Tabs */}
      <div className="flex space-x-4 mb-6">
        {(Object.keys(tabLabels) as MenuType[]).map((tab) => (
          <button
            key={tab}
            onClick={() => {
              setActiveTab(tab);
              setEditItem(null);
              setShowNewForm(false);
            }}
            className={`px-4 py-2 rounded-md transition-colors ${
              activeTab === tab
                ? 'bg-green-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            }`}
          >
            {tabLabels[tab]}
          </button>
        ))}
      </div>

      <div className="bg-white rounded-lg shadow-md p-6">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-lg font-semibold text-gray-800">{tabLabels[activeTab]}</h2>
          <button
            onClick={() => setShowNewForm(!showNewForm)}
            className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors"
          >
            + Добавить пункт
          </button>
        </div>

        {/* New item form */}
        {showNewForm && (
          <div className="mb-6 p-4 bg-gray-50 rounded-lg">
            <h3 className="font-medium text-gray-800 mb-4">Новый пункт меню</h3>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
              <input
                type="text"
                placeholder="Название"
                value={newItem.title}
                onChange={(e) => setNewItem({ ...newItem, title: e.target.value })}
                className="px-3 py-2 border border-gray-300 rounded-md"
              />
              <input
                type="text"
                placeholder="URL (например: /catalog)"
                value={newItem.url}
                onChange={(e) => setNewItem({ ...newItem, url: e.target.value })}
                className="px-3 py-2 border border-gray-300 rounded-md"
              />
              <input
                type="text"
                placeholder="Иконка (опционально)"
                value={newItem.icon}
                onChange={(e) => setNewItem({ ...newItem, icon: e.target.value })}
                className="px-3 py-2 border border-gray-300 rounded-md"
              />
            </div>
            <div className="flex gap-2">
              <button
                onClick={handleAddItem}
                disabled={saving}
                className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md disabled:bg-gray-400"
              >
                Добавить
              </button>
              <button
                onClick={() => setShowNewForm(false)}
                className="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md"
              >
                Отмена
              </button>
            </div>
          </div>
        )}

        {/* Items list */}
        {currentItems.length === 0 ? (
          <div className="text-gray-600 text-center py-8">
            В этом меню пока нет пунктов
          </div>
        ) : (
          <div className="space-y-2">
            {currentItems
              .sort((a, b) => a.order - b.order)
              .map((item) => (
                <div
                  key={item.id}
                  className={`flex items-center justify-between p-4 rounded-lg border ${
                    item.isVisible ? 'bg-white border-gray-200' : 'bg-gray-100 border-gray-300'
                  }`}
                >
                  {editItem?.id === item.id ? (
                    <div className="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                      <input
                        type="text"
                        value={editItem.title}
                        onChange={(e) => setEditItem({ ...editItem, title: e.target.value })}
                        className="px-3 py-2 border border-gray-300 rounded-md"
                      />
                      <input
                        type="text"
                        value={editItem.url}
                        onChange={(e) => setEditItem({ ...editItem, url: e.target.value })}
                        className="px-3 py-2 border border-gray-300 rounded-md"
                      />
                      <input
                        type="number"
                        value={editItem.order}
                        onChange={(e) => setEditItem({ ...editItem, order: parseInt(e.target.value) })}
                        className="px-3 py-2 border border-gray-300 rounded-md"
                        placeholder="Порядок"
                      />
                      <div className="flex gap-2">
                        <button
                          onClick={() => handleSaveItem(editItem)}
                          disabled={saving}
                          className="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm"
                        >
                          Сохранить
                        </button>
                        <button
                          onClick={() => setEditItem(null)}
                          className="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-2 rounded-md text-sm"
                        >
                          Отмена
                        </button>
                      </div>
                    </div>
                  ) : (
                    <>
                      <div className="flex items-center gap-4">
                        <span className="text-gray-400 text-sm w-8">{item.order}</span>
                        {item.icon && <span>{item.icon}</span>}
                        <div>
                          <div className={`font-medium ${item.isVisible ? 'text-gray-800' : 'text-gray-400'}`}>
                            {item.title}
                          </div>
                          <div className="text-sm text-gray-500">{item.url}</div>
                        </div>
                        {!item.isVisible && (
                          <span className="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                            Скрыто
                          </span>
                        )}
                      </div>
                      <div className="flex items-center gap-2">
                        <button
                          onClick={() => handleToggleVisibility(item)}
                          className="text-gray-500 hover:text-gray-700 text-sm"
                          title={item.isVisible ? 'Скрыть' : 'Показать'}
                        >
                          {item.isVisible ? '👁️' : '👁️‍🗨️'}
                        </button>
                        <button
                          onClick={() => setEditItem(item)}
                          className="text-blue-600 hover:text-blue-800 text-sm"
                        >
                          Редактировать
                        </button>
                        <button
                          onClick={() => handleDeleteItem(item.id)}
                          className="text-red-600 hover:text-red-800 text-sm"
                        >
                          Удалить
                        </button>
                      </div>
                    </>
                  )}
                </div>
              ))}
          </div>
        )}
      </div>
    </div>
  );
}
