import SliderCode from "@/app/_components/Myslider"; // Import Myslider component
import "@/app/globals.scss";

import { getPageBySlug, getPAGESEO, getPlaybookBySlug, getPlaybookSEO, extractImageUrls, getMediaAltByUrls } from "@/lib/api";
import parse from 'html-react-parser';
import {createTransformFunction} from "@/app/_components/General/TransformHTML";
import { notFound } from "next/navigation";
import BoxSliderCode from "@/app/_components/Boxslider";
import Singleslider from "@/app/_components/Singleslider";
import "@/app/(defaultLayout)/playbook/styles.css";




export default async function DynamicPlaybookPage({ params, searchParams }: { params: { slug: string }, searchParams: { preview?: string, p?: string } }) {
  const isPreview = searchParams.preview === 'true';
  let data = null;
  
  // First try to fetch as a WordPress Page
  try {
    console.log('Trying to fetch as WordPress Page:', '/playbook/' + params.slug);
    data = await getPageBySlug('/playbook/' + params.slug, isPreview, searchParams.p || '');
    if (data) {
      console.log('Found as WordPress Page');
    }
  } catch (error) {
    console.log('Not found as WordPress Page:', error);
  }
  
  // If not found as Page, try as Playbook post type
  if (!data) {
    try {
      console.log('Trying to fetch as Playbook post type:', '/playbook/' + params.slug);
      data = await getPlaybookBySlug('/playbook/' + params.slug, isPreview, searchParams.p || '');
      if (data) {
        console.log('Found as Playbook post type');
      }
    } catch (error) {
      console.log('Not found as Playbook post type:', error);
    }
  }
  
  // If still not found, try just the slug without /playbook/ prefix
  if (!data) {
    try {
      console.log('Trying to fetch as Playbook with just slug:', params.slug);
      data = await getPlaybookBySlug(params.slug, isPreview, searchParams.p || '');
      if (data) {
        console.log('Found as Playbook with just slug');
      }
    } catch (error) {
      console.log('Not found with just slug:', error);
    }
  }
  
  if (!data || (data.status !== 'publish' && !isPreview)) {
    console.log('No data found for:', params.slug);
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
        "@graph": [
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
                "name": "Playbook",
                "item": "https://fieldcamp.ai/playbook/"
              }
            ]
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
  
  const imageUrls = extractImageUrls(data?.content || '');
  const altTextMap = await getMediaAltByUrls(imageUrls);
  const replacedContent = parse(data?.content, { transform: createTransformFunction(altTextMap) });

  return (
    <>
      <script
        key={`jobJSON`}
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(structuredData) }}
      />
      <div>
        <div className="min-h-screen bg-white mt-[90px]">
        {replacedContent}
        <SliderCode />
        <BoxSliderCode/>
        <Singleslider/>
        </div>
      </div>
    </>
  );
}

// Function to generate dynamic metadata
export async function generateMetadata({ params }:{ params: any }) {
  let data = null;
  
  // Try fetching SEO as Page first
  try {
    data = await getPAGESEO('/playbook/' + params.slug);
  } catch (error) {
    console.log('SEO not found as Page');
  }
  
  // If not found, try as Playbook
  if (!data) {
    try {
      data = await getPlaybookSEO('/playbook/' + params.slug);
    } catch (error) {
      console.log('SEO not found as Playbook with full path');
    }
  }
  
  // Try with just slug
  if (!data) {
    try {
      data = await getPlaybookSEO(params.slug);
    } catch (error) {
      console.log('SEO not found as Playbook with just slug');
    }
  }
  
  return {
    title: data?.seo?.title,
    description: data?.seo?.description,
    robots: data?.seo?.robots?.join(','),
    alternates: { canonical: data?.seo?.canonicalUrl },
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