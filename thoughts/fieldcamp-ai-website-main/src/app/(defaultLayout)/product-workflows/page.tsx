
import React from 'react';
import Link from 'next/link';
import { Search } from 'lucide-react';
import { Input } from '@/app/_components/ui/input';
import { getPAGESEO, getProductWorkflowCategories, getProductWorkflows } from '@/lib/api';
import Image from 'next/image';

// Get base path from environment variable
const BASE_PATH = process.env.BASE_PATH || '';

interface WorkflowCategory {
  name: string;
  slug: string;
}

interface WorkflowTemplate {
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
    nodes: WorkflowCategory[];
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


export default async function WorkflowTemplatesPage({
  searchParams,
}: {
  searchParams?: { [key: string]: string | string[] | undefined };
}) {
   let templates: WorkflowTemplate[] = [];
   let categories: WorkflowCategory[] = [];
   
   try {
     templates = await getProductWorkflows() as WorkflowTemplate[] || [];
     categories = await getProductWorkflowCategories() as WorkflowCategory[] || [];
   } catch (error) {
     console.error('Failed to fetch product workflows data:', error);
     // Return empty arrays to allow static generation to continue
   }
   
   // Get the selected category from URL params
   const selectedCategory = searchParams?.category as string || 'all';
   
   // Filter templates based on selected category
   const filteredTemplates = selectedCategory === 'all' 
     ? templates 
     : templates?.filter((template: WorkflowTemplate) => 
         template.productWorkflowCategories?.nodes?.some(
           (cat: WorkflowCategory) => cat.slug === selectedCategory
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
                <h1 className="text-2xl font-bold text-black mb-0">Product Workflows</h1>
              </div>
              
              <nav className="space-y-1">
                <Link 
                  href={`${BASE_PATH}/product-workflows/`}
                  className={`text-sm py-1 block ${
                    selectedCategory === 'all'
                      ? 'text-black font-medium' 
                      : 'text-gray-500 hover:text-gray-700'
                  }`}
                >
                  All Categories
                </Link>
                {categories?.map((category: WorkflowCategory) => (
                  <Link
                    key={category.slug}
                    href={`${BASE_PATH}/product-workflows/category/${category.slug}/`}
                    className={`text-sm py-1 block ${
                      selectedCategory === category.slug
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
            <div className="max-w-6xl mx-auto">
              <div className="flex items-center justify-between mb-6">
                <h1 className="text-3xl font-semibold text-gray-900">
{selectedCategory === 'all' ? 'All Templates' : categories?.find(c => c.slug === selectedCategory)?.name + ' Templates'}
                </h1>
                
                {/* Search */}
                <div className="relative w-80">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                  <Input
                    placeholder="Search templates..."
                    className="pl-10 h-10 rounded-lg border-gray-200 bg-gray-50 text-sm"
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Templates Grid */}
          <div className="py-8">
            <div className="max-w-6xl mx-auto">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            {filteredTemplates && filteredTemplates.length > 0 ? (
                filteredTemplates.map((template: any, index: number) => (
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
                            src={template.featuredImage?.node?.sourceUrl}
                            alt={template.featuredImage?.node?.altText}
                            width={640}
                            height={409}
                            className="w-full h-full object-cover"
                        />
                    </div>
                    <div className="flex flex-col space-y-1.5 p-6 pb-3 pt-6 px-6">
                        <div className="flex items-start justify-between mb-3 pw-title-list">
                          <h3 className="text-lg font-semibold text-gray-900 leading-tight">{template.title}</h3>
                        </div>
                        <p className="text-gray-600 text-sm leading-relaxed">{template.seo?.description || 'Workflow template for your automation needs.'}</p>
                    </div>
                    <div className="p-6 pt-0 pb-6 px-6 flex flex-col items-start space-y-4">
                        <a href={`${BASE_PATH}/product-workflows/${template.slug}/`}>
                          <button className="gap-2 pd-work-flow-btn whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 px-4 py-2 w-full group/btn bg-blue-600 hover:bg-blue-700 text-white border-0 rounded-lg h-10 text-sm font-medium transition-all duration-200 flex items-center justify-center">
                              Check it out
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-arrow-right w-4 h-4 ml-2 group-hover/btn:translate-x-0.5 transition-transform duration-200">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                              </svg>
                          </button>
                        </a>
                    </div>
                  </div>
                ))
              ) : (
                <div className="col-span-full text-center py-12">
                  <p className="text-gray-500">No templates found in this category.</p>
                  <a href="?category=all" className="text-blue-600 hover:underline mt-2 inline-block">
                    View all templates
                  </a>
                </div>
              )}
            </div>
            </div>
          </div>
          </div>
        </div>
      </div>
    </div>

      
    );
}

export async function generateMetadata({ params}:{params:{ puid: string, uid: string }}) {
  let data = null;
  
  try {
    data = await getPAGESEO(`product-workflows`);
  } catch (error) {
    console.error('Failed to fetch SEO data for product-workflows:', error);
    // Return default metadata if API fails
    return {
      title: 'Product Workflow - Fieldcamp AI',
      description: 'Explore our collection of workflow templates to streamline your business processes.',
      robots: 'index,follow',
    };
  }
  
  return {
    title: data?.seo?.title || 'Product Workflow - Fieldcamp AI',
    description: data?.seo?.description || 'Explore our collection of workflow templates to streamline your business processes.',
    robots: data?.seo?.robots?.join(',') || 'index,follow',
    alternates: { canonical: data?.seo?.canonicalUrl },
    openGraph: {
      type: data?.seo?.openGraph?.type || 'website',
      description: data?.seo?.openGraph?.description || data?.seo?.description || 'Explore our collection of workflow templates to streamline your business processes.',
      title: data?.seo?.openGraph?.title || data?.seo?.title || 'Product Workflow - Fieldcamp AI',
      url: data?.seo?.canonicalUrl,
      images: [
        {
          url: data?.seo?.openGraph?.image?.url || "",
        },
      ],
    },
  }
}
