import { getPageBySlug, getPAGESEO, extractImageUrls, getMediaAltByUrls } from "@/lib/api";
import SliderCode from "@/app/_components/Myslider"; // Import Myslider component
import BoxSliderCode from "@/app/_components/Boxslider";
import Singleslider from "@/app/_components/Singleslider";
import { notFound } from "next/navigation";
import parse from 'html-react-parser';
import Image from 'next/image';
import Link from 'next/link';
import InvoiceGenerator from "@/app/_components/InvoiceGenerator/InvoiceGenerator";
import EstimateGenerator from "@/app/_components/EstimateGenerator/EstimateGenerator";
import {createTransformFunction} from "@/app/_components/General/TransformHTML";
import ReceiptGenerator from "@/app/_components/ReceiptGenerator/ReceiptGenerator";
import HouseCleaningCalculator from "@/app/_components/FreeTools/houseCleaningCalculator";
import LawnCareCalculator from "@/app/_components/FreeTools/lawnCareCalculator";
import RoofingCalculator from "@/app/_components/FreeTools/roofingCalculator";
import ProfitForecastCalculator from "@/app/_components/FreeTools/profitForecaseCalculator";
import HvacCFMCalculator from "@/app/_components/FreeTools/hvacCFMCalculator";
import PipeVolumeCalculator from "@/app/_components/FreeTools/pipeVolumeCalculator";
import HvacLoadCalculator from "@/app/_components/FreeTools/hvacLoadCalculator";
import LaborCostCalculator from "@/app/_components/FreeTools/laborCostCalculator";
import AchCalculator from "@/app/_components/FreeTools/achCalculator";
import HvacDuctCalculator from "@/app/_components/FreeTools/hvacDuctCalculator";



export default async function page({ params, searchParams }: { params: { puid: string }, searchParams: { preview?: boolean, p?: string, view?: string } }) {
  
  const data = await getPageBySlug(`/free-tools/${params.puid}`, searchParams.preview || false, searchParams.p || '', 'template').catch(() => notFound());
  if (!data || (data.status !== 'publish' && !searchParams.preview)) {
    notFound();
  }
  const structuredData = {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "WebPage",
        "@id": `${data?.canonicalUrl || "https://fieldcamp.ai/"}#website`,
        "url": data?.canonicalUrl || "https://fieldcamp.ai/",
        "name": data?.seo?.title || "FieldCamp",
        "description": data?.seo?.description,
        "about": {
          "@type": "Service",
          "name": data?.seo?.title,
          "provider": {
            "@type": "Organization",
            "name": "FieldCamp",
            "url": "https://fieldcamp.ai/"
          }
        }
      },
      {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "https://fieldcamp.ai/"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "Free Tools",
            "item": "https://fieldcamp.ai/free-tools/"
          },
          {
            "@type": "ListItem",
            "position": 3,
            "name": data?.title,
            "item": data?.seo?.canonicalUrl
          }
        ]
      },
      {
        "@type": "Product",
        "name": data?.seo?.title || "FieldCamp: AI Field Service Management Software",
        "description": data?.seo?.description || "Streamline your business with FieldCamp's AI-driven field management software. Intuitive, multilingual, and built for field service professionals to enhance efficiency and reduce complexity.",
        "url": data?.seo?.canonicalUrl || "https://fieldcamp.ai/",
        "image": data?.seo?.screenshot?.url || "https://fieldcamp.ai/_next/static/media/logo.6811b83e.svg",
        "brand": {
          "@type": "Brand",
          "name": "FieldCamp"
        },
        "offers": {
          "@type": "Offer",
          "priceCurrency": "USD",
          "price": "25",
          "url": "https://fieldcamp.ai/pricing/",
          "availability": "https://schema.org/InStock"
        },
        "review": {
          "@type": "Review",
          "reviewRating": {
            "@type": "Rating",
            "ratingValue": "4.8",
            "bestRating": "5"
          },
          "author": {
            "@type": "Organization",
            "name": "Capterra"
          }
        },
        "aggregateRating": {
          "@type": "AggregateRating",
          "ratingValue": "4.8",
          "reviewCount": "150"
        }
      },
      {
        "@type": "Brand",
        "name": data?.seo?.title || "FieldCamp",
        "url": data?.seo?.canonicalUrl || "https://fieldcamp.ai/",
        "logo": data?.seo?.logo?.url || "https://fieldcamp.ai/_next/static/media/logo.6811b83e.svg",
        "description": data?.seo?.description || "FieldCamp is an AI-powered field service management software built to streamline operations for service professionals. It's intuitive, multilingual, and designed to reduce operational complexity."
      }
    ]
  };
  

  // Get alt text map for images
  const imageUrls = extractImageUrls(data?.content || '');
  const altTextMap = await getMediaAltByUrls(imageUrls);

  // Extract side column content
  const extractSideColumn = (content: string, sideClass: string) => {
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

  // Always extract both sides by default
  const topSideContent = extractSideColumn(data.content, 'top-side');
  const bottomSideContent = extractSideColumn(data.content, 'bottom-side');
  const rightSideContent = extractSideColumn(data.content, 'right-side');
  
  
  return (
    <>
      <div className="template-layout">
          <>
            <div className="max-w-1245 pt-24">
              { !topSideContent && 
              <>
              <Link href="/free-tools" className="flex items-center mb-6 text-gray-600 hover:text-black">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M19 12H5M12 19l-7-7 7-7"></path>
                </svg>
                <span className="ml-2">Back to Free Tools</span>
              </Link>

              <div className="md:flex md:flex-row">
                <div className="md:w-3/5 mb-8 md:mb-0 md:pr-10">
                  <h1 className="text-3xl md:text-4xl font-medium mb-2">{data.title}</h1>
                  <div className="border rounded-lg overflow-hidden">
                    {data.featuredImage?.node && (
                      <Image 
                        src={data.featuredImage.node.sourceUrl} 
                        alt={data.featuredImage.node.altText || data.title} 
                        width={800} 
                        height={450} 
                        className="w-full"
                      />
                    )}
                  </div>
                </div>
                <div className="md:w-2/5">
                  {rightSideContent && (
                    <div className="border rounded-lg p-6 mb-6">
                      <div className="flex justify-between mb-6">
                        <a 
                          href="#templateform"
                          className="bg-black text-white px-4 py-2 rounded hover:bg-gray-800"
                          style={{
                            scrollMarginTop: '80px' // Adjust this value based on your header height
                          }}
                        >
                          Get template
                        </a>
                      </div>
                      {rightSideContent}
                      <div className="border-t pt-6 mt-8">
                        <button className="flex items-center justify-center w-full text-gray-700 hover:text-black">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                            <polyline points="16 6 12 2 8 6"></polyline>
                            <line x1="12" y1="2" x2="12" y2="15"></line>
                          </svg>
                          <span className="ml-2">Share</span>
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              </div>
              </>
              }
              {topSideContent && (
                  <div className="">
                    {topSideContent}
                  </div>
                )}
              {data.content && data.content.includes('[invoiceGenerator]') && (
                <InvoiceGenerator preFilledData={data.templateFile.preFilledData}/>
              )}
              {data.content && data.content.includes('[receiptGenerator]') && (
                <ReceiptGenerator preFilledData={data.templateFile.preFilledData}/>
              )}
              {data.content && data.content.includes('[houseCleaningCalculator]') && (
                <HouseCleaningCalculator/>
              )}
              {data.content && data.content.includes('[lawnCareCalculator]') && (
                <LawnCareCalculator/>
              )}
              {data.content && data.content.includes('[roofingCalculator]') && (
                <RoofingCalculator/>
              )}
              {data.content && data.content.includes('[profitForecastCalculator]') && (
                <ProfitForecastCalculator/>
              )}
              {data.content && data.content.includes('[hvacCFMCalculator]') && (
                <HvacCFMCalculator/>
              )}
              {data.content && data.content.includes('[pipeVolumeCalculator]') && (
                <PipeVolumeCalculator/>
              )}
              {data.content && data.content.includes('[hvacLoadCalculator]') && (
                <HvacLoadCalculator/>
              )}
              {data.content && data.content.includes('[laborCostCalculator]') && (
                <LaborCostCalculator/>
              )}
              {data.content && data.content.includes('[achCalculator]') && (
                <AchCalculator/>
              )}
              {data.content && data.content.includes('[estimateGenerator]') && (
                <EstimateGenerator/>
              )}
              {data.content && data.content.includes('[hvacDuctCalculator]') && (
                <HvacDuctCalculator/>
              )}
              {bottomSideContent && (
                <div className="bottom-side-content">
                  {bottomSideContent}
                </div>
              )}
            </div>
            <SliderCode />
            <BoxSliderCode/>
            <Singleslider/>
          </>
      </div>
      {data?.seo?.jsonLd?.raw && <div dangerouslySetInnerHTML={{ __html: data?.seo?.jsonLd?.raw }}></div>}
      <script
        key={`jobJSON`}
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(structuredData) }}
      />
    </>
  );
}

export async function generateMetadata({ params}:{params:{ puid: string }}) {
  const data = await getPAGESEO(`/free-tools/${params.puid}`, 'template') 
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
