import React from 'react';
import Link from 'next/link';
import { Search } from 'lucide-react';
import { Input } from '@/app/_components/ui/input';
import Image from 'next/image';
import { getUsecases } from '@/lib/api';
import './use-cases.scss';

interface UseCase {
  id: string;
  title: string;
  slug: string;
  uri: string;
  featuredImage?: {
    node: {
      altText?: string;
      sourceUrl: string;
    };
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


export default async function UseCasesPage({
  searchParams,
}: {
  searchParams?: { [key: string]: string | string[] | undefined };
}) {
   const useCases = await getUsecases() || [];
   
  return (

<div className="min-h-screen bg-white mt-[90px]">
      <div className="max-w-6xl mx-auto px-6 py-8">
        {/* Header */}
        <div className="pb-8 border-b border-gray-100">
          <div className="max-w-6xl mx-auto">
            <div className="flex items-center justify-between mb-6">
              <h1 className="text-3xl font-semibold text-gray-900">
                Use Cases
              </h1>
              
              {/* Search */}
              <div className="relative w-80">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                <Input
                  placeholder="Search use cases..."
                  className="pl-10 h-10 rounded-lg border-gray-200 bg-gray-50 text-sm"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Use Cases Grid */}
        <div className="py-8">
          <div className="max-w-6xl mx-auto">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {useCases && useCases.length > 0 ? (
                useCases.map((useCase: any, index: number) => (
                  <div 
                    key={index} 
                    className="text-card-foreground shadow-sm group hover:shadow-lg transition-all duration-300 border border-gray-200 bg-white rounded-2xl overflow-hidden"
                  >
                    <div className="bg-gradient-to-br from-orange-50 to-orange-100 flex items-center justify-center border-bottom">
                      {useCase.featuredImage?.node?.sourceUrl && (
                        <Image
                          src={useCase.featuredImage.node.sourceUrl}
                          alt={useCase.featuredImage.node.altText || useCase.title}
                          width={640}
                          height={409}
                          className="w-full h-full object-cover"
                        />
                      )}
                    </div>
                    <div className="flex flex-col space-y-1.5 p-6 pb-3 pt-6 px-6">
                      <div className="flex items-start justify-between mb-3">
                        <h3 className="text-lg font-semibold text-gray-900 leading-tight">{useCase.title}</h3>
                      </div>
                      <p className="text-gray-600 text-sm leading-relaxed">{useCase.seo?.description || 'Use case for your automation needs.'}</p>
                    </div>
                    <div className="p-6 pt-0 pb-6 px-6 flex flex-col items-start space-y-4">
                      <Link 
                        href={useCase.uri}
                        className="inline-flex items-center justify-center gap-2 px-4 py-2 w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                      >
                        Check it out
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="ml-1">
                          <path d="M5 12h14"></path>
                          <path d="m12 5 7 7-7 7"></path>
                        </svg>
                      </Link>
                    </div>
                  </div>
                ))
              ) : (
                <div className="col-span-full text-center py-12">
                  <p className="text-gray-500">No use cases available at the moment.</p>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
      
    );
}

export async function generateMetadata() {
  return {
    title: 'Use Cases',
    description: 'Use cases for your automation needs.',
  }
}