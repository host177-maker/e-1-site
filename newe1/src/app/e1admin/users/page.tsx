'use client';

import { useEffect, useState, useCallback } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface AdminUser {
  id: number;
  username: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  last_login: string | null;
  created_by: number | null;
}

function formatDate(dateString: string | null): string {
  if (!dateString) return 'Никогда';
  return new Date(dateString).toLocaleString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

export default function UsersPage() {
  const [users, setUsers] = useState<AdminUser[]>([]);
  const [loading, setLoading] = useState(true);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [showPasswordModal, setShowPasswordModal] = useState<AdminUser | null>(null);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  // Create user form
  const [newUsername, setNewUsername] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [createError, setCreateError] = useState('');
  const [creating, setCreating] = useState(false);

  // Change password form
  const [changePassword, setChangePassword] = useState('');
  const [passwordError, setPasswordError] = useState('');
  const [changingPassword, setChangingPassword] = useState(false);

  const fetchUsers = useCallback(async () => {
    try {
      const response = await fetch('/api/e1admin/users');
      const data = await response.json();
      if (data.success) {
        setUsers(data.data);
      }
    } catch (error) {
      console.error('Error fetching users:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchUsers();
  }, [fetchUsers]);

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    setCreateError('');
    setCreating(true);

    try {
      const response = await fetch('/api/e1admin/users', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: newUsername, password: newPassword }),
      });

      const data = await response.json();

      if (data.success) {
        setShowCreateModal(false);
        setNewUsername('');
        setNewPassword('');
        fetchUsers();
      } else {
        setCreateError(data.error || 'Ошибка создания пользователя');
      }
    } catch {
      setCreateError('Ошибка подключения к серверу');
    } finally {
      setCreating(false);
    }
  };

  const handleChangePassword = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!showPasswordModal) return;

    setPasswordError('');
    setChangingPassword(true);

    try {
      const response = await fetch(`/api/e1admin/users/${showPasswordModal.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ password: changePassword }),
      });

      const data = await response.json();

      if (data.success) {
        setShowPasswordModal(null);
        setChangePassword('');
      } else {
        setPasswordError(data.error || 'Ошибка смены пароля');
      }
    } catch {
      setPasswordError('Ошибка подключения к серверу');
    } finally {
      setChangingPassword(false);
    }
  };

  const toggleUserStatus = async (user: AdminUser) => {
    setActionLoading(user.id);

    try {
      const response = await fetch(`/api/e1admin/users/${user.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: !user.is_active }),
      });

      const data = await response.json();

      if (data.success) {
        fetchUsers();
      } else {
        alert(data.error || 'Ошибка обновления статуса');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  const deleteUser = async (user: AdminUser) => {
    if (!confirm(`Удалить пользователя "${user.username}"?`)) return;

    setActionLoading(user.id);

    try {
      const response = await fetch(`/api/e1admin/users/${user.id}`, {
        method: 'DELETE',
      });

      const data = await response.json();

      if (data.success) {
        fetchUsers();
      } else {
        alert(data.error || 'Ошибка удаления пользователя');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  return (
    <AdminPageWrapper>
      <div className="p-6 lg:p-8">
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Пользователи</h1>
            <p className="text-gray-600 mt-1">Управление администраторами системы</p>
          </div>
          <button
            onClick={() => setShowCreateModal(true)}
            className="inline-flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white rounded-lg transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Добавить пользователя
          </button>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : (
          <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gray-900">Пользователь</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gray-900">Статус</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gray-900">Последний вход</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gray-900">Создан</th>
                    <th className="px-6 py-4 text-right text-sm font-semibold text-gray-900">Действия</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {users.map((user) => (
                    <tr key={user.id} className="hover:bg-gray-50">
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-3">
                          <div className="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                            <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                          </div>
                          <div>
                            <div className="font-medium text-gray-900">{user.username}</div>
                            <div className="text-sm text-gray-500">ID: {user.id}</div>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        {user.is_active ? (
                          <span className="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 text-sm font-medium rounded-full">
                            <span className="w-1.5 h-1.5 bg-green-500 rounded-full" />
                            Активен
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-600 text-sm font-medium rounded-full">
                            <span className="w-1.5 h-1.5 bg-gray-400 rounded-full" />
                            Деактивирован
                          </span>
                        )}
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-600">
                        {formatDate(user.last_login)}
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-600">
                        {formatDate(user.created_at)}
                      </td>
                      <td className="px-6 py-4">
                        <div className="flex items-center justify-end gap-2">
                          <button
                            onClick={() => {
                              setShowPasswordModal(user);
                              setChangePassword('');
                              setPasswordError('');
                            }}
                            className="p-2 text-gray-500 hover:text-[#7cb342] hover:bg-gray-100 rounded-lg transition-colors"
                            title="Сменить пароль"
                          >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                          </button>
                          <button
                            onClick={() => toggleUserStatus(user)}
                            disabled={actionLoading === user.id}
                            className={`p-2 rounded-lg transition-colors ${
                              user.is_active
                                ? 'text-gray-500 hover:text-orange-500 hover:bg-orange-50'
                                : 'text-gray-500 hover:text-green-600 hover:bg-green-50'
                            }`}
                            title={user.is_active ? 'Деактивировать' : 'Активировать'}
                          >
                            {actionLoading === user.id ? (
                              <svg className="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                              </svg>
                            ) : user.is_active ? (
                              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                              </svg>
                            ) : (
                              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                              </svg>
                            )}
                          </button>
                          <button
                            onClick={() => deleteUser(user)}
                            disabled={actionLoading === user.id}
                            className="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                            title="Удалить"
                          >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {users.length === 0 && (
              <div className="text-center py-12 text-gray-500">
                Нет пользователей
              </div>
            )}
          </div>
        )}
      </div>

      {/* Create user modal */}
      {showCreateModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl max-w-md w-full p-6">
            <h2 className="text-xl font-bold text-gray-900 mb-6">Новый пользователь</h2>

            <form onSubmit={handleCreate} className="space-y-4">
              {createError && (
                <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                  {createError}
                </div>
              )}

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Имя пользователя
                </label>
                <input
                  type="text"
                  value={newUsername}
                  onChange={(e) => setNewUsername(e.target.value)}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  placeholder="Минимум 3 символа"
                  required
                  minLength={3}
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Пароль
                </label>
                <input
                  type="password"
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  placeholder="Минимум 6 символов"
                  required
                  minLength={6}
                />
              </div>

              <div className="flex gap-3 pt-4">
                <button
                  type="button"
                  onClick={() => setShowCreateModal(false)}
                  className="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Отмена
                </button>
                <button
                  type="submit"
                  disabled={creating}
                  className="flex-1 px-4 py-3 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
                >
                  {creating ? 'Создание...' : 'Создать'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Change password modal */}
      {showPasswordModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl max-w-md w-full p-6">
            <h2 className="text-xl font-bold text-gray-900 mb-2">Смена пароля</h2>
            <p className="text-gray-600 mb-6">Пользователь: {showPasswordModal.username}</p>

            <form onSubmit={handleChangePassword} className="space-y-4">
              {passwordError && (
                <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                  {passwordError}
                </div>
              )}

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Новый пароль
                </label>
                <input
                  type="password"
                  value={changePassword}
                  onChange={(e) => setChangePassword(e.target.value)}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  placeholder="Минимум 6 символов"
                  required
                  minLength={6}
                />
              </div>

              <div className="flex gap-3 pt-4">
                <button
                  type="button"
                  onClick={() => setShowPasswordModal(null)}
                  className="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Отмена
                </button>
                <button
                  type="submit"
                  disabled={changingPassword}
                  className="flex-1 px-4 py-3 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
                >
                  {changingPassword ? 'Сохранение...' : 'Сохранить'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AdminPageWrapper>
  );
}
