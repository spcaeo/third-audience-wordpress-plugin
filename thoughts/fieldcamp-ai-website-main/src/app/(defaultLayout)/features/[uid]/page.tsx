import {getPOSTSEO, getSinglePost, extractImageUrls, getMediaAltByUrls } from "@/lib/api";
import SliderCode from "@/app/_components/Myslider"; // Import Myslider component
import BoxSliderCode from "@/app/_components/Boxslider";
import Singleslider from "@/app/_components/Singleslider";
import { notFound } from "next/navigation";
import parse from 'html-react-parser';
import {createTransformFunction} from "@/app/_components/General/TransformHTML";
import Fourcolumnreviewslider from "@/app/_components/Fourcolumnreviewslider";
import "@/app/(defaultLayout)/features/fearures.scss";

export default async function page({ params, searchParams }: { params: { uid: string }, searchParams: { preview?: string, p?: string } }) {
  const isPreview = searchParams.preview === 'true';
  const data = await getSinglePost('feature', params.uid, isPreview, searchParams.p || '').catch(() => notFound());
  if (!data || (data.status !== 'publish' && !isPreview)) {
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
        "@type": "Product",
        "name": data?.seo?.title,
        "url": data?.canonicalUrl,
        "description": data?.seo?.description,
        "image": data?.seo?.openGraph?.image?.url
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
          "name": "Features",
          "item": "https://fieldcamp.ai/features/"
        }
      ]
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
      <div className="feature-template">
      {replacedContent}
        <SliderCode />
        <BoxSliderCode/>
        <Singleslider/>
        <Fourcolumnreviewslider/>
      </div>
    </>
  );
}

export async function generateMetadata({ params}:{params:any}) {
  const data = await getPOSTSEO('feature', params.uid)
  const ogImage = data?.seo?.openGraph?.image;
  const ogImageUrl = ogImage?.url || 'https://cms.fieldcamp.ai/wp-content/uploads/2025/11/fieldcamp-logo.png';

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
          url: ogImageUrl,
          width: ogImage?.width || 1200,
          height: ogImage?.height || 630,
          alt: data?.seo?.openGraph?.title || data?.seo?.title,
        },
      ],
    },
    twitter: {
      card: 'summary_large_image',
      title: data?.seo?.openGraph?.title || data?.seo?.title,
      description: data?.seo?.openGraph?.description || data?.seo?.description,
      images: [ogImageUrl],
    },
  }
}


