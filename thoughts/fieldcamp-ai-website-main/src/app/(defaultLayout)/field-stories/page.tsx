import React from 'react';
import Link from 'next/link';
import { getFieldStories } from '@/lib/api';
import './field-stories.scss';

interface FieldStory {
  id: string;
  title: string;
  slug: string;
  uri: string;
  fieldStorieslable?: {
    categoryLable?: string;
  };
  seo?: {
    canonicalUrl?: string;
    title?: string;
    robots?: string[];
    description?: string;
    jsonLd?: {
      raw: string;
    };
  };
}

type SearchParams = {
  [key: string]: string | string[] | undefined;
};


export default async function FieldStoriesPage({
  searchParams,
}: {
  searchParams?: { [key: string]: string | string[] | undefined };
}) {
   const fieldStories = await getFieldStories() || [];

  return (

<div className="field-stories-page min-h-screen bg-white mt-[90px]">
      <div className="max-w-4xl mx-auto px-6 py-16">
        {/* Header */}
        <div className="text-center mb-16">
          <h1 className="text-5xl md:text-6xl font-normal text-black tracking-tight">
            Field Stories
          </h1>
        </div>

        {/* Field Stories List */}
        <div className="field-stories-list">
          {fieldStories && fieldStories.length > 0 ? (
            <div className="space-y-0">
              {fieldStories.map((fieldStory: any, index: number) => (
                <Link
                  key={index}
                  href={fieldStory.uri}
                  className="field-story-item block py-8"
                >
                  <div className="flex items-center justify-between">
                    <h2 className="text-2xl md:text-3xl font-normal text-black">
                      {fieldStory.title}
                    </h2>
                    {fieldStory.fieldStorieslable?.categoryLable && (
                      <span className="text-base md:text-lg text-amber-700 whitespace-nowrap ml-6">
                        {fieldStory.fieldStorieslable.categoryLable}
                      </span>
                    )}
                  </div>
                </Link>
              ))}
            </div>
          ) : (
            <div className="text-center py-12">
              <p className="text-gray-500 text-lg">No field stories available at the moment.</p>
            </div>
          )}
        </div>
      </div>
    </div>

    );
}

export async function generateMetadata() {
  return {
    title: 'Field Stories',
    description: 'Field stories for your automation needs.',
  }
}
