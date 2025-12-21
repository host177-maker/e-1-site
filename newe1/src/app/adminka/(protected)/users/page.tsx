'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';

interface User {
  id: string;
  username: string;
  role: string;
  createdAt: string;
  createdBy?: string;
}

export default function UsersPage() {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleteId, setDeleteId] = useState<string | null>(null);

  const fetchUsers = async () => {
    try {
      const res = await fetch('/api/admin/users');
      const data = await res.json();
      setUsers(data.users || []);
    } catch (error) {
      console.error('Error fetching users:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  const handleDelete = async (id: string) => {
    if (!confirm('Вы уверены, что хотите удалить этого пользователя?')) return;

    try {
      const res = await fetch(`/api/admin/users/${id}`, { method: 'DELETE' });
      const data = await res.json();

      if (!res.ok) {
        alert(data.error || 'Ошибка удаления');
        return;
      }

      fetchUsers();
    } catch (error) {
      console.error('Error deleting user:', error);
      alert('Ошибка удаления пользователя');
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Пользователи</h1>
        <Link
          href="/adminka/users/new"
          className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors"
        >
          + Добавить
        </Link>
      </div>

      {loading ? (
        <div className="text-gray-600">Загрузка...</div>
      ) : users.length === 0 ? (
        <div className="bg-white rounded-lg shadow-md p-8 text-center text-gray-600">
          Пользователей пока нет
        </div>
      ) : (
        <div className="bg-white rounded-lg shadow-md overflow-hidden">
          <table className="w-full">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="text-left px-6 py-3 text-sm font-medium text-gray-600">Имя пользователя</th>
                <th className="text-left px-6 py-3 text-sm font-medium text-gray-600">Роль</th>
                <th className="text-left px-6 py-3 text-sm font-medium text-gray-600">Создан</th>
                <th className="text-left px-6 py-3 text-sm font-medium text-gray-600">Кем создан</th>
                <th className="text-right px-6 py-3 text-sm font-medium text-gray-600">Действия</th>
              </tr>
            </thead>
            <tbody>
              {users.map((user) => (
                <tr key={user.id} className="border-b hover:bg-gray-50">
                  <td className="px-6 py-4 font-medium text-gray-800">{user.username}</td>
                  <td className="px-6 py-4">
                    <span className="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                      {user.role}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-gray-600 text-sm">{formatDate(user.createdAt)}</td>
                  <td className="px-6 py-4 text-gray-600 text-sm">{user.createdBy || '—'}</td>
                  <td className="px-6 py-4 text-right">
                    <Link
                      href={`/adminka/users/${user.id}`}
                      className="text-blue-600 hover:text-blue-800 mr-4"
                    >
                      Редактировать
                    </Link>
                    <button
                      onClick={() => handleDelete(user.id)}
                      className="text-red-600 hover:text-red-800"
                      disabled={deleteId === user.id}
                    >
                      Удалить
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
