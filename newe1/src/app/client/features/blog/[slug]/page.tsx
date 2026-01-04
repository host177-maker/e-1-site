import { Metadata } from 'next';
import { notFound } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';

interface BlogArticle {
  id: number;
  slug: string;
  title: string;
  category: string;
  short_description: string | null;
  content: string;
  cover_image: string | null;
  published_at: string;
  author_name: string;
  meta_title: string | null;
  meta_description: string | null;
}

interface RelatedArticle {
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

interface ArticleData {
  success: boolean;
  data: BlogArticle;
  related: RelatedArticle[];
  categories: Category[];
}

async function getArticle(slug: string): Promise<ArticleData | null> {
  try {
    const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:3000';
    const response = await fetch(`${baseUrl}/api/blog/${slug}`, {
      next: { revalidate: 60 },
    });

    if (!response.ok) {
      return null;
    }

    const data = await response.json();
    if (!data.success) {
      return null;
    }

    return data;
  } catch (error) {
    console.error('Error fetching article:', error);
    return null;
  }
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  const data = await getArticle(slug);

  if (!data) {
    return {
      title: 'Статья не найдена',
    };
  }

  const article = data.data;

  return {
    title: article.meta_title || article.title,
    description: article.meta_description || article.short_description || undefined,
    openGraph: {
      title: article.meta_title || article.title,
      description: article.meta_description || article.short_description || undefined,
      type: 'article',
      publishedTime: article.published_at,
      authors: [article.author_name],
      images: article.cover_image ? [article.cover_image] : undefined,
    },
  };
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

export default async function BlogArticlePage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const data = await getArticle(slug);

  if (!data) {
    notFound();
  }

  const { data: article, related, categories } = data;

  // JSON-LD structured data for SEO
  const jsonLd = {
    '@context': 'https://schema.org',
    '@type': 'BlogPosting',
    headline: article.title,
    description: article.short_description || article.meta_description,
    image: article.cover_image,
    datePublished: article.published_at,
    author: {
      '@type': 'Person',
      name: article.author_name,
    },
    publisher: {
      '@type': 'Organization',
      name: 'Е1 Мебельная Компания',
      logo: {
        '@type': 'ImageObject',
        url: '/images/logo.svg',
      },
    },
    mainEntityOfPage: {
      '@type': 'WebPage',
      '@id': `/client/features/blog/${article.slug}`,
    },
  };

  return (
    <>
      {/* JSON-LD structured data */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
      />

      <div className="min-h-screen bg-white">
        {/* Breadcrumbs */}
        <nav className="border-b border-gray-200" aria-label="Хлебные крошки">
          <div className="max-w-[1200px] mx-auto px-4 py-3">
            <ol className="flex items-center gap-2 text-sm" itemScope itemType="https://schema.org/BreadcrumbList">
              <li itemProp="itemListElement" itemScope itemType="https://schema.org/ListItem">
                <Link href="/" className="text-gray-500 hover:text-[#62bb46]" itemProp="item">
                  <span itemProp="name">Главная</span>
                </Link>
                <meta itemProp="position" content="1" />
              </li>
              <li className="text-gray-400">/</li>
              <li itemProp="itemListElement" itemScope itemType="https://schema.org/ListItem">
                <Link href="/client/features/blog" className="text-gray-500 hover:text-[#62bb46]" itemProp="item">
                  <span itemProp="name">Блог</span>
                </Link>
                <meta itemProp="position" content="2" />
              </li>
              <li className="text-gray-400">/</li>
              <li itemProp="itemListElement" itemScope itemType="https://schema.org/ListItem">
                <span className="text-gray-900" itemProp="name">{article.title}</span>
                <meta itemProp="position" content="3" />
              </li>
            </ol>
          </div>
        </nav>

        {/* Main content */}
        <div className="max-w-[1200px] mx-auto px-4 py-8">
          <div className="flex flex-col lg:flex-row gap-8">
            {/* Article content */}
            <article
              className="flex-1 min-w-0"
              itemScope
              itemType="https://schema.org/BlogPosting"
            >
              {/* Category */}
              <div className="text-xs text-gray-500 uppercase tracking-wide mb-2" itemProp="articleSection">
                {article.category}
              </div>

              {/* Date */}
              <time
                dateTime={article.published_at}
                className="text-sm text-[#62bb46] mb-4 block"
                itemProp="datePublished"
              >
                {formatDate(article.published_at)}
              </time>

              {/* Title */}
              <h1
                className="text-3xl lg:text-4xl font-bold text-gray-900 mb-6"
                itemProp="headline"
              >
                {article.title}
              </h1>

              {/* Cover image */}
              {article.cover_image && (
                <div className="relative aspect-[16/9] mb-8 rounded-xl overflow-hidden">
                  <Image
                    src={article.cover_image}
                    alt={article.title}
                    fill
                    className="object-cover"
                    priority
                    itemProp="image"
                  />
                </div>
              )}

              {/* Short description */}
              {article.short_description && (
                <p className="text-lg text-gray-600 mb-8 leading-relaxed" itemProp="description">
                  {article.short_description}
                </p>
              )}

              {/* Content */}
              <div
                className="prose prose-lg max-w-none prose-headings:text-gray-900 prose-p:text-gray-700 prose-a:text-[#62bb46] prose-a:no-underline hover:prose-a:underline prose-img:rounded-lg"
                itemProp="articleBody"
                dangerouslySetInnerHTML={{ __html: article.content }}
              />

              {/* Author */}
              <div className="mt-8 pt-8 border-t border-gray-200">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                    <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <div>
                    <span className="text-sm text-gray-500">Автор</span>
                    <p className="font-medium text-gray-900" itemProp="author" itemScope itemType="https://schema.org/Person">
                      <span itemProp="name">{article.author_name}</span>
                    </p>
                  </div>
                </div>
              </div>
            </article>

            {/* Sidebar */}
            <aside className="lg:w-72 flex-shrink-0">
              <div className="lg:sticky lg:top-24">
                {/* Categories */}
                <nav aria-label="Разделы блога">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">Разделы</h3>
                  <ul className="space-y-0 border-t border-gray-200">
                    {categories.map((cat) => (
                      <li key={cat.category} className="border-b border-gray-200">
                        <Link
                          href={`/client/features/blog?category=${encodeURIComponent(cat.category)}`}
                          className={`w-full flex items-center justify-between py-4 px-2 hover:bg-gray-50 transition-colors ${
                            cat.category === article.category ? 'bg-gray-50' : ''
                          }`}
                        >
                          <span className={`text-sm ${
                            cat.category === article.category ? 'font-semibold text-[#62bb46]' : 'text-gray-700'
                          }`}>
                            {cat.category}
                          </span>
                          <span className="text-sm text-gray-400">{cat.count}</span>
                        </Link>
                      </li>
                    ))}
                  </ul>
                </nav>

                {/* Related articles */}
                {related.length > 0 && (
                  <div className="mt-8">
                    <h3 className="text-lg font-semibold text-gray-900 mb-4">Похожие статьи</h3>
                    <div className="space-y-4">
                      {related.map((item) => (
                        <Link
                          key={item.id}
                          href={`/client/features/blog/${item.slug}`}
                          className="block group"
                        >
                          {item.cover_image && (
                            <div className="aspect-[4/3] relative mb-2 overflow-hidden rounded-lg">
                              <Image
                                src={item.cover_image}
                                alt={item.title}
                                fill
                                className="object-cover group-hover:scale-105 transition-transform duration-300"
                              />
                            </div>
                          )}
                          <h4 className="text-sm font-medium text-gray-900 group-hover:text-[#62bb46] transition-colors line-clamp-2">
                            {item.title}
                          </h4>
                          <time className="text-xs text-gray-500 mt-1 block">
                            {formatDate(item.published_at)}
                          </time>
                        </Link>
                      ))}
                    </div>
                  </div>
                )}

                {/* Back to blog link */}
                <div className="mt-8">
                  <Link
                    href="/client/features/blog"
                    className="inline-flex items-center gap-2 text-[#62bb46] hover:underline font-medium"
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                    </svg>
                    Все статьи блога
                  </Link>
                </div>
              </div>
            </aside>
          </div>
        </div>
      </div>
    </>
  );
}
