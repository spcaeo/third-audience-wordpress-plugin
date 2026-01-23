import Link from 'next/link';
import Image from 'next/image';
import { Search } from 'lucide-react';
import { Input } from '@/app/_components/ui/input';
import { getPlaybooks } from '@/lib/api';


interface Playbook {
  id: string;
  title: string;
  slug: string;
  uri: string;
  content?: string;
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

export default async function PlaybooksPage() {
  let playbooks: Playbook[] = [];
  
  try {
    playbooks = await getPlaybooks() as Playbook[];
  } catch (error) {
    playbooks = [];
  }
  
  const filteredPlaybooks = playbooks.filter((playbook) => {
    if (!playbook.uri) return false;
    const segments = playbook.uri.replace(/^\/|\/$/g, '').split('/').filter(segment => segment);
    console.log(segments);
    return segments.length === 2 && segments[0] === 'playbook';
  });

  return (
    <div className="min-h-screen bg-white mt-[90px]">
      <div className="max-w-6xl mx-auto px-6 py-8">
        <div className="pb-8 border-b border-gray-100">
              <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <h1 className="text-3xl font-semibold text-gray-900">
                  All Playbooks
                </h1>
                
                <div className="relative w-full md:w-80">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                  <Input
                    placeholder="Search playbooks..."
                    className="pl-10 h-10 rounded-lg border-gray-200 bg-gray-50 text-sm"
                  />
                </div>
              </div>
            </div>

        <div className="py-8">
          {filteredPlaybooks.length > 0 ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {filteredPlaybooks.map((playbook, index) => (
                    <div 
                      key={index} 
                      className="text-card-foreground shadow-sm group hover:shadow-lg transition-all duration-300 border border-gray-200 bg-white rounded-2xl overflow-hidden"
                    >
                      {playbook.featuredImage?.node?.sourceUrl ? (
                        <div className="bg-gradient-to-br from-orange-50 to-orange-100 h-48 flex items-center justify-center">
                          <Image
                            src={playbook.featuredImage.node.sourceUrl}
                            alt={playbook.featuredImage.node.altText || playbook.title}
                            width={640}
                            height={409}
                            className="w-full h-full object-cover"
                          />
                        </div>
                      ) : (
                        <div className="bg-gradient-to-br from-orange-50 to-orange-100 h-48 flex items-center justify-center">
                          <div className="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center">
                            <svg 
                              className="w-8 h-8 text-white" 
                              fill="none" 
                              stroke="currentColor" 
                              viewBox="0 0 24 24"
                            >
                              <path 
                                strokeLinecap="round" 
                                strokeLinejoin="round" 
                                strokeWidth={2} 
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" 
                              />
                            </svg>
                          </div>
                        </div>
                      )}
                      
                      <div className="flex flex-col space-y-1.5 p-6 pb-3 pt-6 px-6">
                        <div className="flex items-start justify-between mb-3">
                          <h3 className="text-lg font-semibold text-gray-900 leading-tight">{playbook.title}</h3>
                        </div>
                        <p className="text-gray-600 text-sm leading-relaxed">
                          {playbook.seo?.description || 'Explore this playbook for detailed insights and strategies.'}
                        </p>
                      </div>
                      
                      <div className="p-6 pt-0 pb-6 px-6 flex flex-col items-start space-y-4">
                        <Link href={playbook.uri}>
                          <button className="gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 px-4 py-2 w-full group/btn bg-blue-600 hover:bg-blue-700 text-white border-0 rounded-lg h-10 text-sm font-medium transition-all duration-200 flex items-center justify-center">
                            Check it out
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="lucide lucide-arrow-right w-4 h-4 ml-2 group-hover/btn:translate-x-0.5 transition-transform duration-200">
                              <path d="M5 12h14"></path>
                              <path d="m12 5 7 7-7 7"></path>
                            </svg>
                          </button>
                        </Link>
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
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" 
                    />
                  </svg>
                  <h3 className="mt-2 text-lg font-medium text-gray-900">No playbooks found</h3>
                  <p className="mt-1 text-gray-500">
                    We couldn't find any playbooks at the moment.
                  </p>
                </div>
          )}
        </div>
      </div>
    </div>
  );
}

export async function generateMetadata() {
  return {
    title: 'Playbooks - FieldCamp',
    description: 'Explore our collection of playbooks for field service management best practices and strategies.',
    robots: 'index,follow',
    alternates: { canonical: `${process.env.NEXT_PUBLIC_FRONTEND_URL}/playbook` },
    openGraph: {
      type: 'website',
      description: 'Explore our collection of playbooks for field service management best practices and strategies.',
      title: 'Playbooks - FieldCamp',
      url: `${process.env.NEXT_PUBLIC_FRONTEND_URL}/playbook`,
    },
  }
}