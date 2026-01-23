import SliderCode from "@/app/_components/Myslider";
import "@/app/globals.scss";
import { getPlaybookBySlug, getPlaybookSEO, getPageBySlug, getAllLabelCategories, getPlaybooks, getPAGESEO, extractImageUrls, getMediaAltByUrls } from "@/lib/api";
import parse from 'html-react-parser';
import { createTransformFunction } from "@/app/_components/General/TransformHTML";
import { notFound } from "next/navigation";
import BoxSliderCode from "@/app/_components/Boxslider";
import Singleslider from "@/app/_components/Singleslider";  
import Link from "next/link";
import PlaybookSidebarScript from "@/app/_components/PlaybookSidebarScript";
import SidebarTOC from "./SidebarTOC";
import "@/app/(defaultLayout)/playbook/[slug]/[uid]/styles.css";
import Image from "next/image";

const BASE_PATH = process.env.BASE_PATH || '';

interface PlaybookCategory {
  name: string;
  slug: string;
  databaseId: number;
  featuredImage?: {
    node: {
      altText?: string;
      sourceUrl: string;
    };
  };
}

interface Author {
  node: {
    name: string;
    slug?: string;
    uri?: string;
    firstName?: string;
    lastName?: string;
    description?: string;
    userDetails?: {
      authorPic?: {
        node: {
          sourceUrl: string;
        };
      };
      designation?: string;
    };
  };
}

interface Playbook {
  id: string;
  title: string;
  slug: string;
  uri: string;
  content?: string;
  featuredImage?: {
    node: {
      altText?: string;
      sourceUrl: string;
    };
  };
  playbookCategories?: {
    nodes: PlaybookCategory[];
  };
  author?: Author;
  seo?: {
    canonicalUrl?: string;
    title?: string;
    robots?: string[];
    description?: string;
    jsonLd?: {
      raw: string;
    };
  };
}

export default async function DynamicPlaybookPage({
  params,
  searchParams
}: {
  params: { slug: string, uid: string },
  searchParams: { preview?: string, p?: string }

}) {
  const isPreview = searchParams.preview === 'true';
  const playbooks = await getPlaybooks() as Playbook[];
  // For published pages, pass full URI path; for preview, pass just the uid (slug)
  const slugOrUri = isPreview ? params.uid : `/playbook/${params.slug}/${params.uid}/`;
  const data = await getPlaybookBySlug(slugOrUri, isPreview, searchParams.p || '').catch(() => notFound());
  console.log(params);
  if (!data || (data.status !== 'publish' && !isPreview)) {
    notFound();
  }

  // Fetch all label categories for the sidebar
  const allLabelCategories = await getAllLabelCategories();


  const structuredData = {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "WebPage",
        "@id": `${data?.seo?.canonicalUrl || "https://fieldcamp.ai/"}#website`,
        "url": data?.seo?.canonicalUrl || "https://fieldcamp.ai/",
        "name": data?.seo?.title || "FieldCamp Playbook",
        "description": data?.seo?.description,
        "about": {
          "@type": "Guide",
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
            "name": "Playbooks",
            "item": `https://fieldcamp.ai/playbooks/`
          },
          {
            "@type": "ListItem",
            "position": 4,
            "name": data?.title,
            "item": data?.seo?.canonicalUrl
          }
        ]
      },
      {
        "@type": "HowTo",
        "name": data?.seo?.title || "FieldCamp Playbook",
        "description": data?.seo?.description || "Expert playbook for field service management",
        "url": data?.seo?.canonicalUrl || "https://fieldcamp.ai/",
        "image": data?.featuredImage?.node?.sourceUrl || "https://fieldcamp.ai/_next/static/media/logo.6811b83e.svg",
        "publisher": {
          "@type": "Organization",
          "name": "FieldCamp",
          "logo": {
            "@type": "ImageObject",
            "url": "https://fieldcamp.ai/_next/static/media/logo.6811b83e.svg"
          }
        }
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

  const replacedContent = parse(data?.content || '', { transform: createTransformFunction(altTextMap) });
  const playbookbannerlable = extractSideColumn(data.content, 'playbookbannerlable');
  const playbookbannersubhead = extractSideColumn(data.content, 'playbookbannersubhead');
  const playbookbody = extractSideColumn(data.content, 'playbookbody');
  const tocplaybook = extractSideColumn(data.content, 'tocplaybook');


  return (
    <>
      <script
        key={`playbookJSON`}
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(structuredData) }}
      />

      <PlaybookSidebarScript />

      <div className="min-h-screen bg-white lg:mt-[90px] mt-[51px]">

        <div className="mx-auto">
          <article className="prose prose-lg max-w-none">

            {/* <section className="hero-banner">
              <div className="hero-background">
                <div className="geometric-pattern"></div>
                <div className="geometric-shape shape-1"></div>
                <div className="geometric-shape shape-2"></div>
                <div className="geometric-shape shape-3"></div>
              </div>
              <div className="container">
                <div className="hero-content">
                  {playbookbannerlable && (
                    <div className="tutorial-label">
                      {playbookbannerlable}
                    </div>
                  )}
                  <h1>{data?.title}</h1>
                  {playbookbannersubhead && (
                    <div className="hero-subtitle">
                      {playbookbannersubhead}
                    </div>
                  )}
                </div>
              </div>
            </section> */}

            <section className="hero-banner">
              <div className="container">
                <div className="hero-playbook">
                    {data.featuredImage?.node?.sourceUrl && (
                      <div className="fetureimg-hero flex-50">
                        <div className="bg-gradient-to-br from-orange-50 to-orange-100 flex items-center justify-center">
                          <Image
                            src={data.featuredImage.node.sourceUrl}
                            alt={data.featuredImage.node.altText || data.title}
                            width={800}
                            height={800}
                            className="w-full h-full object-cover"
                          />
                        </div>
                      </div>
                    )}
                    <div className="hero-contents flex-50">
                      {playbookbannerlable && (
                        <div className="tutorial-label">
                          {playbookbannerlable}
                        </div>
                      )}
                      <h1>{data?.title}</h1>
                      {playbookbannersubhead && (
                        <div className="hero-subtitle">
                          {playbookbannersubhead}
                        </div>
                      )}
                  </div>
                </div>
                
              </div>
            </section>

            <div className="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
              <div className="content">
                {/* {replacedContent} */}
                <div className="wp-block-group container is-layout-flow wp-block-group-is-layout-flow">
                  <div className="wp-block-group main-wrapper is-layout-flow wp-block-group-is-layout-flow">
                      {playbookbody}
                    <div className="wp-block-group sidebar is-layout-flow wp-block-group-is-layout-flow">
                      <div className="wp-block-group author-section is-layout-flow wp-block-group-is-layout-flow">
                        {data.author?.node && (
                          <div className="wp-block-group author-info is-layout-flow wp-block-group-is-layout-flow">
                            {data.author.node.userDetails?.authorPic?.node?.sourceUrl && (
                              <figure className="wp-block-image author-image">
                                <Image
                                  src={data.author.node.userDetails.authorPic.node.sourceUrl}
                                  alt={data.author.node.name || 'Author'}
                                  width={60}
                                  height={60}
                                  loading="lazy"
                                  className=""
                                  style={{ width: '100%', height: 'auto' }}
                                />
                              </figure>
                            )}
                            
                            <div className="wp-block-group author-text is-layout-flow wp-block-group-is-layout-flow">
                              <div className="wp-block-group author-byline has-global-padding is-layout-constrained wp-block-group-is-layout-constrained">
                                <p className="author-byline">BY {data.author.node.name?.toUpperCase()}</p>
                              </div>
                              
                              {data.author.node.userDetails?.designation && (
                                <div className="wp-block-group author-title has-global-padding is-layout-constrained wp-block-group-is-layout-constrained">
                                  <p className="author-title">{data.author.node.userDetails.designation}</p>
                                </div>
                              )}
                            </div>
                          </div>
                        )}
                      </div>
                      <SidebarTOC
                        labelCategories={allLabelCategories}
                        currentUri={`/playbook/${params.slug}/${params.uid}/`}
                        parentSlug={params.slug}
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
          </article>
        </div>

        {/* Additional Components */}
        <div className="mt-16">
          <SliderCode />
          <BoxSliderCode />
          <Singleslider />
        </div>
      </div>
    </>
  );
}

// Function to generate dynamic metadata
export async function generateMetadata({ params }: { params: { slug: string, uid: string } }) {
  // Try different URI formats for SEO data
  let data = await getPAGESEO(`playbook/${params.slug}/${params.uid}`, 'playbook').catch(() => null);

  return {
    title: data?.seo?.title || 'Playbook - FieldCamp',
    description: data?.seo?.description || 'Expert playbook for field service management',
    robots: data?.seo?.robots?.join(',') || 'index,follow',
    alternates: { canonical: data?.seo?.canonicalUrl },
    openGraph: {
      type: data?.seo?.openGraph?.type || 'article',
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