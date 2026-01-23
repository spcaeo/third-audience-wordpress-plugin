import Image from "next/image";
import Link from "next/link";
import { getPOSTSEO, getSinglePost, extractImageUrls, getMediaAltByUrls } from "@/lib/api";
import "@/app/blog.scss";

import parse from 'html-react-parser';
import { createTransformFunction , tocItems} from "@/app/_components/General/TransformHTML";
import BlogTOC from "@/app/_components/General/BlogTOC";
import FieldServiceCTA from "@/app/_components/General/FieldServiceCTA";
import { notFound } from "next/navigation";
import FullWidthLayout from "@/app/_components/Layouts/Blog/FullWidthLayout";
import DefaultBlogLayout from "@/app/_components/Layouts/Blog/DefaultBlogLayout";
 
function getReadingTime(content: string): number {
  const wordsPerMinute = 200;
  const wordCount = content.trim().split(/\s+/).length;
  return Math.ceil(wordCount / wordsPerMinute);
}

const BlogPost = async ({ params, searchParams }: { params: { uid: string }, searchParams: { preview?: boolean, p?: string } }) => {
  
    const data = await getSinglePost('review', params.uid, searchParams.preview || false, searchParams.p || '').catch(() => notFound());;

    const structuredData = {
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
              "name": "Reviews",
              "item": "https://fieldcamp.ai/reviews/"
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

  const layout = data.layout?.layout[0] || 'default'; // Assuming `layout` comes from CMS
  switch (layout) {
    case 'full-width':
      return (
        <>
          <FullWidthLayout data={data} altTextMap={altTextMap} />
          <script
            key={`jobJSON`}
            type="application/ld+json"
            dangerouslySetInnerHTML={{ __html: JSON.stringify(structuredData) }}
          />
        </>
      );
    case 'default':
    default:
      return (
        <>
          <DefaultBlogLayout data={data} pageType="review" altTextMap={altTextMap}/>
          <script
            key={`jobJSON`}
            type="application/ld+json"
            dangerouslySetInnerHTML={{ __html: JSON.stringify(structuredData) }}
          />
        </>
      );
  }
};

export default BlogPost;

// Function to generate dynamic metadata
export async function generateMetadata({ params }: { params: any }) {
  const data = await getPOSTSEO("review", params.uid);

  return {
    title: data?.seo?.title,
    description: data?.seo?.description,
    robots: data?.seo?.robots.join(","),
    alternates: { canonical: data?.seo.canonicalUrl },
    openGraph: {
      type: data?.seo?.openGraph?.type || "website",
      description: data?.seo?.openGraph?.description || data?.seo?.description,
      title: data?.seo?.openGraph?.title || data?.seo?.title,
      url: data?.seo?.canonicalUrl,
      images: [
        {
          url: data?.seo?.openGraph?.image?.url || "",
        },
      ],
    },
  };
}