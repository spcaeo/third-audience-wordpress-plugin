import {getPOSTSEO, getSinglePost, extractImageUrls, getMediaAltByUrls } from "@/lib/api";
import SliderCode from "@/app/_components/Myslider"; // Import Myslider component
import BoxSliderCode from "@/app/_components/Boxslider";
import Singleslider from "@/app/_components/Singleslider";
import { notFound } from "next/navigation";
import parse from 'html-react-parser';
import {createTransformFunction} from "@/app/_components/General/TransformHTML";
import DefaultPPCBlogLayout from "@/app/_components/Layouts/Blog/DefaultPPCBlogLayout";
import FullWidthLayout from "@/app/_components/Layouts/Blog/FullWidthLayout";

export default async function page({ params, searchParams }: { params: { uid: string }, searchParams: { preview?: boolean, p?: string } }) {
  
  const data = await getSinglePost('ppc', params.uid, searchParams.preview || false, searchParams.p || '').catch(() => notFound());
  if (!data) {
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
          "name": "Compare",
          "item": "https://fieldcamp.ai/compare/"
        }
      ]
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
          <DefaultPPCBlogLayout data={data} altTextMap={altTextMap} />
          <script
            key={`jobJSON`}
            type="application/ld+json"
            dangerouslySetInnerHTML={{ __html: JSON.stringify(structuredData) }}
          />
        </>
      );
  }
}

export async function generateMetadata({ params}:{params:any}) {
  const data = await getPOSTSEO('ppc', params.uid) 
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


