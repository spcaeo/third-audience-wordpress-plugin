import { getPageBySlug, getPAGESEO, extractImageUrls, getMediaAltByUrls } from "@/lib/api";
import SliderCode from "@/app/_components/Myslider"; // Import Myslider component
import BoxSliderCode from "@/app/_components/Boxslider";
import Singleslider from "@/app/_components/Singleslider";
import { notFound } from "next/navigation";
import parse from 'html-react-parser';
import Image from 'next/image';
import Link from 'next/link';
import InvoiceGenerator from "@/app/_components/InvoiceGenerator/InvoiceGenerator";
import {createTransformFunction} from "@/app/_components/General/TransformHTML";
import ShareMenu from "@/app/_components/General/ShareMenu";
import ReceiptGenerator from "@/app/_components/ReceiptGenerator/ReceiptGenerator";
import HouseCleaningCalculator from "@/app/_components/FreeTools/houseCleaningCalculator";
import LawnCareCalculator from "@/app/_components/FreeTools/lawnCareCalculator";
import RoofingCalculator from "@/app/_components/FreeTools/roofingCalculator";
import ProfitForecastCalculator from "@/app/_components/FreeTools/profitForecaseCalculator";
import HvacCFMCalculator from "@/app/_components/FreeTools/hvacCFMCalculator";
import PipeVolumeCalculator from "@/app/_components/FreeTools/pipeVolumeCalculator";
import HvacLoadCalculator from "@/app/_components/FreeTools/hvacLoadCalculator";
import AchCalculator from "@/app/_components/FreeTools/achCalculator";
import EstimateGenerator from "@/app/_components/EstimateGenerator/EstimateGenerator";
import HvacDuctCalculator from "@/app/_components/FreeTools/hvacDuctCalculator";


export default async function page({ params, searchParams }: { params: { puid: string, uid: string }, searchParams: { preview?: boolean, p?: string, view?: string } }) {
  const data = await getPageBySlug(`/free-tools/${params.puid}/${params.uid}`, searchParams.preview || false, searchParams.p || '', 'template').catch(() => notFound());

  if (!data || (data.status !== 'publish' && !searchParams.preview)) {
    notFound();
  }

  const structuredData = {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "WebPage",
        "@id": `${data?.seo?.canonicalUrl || "https://fieldcamp.ai/"}#website`,
        "url": data?.seo?.canonicalUrl || "https://fieldcamp.ai/",
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
            "name": data?.seo?.title,
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
               <nav className="flex mb-6 text-sm" aria-label="Breadcrumb">
                  <ol className="inline-flex items-center space-x-1 md:space-x-2 list-style-none">
                    <li className="inline-flex items-center">
                      <Link href="/" className="text-gray-600 hover:text-black">
                        Home
                      </Link>
                    </li>
                    <li>
                      <div className="flex items-center">
                        <svg className="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                          <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <Link href="/free-tools" className="ml-1 text-gray-600 hover:text-black md:ml-2">
                          Free Tools
                        </Link>
                      </div>
                    </li>
                    <li aria-current="page">
                      <div className="flex items-center">
                        <svg className="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                          <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <Link href={`/free-tools/${params.puid}`} className="ml-1 text-gray-600 hover:text-black md:ml-2">
                          {params.puid.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')}
                        </Link>
                      </div>
                    </li>
                  </ol>
                </nav>

              <div className="md:flex md:flex-row">
                <div className="md:w-3/5 mb-8 md:mb-0 md:pr-10">
                  <h1 className="text-3xl md:text-4xl font-medium mb-2">{data.title}</h1>
                  
                  {/* <div className="flex items-center mt-1 mb-6">
                    <span className="mr-2 text-sm">Invoices</span>
                    <span className="mx-2 text-sm text-gray-400">•</span>
                    <span className="text-sm">HVAC</span>
                    <div className="flex items-center ml-4">
                      <div className="flex">
                        {[1, 2, 3, 4].map((star) => (
                          <svg key={star} className="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                          </svg>
                        ))}
                        <svg className="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                      </div>
                      <span className="ml-1 text-sm text-gray-500">(156)</span>
                    </div>
                  </div> */}
                  
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
                  {/* <div className="border-b mt-10">
                    <div className="flex">
                      <button className="py-2 px-4 font-medium border-b-2 border-black">Overview</button>
                      <button className="py-2 px-4 text-gray-500">Reviews (156)</button>
                    </div>
                  </div> */}
                </div>
                
                <div className="md:w-2/5">
                  {rightSideContent && (
                    <div className="border rounded-lg p-6 mb-6">
                      <div className="flex justify-between mb-6">
                        {/* <a 
                          href={data?.templateFile?.templateFile?.node?.mediaItemUrl || '#'}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center text-gray-700 hover:text-black"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                          </svg>
                          <span className="ml-2">View template</span>
                        </a> */}
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
                      <ShareMenu />
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
              {data.content && data.content.includes('[achCalculator]') && (
                <AchCalculator/>
              )}
              {data.content && data.content.includes('[estimateGenerator]') && (
                <EstimateGenerator preFilledData={data.templateFile.preFilledData}/>
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

export async function generateMetadata({ params}:{params:{ puid: string, uid: string }}) {
  const data = await getPAGESEO(`/free-tools/${params.puid}/${params.uid}`, 'template') 
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
