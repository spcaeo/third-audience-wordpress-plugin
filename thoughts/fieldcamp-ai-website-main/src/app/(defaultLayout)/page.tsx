import { getPageBySlug, getPAGESEO } from "@/lib/api";
import { DEFAULT_OG_IMAGE } from "@/lib/ogImageDefaults";
import React from 'react';
import parse from 'html-react-parser';
import {createTransformFunction} from "@/app/_components/General/TransformHTML";
import SliderCode from "@/app/_components/Myslider";
import BoxSliderCode from "@/app/_components/Boxslider";
import Singleslider from "@/app/_components/Singleslider";
import { notFound } from "next/navigation";


export default async function Home({ searchParams }: { searchParams: { preview?: boolean; p?: string } }) {
  const data = await getPageBySlug('/', searchParams.preview || false, searchParams.p || '').catch(() => notFound());
  if (!data || (data.status !== 'publish' && !searchParams.preview)) {
    notFound();
  }
  const structuredData = {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Organization",
        "@id": "https://fieldcamp.ai/#organization",
        "name": "FieldCamp",
        "url": "https://fieldcamp.ai/",
        "logo": {
          "@type": "ImageObject",
          "url": data?.seo?.logo?.url || "https://fieldcamp.ai/_next/static/media/logo.6811b83e.svg"
        },
        "sameAs": data?.seo?.socialLinks || [
          "https://www.linkedin.com/company/fieldcamp/",
          "https://www.instagram.com/fieldcamp.ai/",
          "https://x.com/FieldCamp_ai",
          "https://www.facebook.com/getfieldcamp",
          "https://www.youtube.com/@fieldcamp_ai"
        ],
        "description": data?.seo?.description || "Streamline your business with FieldCamp's AI-driven field management software. Intuitive, multilingual, and built for field service professionals to enhance efficiency and reduce complexity."
      },
      {
        "@type": "WebSite",
        "@id": `${data?.seo?.canonicalUrl || "https://fieldcamp.ai/"}#website`,
        "url": data?.seo?.canonicalUrl || "https://fieldcamp.ai/",
        "name": data?.seo?.title || "FieldCamp",
        "publisher": {
          "@id": `${data?.seo?.canonicalUrl || "https://fieldcamp.ai/"}#organization`
        }
      },
      {
        "@type": "SoftwareApplication",
        "name": data?.seo?.title || "Fieldcamp: AI Field Service Management Software",
        "operatingSystem": "Web",
        "applicationCategory": "BusinessApplication",
        "offers": {
          "@type": "Offer",
          "price": data?.seo?.price || "25.00",
          "priceCurrency": "USD"
        },
        "description": data?.seo?.description || "Streamline your business with FieldCamp's AI-driven field management software. Intuitive, multilingual, and built for field service professionals to enhance efficiency and reduce complexity.",
        "url": data?.seo?.canonicalUrl || "https://fieldcamp.ai/",
        "screenshot": data?.seo?.screenshot?.url || "https://fieldcamp.ai/_next/image/?url=https%3A%2F%2Fcms.fieldcamp.ai%2Fwp-content%2Fuploads%202025%2F03%2Fbanner-img.png&w=1920&q=75",
        "publisher": {
          "@type": "Organization",
          "name": data?.seo?.title || "FieldCamp"
        }
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
  const replacedContent = parse(data?.content, { transform: createTransformFunction()});
  return (
    <>
    <script
        key={`jobJSON`}
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(structuredData) }}
      />
      {replacedContent}
      <SliderCode />
      <BoxSliderCode/>
      <Singleslider/>
    </>
  );
}

export async function generateMetadata() {
  const data = await getPAGESEO('/')
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
          url: data?.seo?.openGraph?.image?.url || DEFAULT_OG_IMAGE.url,
          width: data?.seo?.openGraph?.image?.width || DEFAULT_OG_IMAGE.width,
          height: data?.seo?.openGraph?.image?.height || DEFAULT_OG_IMAGE.height,
          alt: data?.seo?.title || DEFAULT_OG_IMAGE.alt,
        },
      ],
    },
    twitter: {
      card: 'summary_large_image',
      title: data?.seo?.openGraph?.title || data?.seo?.title,
      description: data?.seo?.openGraph?.description || data?.seo?.description,
      images: [data?.seo?.openGraph?.image?.url || DEFAULT_OG_IMAGE.url],
    },
  };
}





