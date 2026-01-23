
import React from 'react';
import Link from 'next/link';
import { Search } from 'lucide-react';
import { Input } from '@/app/_components/ui/input';
import { notFound } from 'next/navigation';
import { getPOSTSEO, getProductWorkflowCategories, getProductWorkflows } from '@/lib/api';
import Image from 'next/image';

// Get base path from environment variable
const BASE_PATH = process.env.BASE_PATH || '';

interface ProductWorkflowCategory {
  name: string;
  slug: string;
}

interface ProductWorkflowTemplate {
  id: string;
  title: string;
  slug: string;
  featuredImage?: {
    node: {
      altText?: string;
      sourceUrl: string;
    };
  };
  productWorkflowCategories?: {
    nodes: ProductWorkflowCategory[];
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


export default async function ProductWorkflowCategoryPage({
  params,
  searchParams,
}: {
  params: { uid: string };
  searchParams?: { [key: string]: string | string[] | undefined };
}) {
   const templates = await getProductWorkflows() as ProductWorkflowTemplate[];
   const categories = await getProductWorkflowCategories() as ProductWorkflowCategory[];
   
   // Get the current category from URL params
   const currentCategory = categories.find(cat => cat.slug === params.uid);
   
   if (!currentCategory) {
     notFound();
   }
   
   // Filter templates that belong to the current category
   const filteredTemplates = templates.filter((template: ProductWorkflowTemplate) => 
     template.productWorkflowCategories?.nodes?.some(
       (cat: ProductWorkflowCategory) => cat.slug === params.uid
     )
   ) || [];

   
  return (
    <div className="min-h-screen bg-white mt-[90px]">
      <div className="max-w-6xl mx-auto px-6 py-8">
        <div className="flex flex-col md:flex-row gap-8">
          {/* Sidebar */}
          <div className="w-44 flex-shrink-0">
            <div className="sticky top-[90px]">
              <div className="mb-8">
                <h1 className="text-2xl font-bold text-black mb-0">Product Workflow</h1>
              </div>
              
              <nav className="space-y-1">
                <Link
                  href={`${BASE_PATH}/product-workflows/`}
                  className="text-sm py-1 block text-gray-500 hover:text-gray-700"
                >
                  All Categories
                </Link>
                {categories?.map((category: ProductWorkflowCategory) => (
                  <Link
                    key={category.slug}
                    href={`${BASE_PATH}/product-workflows/category/${category.slug}/`}
                    className={`text-sm py-1 block ${
                      params.uid === category.slug
                        ? 'text-black font-medium' 
                        : 'text-gray-500 hover:text-gray-700'
                    }`}
                  >
                    {category.name}
                  </Link>
                ))}
              </nav>
            </div>
          </div>

          {/* Main Content */}
          <div className="flex-1">
            {/* Header */}
            <div className="pb-8 border-b border-gray-100">
              <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <h1 className="text-3xl font-semibold text-gray-900">
                  {currentCategory.name} Templates
                </h1>
                
                {/* Search */}
                <div className="relative w-full md:w-80">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                  <Input
                    placeholder="Search templates..."
                    className="pl-10 h-10 rounded-lg border-gray-200 bg-gray-50 text-sm"
                  />
                </div>
              </div>
            </div>

            {/* Templates Grid */}
            <div className="py-8">
              {filteredTemplates.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                  {filteredTemplates.map((template: ProductWorkflowTemplate, index: number) => (
                    <div 
                          key={index} 
                          className={`text-card-foreground shadow-sm group hover:shadow-lg transition-all duration-300 border border-gray-200 bg-white rounded-2xl overflow-hidden ${
                            template.productWorkflowCategories?.nodes?.map((category: any) => `category-${category?.slug || 'uncategorized'}`).join(' ') || 'category-uncategorized'
                          }`}
                          data-categories={template.productWorkflowCategories?.nodes?.map((c: any) => c?.slug).filter(Boolean).join(' ') || 'uncategorized'}
                        >
                          {/* Debug: Show categories for this template */}
                          <div className="absolute top-2 right-2 bg-gray-100 text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                            {template.productWorkflowCategories?.nodes?.map((c: any, i: number) => (
                              <span key={i} className="mr-1">{c?.slug}</span>
                            ))}
                          </div>
                        <div className=" bg-gradient-to-br from-orange-50 to-orange-100 flex items-center justify-center">
                           <Image
                                src={template.featuredImage?.node?.sourceUrl || ''}
                                alt={template.featuredImage?.node?.altText || ''}
                                width={640}
                                height={409}
                                className="w-full h-full object-cover"
                            />
                        </div>
                        <div className="flex flex-col space-y-1.5 p-6 pb-3 pt-6 px-6">
                            <div className="flex items-start justify-between mb-3">
                              <h3 className="text-lg font-semibold text-gray-900 leading-tight">{template.title}</h3>
                            </div>
                            <p className="text-gray-600 text-sm leading-relaxed">{template.seo?.description || 'Workflow template for your automation needs.'}</p>
                        </div>
                        <div className="p-6 pt-0 pb-6 px-6 flex flex-col items-start space-y-4">
                            <a href={`${BASE_PATH}/product-workflows/${template.slug}/`}>
                              <button className="gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 px-4 py-2 w-full group/btn bg-blue-600 hover:bg-blue-700 text-white border-0 rounded-lg h-10 text-sm font-medium transition-all duration-200 flex items-center justify-center">
                                  Check it out
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-arrow-right w-4 h-4 ml-2 group-hover/btn:translate-x-0.5 transition-transform duration-200">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                  </svg>
                              </button>
                            </a>
                        </div>
                      </div>
                  ))}
                </div>
              ) : (
                <div className="text-center py-16 bg-gray-50 rounded-lg">
                  <svg 
                    className="mx-auto h-12 w-12 text-gray-400" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path 
                      strokeLinecap="round" 
                      strokeLinejoin="round" 
                      strokeWidth={1.5} 
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" 
                    />
                  </svg>
                  <h3 className="mt-2 text-lg font-medium text-gray-900">No templates found</h3>
                  <p className="mt-1 text-gray-500">We couldn't find any templates in this category.</p>
                  <div className="mt-6">
                    <Link
                      href={`${BASE_PATH}/product-workflows/`}
                      className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      Browse all templates
                    </Link>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export async function generateMetadata({ params}:{params:{ puid: string, uid: string }}) {
  const data = await getPOSTSEO('productWorkflowCategory', params.uid) 
  return {
    title: data?.seo?.title,
    description: data?.seo?.description,
    robots: data?.seo?.robots.join(','),
    alternates: { canonical: data?.seo.canonicalUrl },
    openGraph: {
      type: data?.seo?.openGraph?.type || 'website',
      description: data?.seo?.openGraph?.description || data?.seo?.description,
      title: data?.seo?.openGraph?.title || data?.seo?.title,
      url: data?.seo?.canonicalUrl,
      images: [
        {
          url: data?.seo?.openGraph?.image?.url || "",
        },
      ],
    },
  }
}
