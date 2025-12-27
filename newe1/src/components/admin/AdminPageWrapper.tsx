'use client';

import { useEffect, useState } from 'react';
import AdminSidebar from './AdminSidebar';

interface AdminUser {
  id: number;
  username: string;
}

interface Props {
  children: React.ReactNode;
}

export default function AdminPageWrapper({ children }: Props) {
  const [user, setUser] = useState<AdminUser | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api/e1admin/session')
      .then((res) => res.json())
      .then((data) => {
        if (data.authenticated && data.user) {
          setUser(data.user);
          setLoading(false);
        } else {
          window.location.href = '/e1admin/login';
        }
      })
      .catch(() => {
        window.location.href = '/e1admin/login';
      });
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  if (!user) {
    return null;
  }

  return (
    <div className="min-h-screen bg-gray-50 lg:flex">
      <AdminSidebar user={user} />
      <main className="flex-1 min-w-0">
        {children}
      </main>
    </div>
  );
}
