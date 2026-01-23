import { getCustomerBySlug, getCustomerSEO, extractImageUrls, getMediaAltByUrls } from '@/lib/api';
import { notFound } from 'next/navigation';
import React from 'react';
import parse from 'html-react-parser';
import {createTransformFunction} from "@/app/_components/General/TransformHTML";
import '../customers.scss';

const CustomerDetailPage = async ({ params, searchParams }: { params: { puid: string }, searchParams: { preview?: boolean, p?: string, view?: string } }) => {

  const data = await getCustomerBySlug(`/customers/${params.puid}`, searchParams.preview || false, searchParams.p || '').catch(() => notFound());
    if (!data?.content) {
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
            "@type": "Organization",
            "name": data?.seo?.title,
            "url": data?.seo?.canonicalUrl,
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
              "name": "Customers",
              "item": "https://fieldcamp.ai/customers/"
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
      <div className="min-h-screen bg-white pt-20">
        <div className="content">
          {replacedContent}
        </div>
      </div>
    </>
  );
}

export default CustomerDetailPage;

export async function generateMetadata({ params}:{params:{ puid: string, uid: string }}) {
  const data = await getCustomerSEO(`/customers/${params.puid}`)
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
