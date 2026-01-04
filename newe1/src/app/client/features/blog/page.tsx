'use client';

import { useEffect, useState, useCallback, Suspense } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';

interface BlogArticle {
  id: number;
  slug: string;
  title: string;
  category: string;
  short_description: string | null;
  cover_image: string | null;
  published_at: string;
}

interface Category {
  category: string;
  count: number;
}

interface YearCount {
  year: number;
  count: number;
}

const PLACEHOLDER_IMAGE = '/images/placeholder-product.svg';

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

function BlogPageContent() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const categoryFilter = searchParams?.get('category') || null;
  const yearFilter = searchParams?.get('year') || 'all';

  const [articles, setArticles] = useState<BlogArticle[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [years, setYears] = useState<YearCount[]>([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [offset, setOffset] = useState(0);
  const limit = 9;

  const fetchArticles = useCallback(async (append = false) => {
    if (append) {
      setLoadingMore(true);
    } else {
      setLoading(true);
    }

    try {
      const params = new URLSearchParams();
      params.set('limit', limit.toString());
      params.set('offset', append ? offset.toString() : '0');
      if (categoryFilter) params.set('category', categoryFilter);
      if (yearFilter && yearFilter !== 'all') params.set('year', yearFilter);

      const response = await fetch(`/api/blog?${params}`);
      const data = await response.json();

      if (data.success) {
        if (append) {
          setArticles(prev => [...prev, ...data.data]);
        } else {
          setArticles(data.data);
          setOffset(0);
        }
        setCategories(data.categories);
        setYears(data.years);
        setTotal(data.total);
      }
    } catch (error) {
      console.error('Error fetching articles:', error);
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  }, [categoryFilter, yearFilter, offset]);

  useEffect(() => {
    setOffset(0);
    fetchArticles(false);
  }, [categoryFilter, yearFilter]);

  const handleLoadMore = () => {
    const newOffset = offset + limit;
    setOffset(newOffset);
    fetchArticles(true);
  };

  const setYear = (year: string) => {
    const params = new URLSearchParams();
    if (year !== 'all') params.set('year', year);
    if (categoryFilter) params.set('category', categoryFilter);
    const query = params.toString();
    router.push(query ? `/client/features/blog?${query}` : '/client/features/blog');
  };

  const setCategory = (category: string | null) => {
    const params = new URLSearchParams();
    if (category) params.set('category', category);
    if (yearFilter !== 'all') params.set('year', yearFilter);
    const query = params.toString();
    router.push(query ? `/client/features/blog?${query}` : '/client/features/blog');
  };

  const hasMore = articles.length < total;

  // Group articles by visual layout (3 per row on desktop)
  const groupedArticles = articles.reduce((groups: BlogArticle[][], article, index) => {
    const groupIndex = Math.floor(index / 3);
    if (!groups[groupIndex]) {
      groups[groupIndex] = [];
    }
    groups[groupIndex].push(article);
    return groups;
  }, []);

  return (
    <div className="min-h-screen bg-white">
      {/* Header */}
      <div className="border-b border-gray-200">
        <div className="max-w-[1200px] mx-auto px-4 py-8">
          <h1 className="text-3xl font-bold text-gray-900">Блог</h1>
        </div>
      </div>

      {/* Year tabs */}
      <div className="border-b border-gray-200">
        <div className="max-w-[1200px] mx-auto px-4">
          <nav className="flex gap-1 overflow-x-auto py-2" aria-label="Фильтр по годам">
            <button
              onClick={() => setYear('all')}
              className={`px-4 py-2 text-sm font-medium whitespace-nowrap rounded-lg transition-colors ${
                yearFilter === 'all'
                  ? 'bg-gray-900 text-white'
                  : 'text-gray-600 hover:bg-gray-100'
              }`}
            >
              ЗА ВСЕ ВРЕМЯ
            </button>
            {years.map((y) => (
              <button
                key={y.year}
                onClick={() => setYear(y.year.toString())}
                className={`px-4 py-2 text-sm font-medium whitespace-nowrap rounded-lg transition-colors ${
                  yearFilter === y.year.toString()
                    ? 'bg-gray-900 text-white'
                    : 'text-gray-600 hover:bg-gray-100'
                }`}
              >
                {y.year}
              </button>
            ))}
          </nav>
        </div>
      </div>

      {/* Main content */}
      <div className="max-w-[1200px] mx-auto px-4 py-8">
        <div className="flex flex-col lg:flex-row gap-8">
          {/* Articles grid */}
          <main className="flex-1">
            {loading ? (
              <div className="flex justify-center py-12">
                <div className="w-10 h-10 border-4 border-[#62bb46] border-t-transparent rounded-full animate-spin" />
              </div>
            ) : articles.length === 0 ? (
              <div className="text-center py-12">
                <p className="text-gray-500">Статьи не найдены</p>
                {(categoryFilter || yearFilter !== 'all') && (
                  <button
                    onClick={() => {
                      router.push('/client/features/blog');
                    }}
                    className="mt-4 text-[#62bb46] hover:underline"
                  >
                    Сбросить фильтры
                  </button>
                )}
              </div>
            ) : (
              <>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {articles.map((article) => (
                    <article
                      key={article.id}
                      className="group"
                      itemScope
                      itemType="https://schema.org/BlogPosting"
                    >
                      <Link href={`/client/features/blog/${article.slug}`}>
                        {/* Image */}
                        <div className="aspect-[4/3] relative mb-4 overflow-hidden rounded-lg bg-gray-100">
                          {article.cover_image ? (
                            <Image
                              src={article.cover_image}
                              alt={article.title}
                              fill
                              className="object-cover group-hover:scale-105 transition-transform duration-300"
                              itemProp="image"
                            />
                          ) : (
                            <div className="w-full h-full flex items-center justify-center">
                              <svg className="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                              </svg>
                            </div>
                          )}
                        </div>

                        {/* Category */}
                        <div className="text-xs text-gray-500 uppercase tracking-wide mb-2" itemProp="articleSection">
                          {article.category}
                        </div>

                        {/* Date */}
                        <time
                          dateTime={article.published_at}
                          className="text-sm text-[#62bb46] mb-2 block"
                          itemProp="datePublished"
                        >
                          {formatDate(article.published_at)}
                        </time>

                        {/* Title */}
                        <h2
                          className="text-lg font-semibold text-gray-900 group-hover:text-[#62bb46] transition-colors line-clamp-3 mb-2"
                          itemProp="headline"
                        >
                          {article.title}
                        </h2>

                        {/* Description */}
                        {article.short_description && (
                          <p className="text-sm text-gray-600 line-clamp-3" itemProp="description">
                            {article.short_description}
                          </p>
                        )}
                      </Link>
                    </article>
                  ))}
                </div>

                {/* Load more button */}
                {hasMore && (
                  <div className="mt-8 text-center">
                    <button
                      onClick={handleLoadMore}
                      disabled={loadingMore}
                      className="px-8 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 transition-colors disabled:opacity-50"
                    >
                      {loadingMore ? (
                        <span className="flex items-center gap-2 justify-center">
                          <div className="w-5 h-5 border-2 border-current border-t-transparent rounded-full animate-spin" />
                          Загрузка...
                        </span>
                      ) : (
                        'ПОКАЗАТЬ ЕЩЕ'
                      )}
                    </button>
                  </div>
                )}
              </>
            )}
          </main>

          {/* Sidebar */}
          <aside className="lg:w-72 flex-shrink-0">
            <nav aria-label="Разделы блога">
              <ul className="space-y-0 border-t border-gray-200">
                {categories.map((cat) => (
                  <li key={cat.category} className="border-b border-gray-200">
                    <button
                      onClick={() => setCategory(categoryFilter === cat.category ? null : cat.category)}
                      className={`w-full flex items-center justify-between py-4 px-2 text-left hover:bg-gray-50 transition-colors ${
                        categoryFilter === cat.category ? 'bg-gray-50' : ''
                      }`}
                    >
                      <span className={`text-sm ${
                        categoryFilter === cat.category ? 'font-semibold text-[#62bb46]' : 'text-gray-700'
                      }`}>
                        {cat.category}
                      </span>
                      <span className="text-sm text-gray-400">{cat.count}</span>
                    </button>
                  </li>
                ))}
              </ul>
            </nav>
          </aside>
        </div>
      </div>
    </div>
  );
}

export default function BlogPage() {
  return (
    <Suspense fallback={
      <div className="min-h-screen bg-white">
        <div className="border-b border-gray-200">
          <div className="max-w-[1200px] mx-auto px-4 py-8">
            <div className="h-9 w-32 bg-gray-200 rounded animate-pulse" />
          </div>
        </div>
        <div className="flex justify-center py-12">
          <div className="w-10 h-10 border-4 border-[#62bb46] border-t-transparent rounded-full animate-spin" />
        </div>
      </div>
    }>
      <BlogPageContent />
    </Suspense>
  );
}
