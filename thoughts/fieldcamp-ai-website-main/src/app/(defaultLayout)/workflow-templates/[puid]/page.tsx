import { getPageBySlug, getPAGESEO, extractImageUrls, getMediaAltByUrls } from '@/lib/api';
import { notFound } from 'next/navigation';
import React from 'react';
import parse from 'html-react-parser';
import {createTransformFunction} from "@/app/_components/General/TransformHTML";

const SalesLeadsTemplate = async ({ params, searchParams }: { params: { puid: string }, searchParams: { preview?: boolean, p?: string, view?: string } }) => {

  const data = await getPageBySlug(`/workflow-templates/${params.puid}`, searchParams.preview || false, searchParams.p || '', 'workflowTemplate').catch(() => notFound());
    if (!data?.content) {
      notFound();
    }

    const imageUrls = extractImageUrls(data?.content || '');
    const altTextMap = await getMediaAltByUrls(imageUrls);

    const overViewContent = extractSideColumn(data.content, 'overview-section', altTextMap);
    const templateDetails = extractSideColumn(data.content, 'template-details', altTextMap);
    const relatedTemplates = extractSideColumn(data.content, 'related-templates', altTextMap);
    const subTitle = extractSideColumn(data.content, 'sub-title', altTextMap);
  return (
    <>
    <div className="bg-gray-50 font-sans min-h-screen pt-[52px] min-[1023px]:pt-[74px] workflow-template">
      {/* Header Navigation */}
      <div className="bg-white border-b border-gray-200 px-[20px] md:px-6 py-4">
        <div className="flex items-center text-sm text-gray-600 w-full max-w-full xl:max-w-[1245px] 2xl:max-w-[1245px] mx-auto">
          <a href="/workflow-templates/">Templates</a>
          <svg className="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
          </svg>
          <span className="text-gray-900 font-medium md:text-base text-[12px]">{data?.title}</span>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-[20px] md:px-6 py-8">
        {/* Main Header */}
        <div className="mb-6 banner flex gap-4 flex-col-reverse md:flex-row">
          <div className={`flex flex-col justify-center ${data?.featuredImage?.node?.sourceUrl ? 'md:w-[50%]' : ''}`}>
          {/* Back Button */}
          <h1 className="text-2xl lg:text-4xl font-bold text-gray-900 mb-4">{data?.title}</h1>
          {subTitle && <p className="md:text-lg text-[16px] text-gray-600 max-w-2xl">
            {subTitle}
          </p>}

          {/* Tags */}
          <div className="flex flex-wrap gap-2 mt-3">
            {data?.workflowCategories?.nodes?.map((category: any, index: number) => (
              <span key={index} className="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm"> {category.name}</span>
            ))}
          </div>
          {/* CTA Button */}
          <a href="https://app.fieldcamp.ai/signup" className="max-w-fit mt-6 bg-gray-900 text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-800 transition-colors">
            Use This Template
          </a>
          </div>
          {data?.featuredImage?.node?.sourceUrl && (
            <div className='right-image w-[100%] md:w-[50%]'>
              <img 
                src={data.featuredImage.node.sourceUrl} 
                alt={data.featuredImage.node.altText || data?.title} 
              />
            </div>
          )}
        </div>

        {/* Main Content Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Left Column - Main Content */}
          <div className="lg:col-span-2 space-y-12">
            {/* Overview Section */}
            {overViewContent}
          </div>

          {/* Right Sidebar */}
          <div className="space-y-8">
            {/* Template Details */}
            {templateDetails && 
              <div className="bg-white p-[20px] md:p-6 rounded-lg border border-gray-200">
                {templateDetails}
              </div>
            }

            {/* Related Templates */}
            {relatedTemplates && <div className="bg-white p-[20px] md:p-6 rounded-lg border border-gray-200">

              <h3 className="text-lg font-bold text-gray-900 mb-4">Related Templates</h3>
              {relatedTemplates}
            </div>
            }
          </div>
        </div>
      </div>
    </div>
    </>
  );
};

export default SalesLeadsTemplate;

export async function generateMetadata({ params}:{params:{ puid: string, uid: string }}) {
  const data = await getPAGESEO(`/workflow-templates/${params.puid}`, 'workflowTemplate') 
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

// Extract side column content
  const extractSideColumn = (content: string, sideClass: string, altTextMap?: Record<string, string>) => {
    try {
      const contentDOM = parse(content, { transform: createTransformFunction(altTextMap) });
      
      // Function to recursively search for the side div in the parsed content
      const findSideContent = (nodes: any): React.ReactNode | null => {
        if (!Array.isArray(nodes)) return null;
        
        for (const node of nodes) {
          if (node.props?.className?.includes(sideClass)) {
            return node;
          }
          
          if (node.props?.children) {
            const found: React.ReactNode | null = findSideContent(node.props.children);
            if (found) return found;
          }
        }
        
        return null;
      };
      
      // Try to extract side content if it exists
      if (contentDOM && Array.isArray(contentDOM)) {
        return findSideContent(contentDOM);
      }
      
      return null;
    } catch (error) {
      console.error(`Error extracting ${sideClass} column:`, error);
      return null;
    }
  };