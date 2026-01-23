import React from 'react';
import Link from 'next/link';
import { Search } from 'lucide-react';
import { Input } from '@/app/_components/ui/input';
import Image from 'next/image';
import { getTeams } from '@/lib/api';
import './teams.scss';

interface Team {
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


export default async function TeamsPage({
  searchParams,
}: {
  searchParams?: { [key: string]: string | string[] | undefined };
}) {
   const teams = await getTeams() || [];
   
  return (

<div className="min-h-screen bg-white mt-[90px]">
      <div className="max-w-6xl mx-auto px-6 py-8">
        {/* Header */}
        <div className="pb-8 border-b border-gray-100">
          <div className="max-w-6xl mx-auto">
            <div className="flex items-center justify-between mb-6">
              <h1 className="text-3xl font-semibold text-gray-900">
                Teams
              </h1>
              
              {/* Search */}
              <div className="relative w-80">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                <Input
                  placeholder="Search teams..."
                  className="pl-10 h-10 rounded-lg border-gray-200 bg-gray-50 text-sm"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Teams Grid */}
        <div className="py-8">
          <div className="max-w-6xl mx-auto">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {teams && teams.length > 0 ? (
                teams.map((team: any, index: number) => (
                  <div 
                    key={index} 
                    className="text-card-foreground shadow-sm group hover:shadow-lg transition-all duration-300 border border-gray-200 bg-white rounded-2xl overflow-hidden"
                  >
                    <div className="bg-gradient-to-br from-orange-50 to-orange-100 flex items-center justify-center border-bottom">
                      {team.featuredImage?.node?.sourceUrl && (
                        <Image
                          src={team.featuredImage.node.sourceUrl}
                          alt={team.featuredImage.node.altText || team.title}
                          width={640}
                          height={409}
                          className="w-full h-full object-cover"
                        />
                      )}
                    </div>
                    <div className="flex flex-col space-y-1.5 p-6 pb-3 pt-6 px-6">
                      <div className="flex items-start justify-between mb-3">
                        <h3 className="text-lg font-semibold text-gray-900 leading-tight">{team.title}</h3>
                      </div>
                      <p className="text-gray-600 text-sm leading-relaxed">{team.seo?.description || 'Team member information.'}</p>
                    </div>
                    <div className="p-6 pt-0 pb-6 px-6 flex flex-col items-start space-y-4">
                      <Link href={team.uri}>
                        <button className="gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 px-4 py-2 w-full group/btn bg-blue-600 hover:bg-blue-700 text-white border-0 rounded-lg h-10 text-sm font-medium transition-all duration-200 flex items-center justify-center">
                          View Profile
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-arrow-right w-4 h-4 ml-2 group-hover/btn:translate-x-0.5 transition-transform duration-200">
                            <path d="M5 12h14"></path>
                            <path d="m12 5 7 7-7 7"></path>
                          </svg>
                        </button>
                      </Link>
                    </div>
                  </div>
                ))
              ) : (
                <div className="col-span-full text-center py-12">
                  <p className="text-gray-500">No teams available at the moment.</p>
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
    title: 'Teams',
    description: 'Meet our team members.',
  }
}