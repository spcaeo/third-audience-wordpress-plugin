import { getSitemapURLByPostType, getSitemapPostType, getSitemapURLByTaxonomyType } from "@/lib/api";

// Define types for the API response
interface SitemapNode {
    uri: string;
    modified: string;
    seo?: {
        robots?: string[];
    };
    workflowTemplates?: {
        nodes: Array<{
            modified: string;
        }>;
    };
}

interface SitemapContentType {
    type: string;
    isInSitemap: boolean;
    connectedContentNodes?: {
        nodes: SitemapNode[];
    };
}

interface SitemapTaxonomy {
    type: string;
    isInSitemap: boolean;
    connectedTerms?: {
        nodes: SitemapNode[];
    };
}

interface SitemapData {
    rankMathSettings: {
        sitemap: {
            contentTypes: SitemapContentType[];
            taxonomies: SitemapTaxonomy[];
        };
    };
}

export const revalidate = 3600; // Revalidate every 60 seconds

// Define types for our content types
interface ContentType {
    type: string;
    isTaxonomy: boolean;
}

// Cache for available content types
let availablePostTypes: ContentType[] = [];
let availableTaxonomies: ContentType[] = [];

// Function to get available content types from WordPress
async function getAvailablePostTypes(): Promise<ContentType[]> {
    // If we already have both post types and taxonomies, return the combined array
    if (availablePostTypes.length > 0 && availableTaxonomies.length > 0) {
        return [...availablePostTypes, ...availableTaxonomies];
    }
    
    try {
        const data = await getSitemapPostType();
        
        // Update post types
        availablePostTypes = data?.rankMathSettings?.sitemap?.contentTypes?.map((contentType: any) => ({
            type: contentType.type,
            isTaxonomy: false
        })) || [];
        
        // Update taxonomies
        availableTaxonomies = data?.rankMathSettings?.sitemap?.taxonomies?.map((taxonomy: any) => ({
            type: taxonomy.type,
            isTaxonomy: true
        })) || [];
        
        // Combine and return both arrays
        return [...availablePostTypes, ...availableTaxonomies];
    } catch (error) {
        // Error fetching post types
        return [];
    }
}

// Function to validate and normalize post type
async function validatePostType(postType: string): Promise<{type: string, isTaxonomy: boolean} | null> {
    const availableTypes = await getAvailablePostTypes();
    
    const normalizedType = postType.toUpperCase();
    
    // Special cases for static sitemaps
    const lowerPostType = postType.toLowerCase();
    if (lowerPostType === 'page') {
        return { type: 'PAGE', isTaxonomy: false };
    }
    if (lowerPostType === 'compare') {
        return { type: 'COMPARE', isTaxonomy: false };
    }
    
    // Find the type in available types
    const foundType = availableTypes.find(t => t.type === normalizedType);
    
    if (foundType) {
        return foundType;
    }
    
    // Try case-insensitive match if exact match fails
    const caseInsensitiveMatch = availableTypes.find(t => t.type.toUpperCase() === normalizedType);
    if (caseInsensitiveMatch) {
        return caseInsensitiveMatch;
    }
    return null;
}

export async function GET(request: any, { params }: { params: { post_type: string } }) {
    const origin = process.env.NEXT_PUBLIC_FRONTEND_URL || '';
    
    try {
        // Validate the content type
        const contentType = await validatePostType(params.post_type);
        
        if (!contentType) {
            return new Response('Invalid content type', { status: 400 });
        }
        
        let data: SitemapData;
        if (contentType.isTaxonomy) {
            try {
                data = await getSitemapURLByTaxonomyType(contentType.type) as SitemapData;
                
                // Process taxonomy data
                const taxonomy = data?.rankMathSettings?.sitemap?.taxonomies?.[0];
                
                const sitemapItems = taxonomy?.connectedTerms?.nodes?.map(node => {
                    const modifiedDate = node.workflowTemplates?.nodes?.[0]?.modified 
                        ? `${node.workflowTemplates.nodes[0].modified}+00:00`
                        : new Date().toISOString();
                    
                    return {
                        url: `${origin}${node.uri}`,
                        lastModified: modifiedDate
                    };
                }) || [];
                
                const sitemapXml = await buildPagesSitemap(sitemapItems);
                
                return new Response(sitemapXml, {
                    headers: { 'Content-Type': 'application/xml' },
                });
            } catch (error) {
                // Error in taxonomy processing
                throw error; // Re-throw to be caught by the outer try-catch
            }
        } else {
            // Handle post types
            let sitemapItems = [];
            
            // Check if this is the compare sitemap
            if (params.post_type.toLowerCase() === 'compare') {
                // Add static comparison links
                const comparisonLinks = [
                    '/compare/fieldcamp-vs-housecall-pro/',
                    '/compare/fieldcamp-vs-jobber/',
                    '/compare/fieldcamp-vs-gorilla-desk/',
                    '/compare/jobber-vs-housecall-pro/',
                    '/compare/jobber-vs-workiz/',
                    '/compare/jobber-vs-servicetitan/',
                    '/compare/jobber-vs-yardbook/',
                    '/compare/housecall-pro-vs-servicetitan/',
                    '/compare/housecall-pro-vs-service-fusion/',
                    '/compare/workiz-vs-housecall-pro/',
                    '/compare/gorilladesk-vs-jobber/',
                    // Add more comparison links here as needed
                ];
                
                sitemapItems = comparisonLinks.map(link => ({
                    url: `${origin}${link}`,
                    lastModified: new Date().toISOString(),
                }));
            } else {
                // Handle regular post types
                data = await getSitemapURLByPostType(contentType.type) as SitemapData;
                const contentTypeData = data?.rankMathSettings?.sitemap?.contentTypes?.[0];
                
                sitemapItems = contentTypeData?.connectedContentNodes?.nodes
                    ?.filter((node) => {
                        const isInSitemap = contentTypeData.isInSitemap;
                        const isNoIndex = node.seo?.robots?.includes('noindex');
                        return isInSitemap && !isNoIndex;
                    })
                    .map((node) => ({
                        url: `${origin}${node.uri}`,
                        lastModified: `${node.modified}+00:00`,
                    })) || [];
                    
                // Add static pages that should be in the sitemap
                if (contentType.type === 'PAGE') {
                    const staticPages = [
                        '/online-booking/',
                        '/ai-receptionist/',
                        '/ai-receptionist/workflows/'
                    ];

                    staticPages.forEach(page => {
                        sitemapItems.push({
                            url: `${origin}${page}`,
                            lastModified: new Date().toISOString(),
                        });
                    });
                }
            }
            
            const sitemapXml = await buildPagesSitemap(sitemapItems);
            return new Response(sitemapXml, {
                headers: { 'Content-Type': 'application/xml' },
            });
        }
        
    } catch (error) {
        console.error(`Error generating sitemap for ${params.post_type}:`, error);
        return new Response('Error generating sitemap', { status: 500 });
    }
}

async function buildPagesSitemap(pages: any) {
    let xml = '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="sitemap.xsl"?>';
    xml += '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    for (const pageURL of pages) {
        xml += "<url>";
        xml += `<loc>${pageURL.url}</loc>`;
        xml += `<lastmod>${pageURL.lastModified}</lastmod>`; // Set the <lastmod> to the current date in IST
        xml += "</url>";
    }

    xml += "</urlset>";
    return xml;
}
