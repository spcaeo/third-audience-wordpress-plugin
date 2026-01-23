import React from 'react';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';
import Image from 'next/image';
import { getCustomers } from '@/lib/api';
import './customers.scss';

interface Customer {
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


export default async function CustomersPage({
  searchParams,
}: {
  searchParams?: { [key: string]: string | string[] | undefined };
}) {
   const customers = await getCustomers() || [];

  return (
    <div className="min-h-screen bg-white mt-[90px]">
      {/* Hero Section */}
      <section className="customers-hero">
        <div className="max-w-7xl mx-auto px-6 pt80">
          <div className="text-center max-w-4xl mx-auto">
            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
              Meet the teams who build the future
            </h1>
            <p className="text-lg sm:text-xl text-gray-600 mb-4 max-w-2xl mx-auto">
              More than 15,000 organizations trust FieldCamp, from ambitious startups to major enterprises, to streamline their field operations and drive growth.
            </p>
          </div>
        </div>
      </section>

      {/* Featured Customers Grid */}
      <section className="customers-grid-section py-12 sm:py-16 lg:py-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          {customers && customers.length > 0 ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
              {customers.map((customer: any, index: number) => (
                <Link href={customer.uri} key={customer.id || index}>
                  <div className="customer-card group cursor-pointer bg-white rounded-2xl overflow-hidden shadow-sm flex flex-col">
                    {/* Customer Image/Logo Background */}
                    <div className="customer-card-image relative bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 overflow-hidden">
                      {customer.featuredImage?.node?.sourceUrl ? (
                       <Image
                          src={customer.featuredImage.node.sourceUrl}
                          alt={customer.featuredImage.node.altText || customer.title}
                          width={400}
                          height={280}
                          className=""
                          unoptimized
                        />
                      ) : (
                        <div className="w-full h-full flex items-center justify-center">
                          <div className="w-400 h-300 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold">
                            {customer.title.charAt(0)}
                          </div>
                        </div>
                      )}

                    
                    </div>

                    {/* Customer Content */}
                    <div className="p-6 flex flex-col flex-grow">
                      <h3 className="text-xl font-semibold text-gray-900 mb-3">
                        {customer.title}
                      </h3>
                      <p className="text-gray-600 text-sm leading-relaxed flex-grow">
                        {customer.seo?.description || 'Discover how they transformed their field operations with FieldCamp.'}
                      </p>
                      <div className="flex items-center text-blue-600 font-medium text-sm group-hover:gap-2 transition-all">
                        Read More
                        <ArrowRight className="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" />
                      </div>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          ) : (
            <div className="text-center py-12">
              <p className="text-gray-500 text-lg">No customer stories available at the moment.</p>
            </div>
          )}
        </div>
      </section>
    </div>
  );
}

export async function generateMetadata() {
  return {
    title: 'Customers - FieldCamp',
    description: 'Meet the teams who build the future. More than 15,000 organizations trust FieldCamp to streamline their field operations.',
  }
}
