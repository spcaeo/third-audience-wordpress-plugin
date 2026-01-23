'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { renderToStaticMarkup } from 'react-dom/server';

export const TransformRelatedTemplates = ({ html }: { html: string | React.ReactNode | null }) => {
  const [content, setContent] = useState<React.ReactNode>(null);

  useEffect(() => {
    // Only run on client
    if (typeof window === 'undefined') return;

    try {
      const htmlString =
        typeof html === 'string'
          ? html
          : html
          ? renderToStaticMarkup(html as React.ReactElement)
          : '';

      if (!htmlString) {
        setContent(null);
        return;
      }

      const parser = new DOMParser();
      const doc = parser.parseFromString(htmlString, 'text/html');
      const items = doc.querySelectorAll('li.wp-block-post');
      if (items.length === 0) {
        setContent(null);
        return;
      }

      const borderColors = [
        'border-blue-500',
        'border-green-500',
        'border-purple-500',
        'border-yellow-500'
      ];

      const nodes = Array.from(items).map((item, index) => {
        const titleElement = item.querySelector('h2.wp-block-post-title a');
        const excerptElement = item.querySelector('.wp-block-post-excerpt__excerpt');

        if (!titleElement) return null;

        const title = titleElement.textContent || '';
        const url = titleElement.getAttribute('href') || '#';
        const excerpt = excerptElement?.textContent?.trim() || '';

        return (
          <Link
            key={index}
            href={url}
            className={`block border-l-4 ${borderColors[index % borderColors.length]} pl-4 hover:bg-gray-50 p-2 rounded-r transition-colors`}
          >
            <h4 className="font-medium text-gray-900 mb-1">{title}</h4>
            {excerpt && (
              <p className="text-sm text-gray-600">
                {excerpt.length > 120 ? `${excerpt.substring(0, 120)}...` : excerpt}
              </p>
            )}
          </Link>
        );
      });

      setContent(nodes);
    } catch (error) {
      console.error('Error transforming related templates:', error);
      setContent(null);
    }
  }, [html]);

  if (!content) return null;

  return <div className="space-y-4">{content}</div>;
};