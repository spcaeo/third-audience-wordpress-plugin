import { getFieldStoryBySlug, getFieldStorySEO, extractImageUrls, getMediaAltByUrls } from '@/lib/api';
import { notFound } from 'next/navigation';
import React from 'react';
import parse from 'html-react-parser';
import {createTransformFunction} from "@/app/_components/General/TransformHTML";
import '../field-stories.scss';

const FieldStoryDetailPage = async ({ params, searchParams }: { params: { uid: string }, searchParams: { preview?: boolean, p?: string, view?: string } }) => {

  const data = await getFieldStoryBySlug(`/field-stories/${params.uid}`, searchParams.preview || false, searchParams.p || '').catch(() => notFound());
    if (!data?.content) {
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
              "name": "Field Stories",
              "item": "https://fieldcamp.ai/field-stories/"
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
      <div className="content">
              {/* {replacedContent} */}
              {replacedContent}
            </div>
    </>
  );
}

export default FieldStoryDetailPage;

export async function generateMetadata({ params}:{params:{ uid: string }}) {
  const data = await getFieldStorySEO(`/field-stories/${params.uid}`)
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
  const extractSideColumn = (content: string, sideClass: string) => {
    try {
      const contentDOM = parse(content, { transform: createTransformFunction() });

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
