import { getSitemapPostType } from "@/lib/api";

export async function GET() {
    
    const origin = process.env.NEXT_PUBLIC_FRONTEND_URL || '';
    
    
    const data = await getSitemapPostType();
    // Process content types
    const filteredPages = data?.rankMathSettings?.sitemap?.contentTypes?.flatMap((contentType : any) => {
        if (contentType.connectedContentNodes && contentType.connectedContentNodes.nodes) {
            return contentType.connectedContentNodes.nodes
                .filter((node : any) => contentType.isInSitemap)
                .map((node : any) => {
                    const lastModified = `${node.modified}+00:00`;
                    const url = `${origin}/sitemap-${getFriendlyTypeName(contentType.type)}.xml`;
                    return {
                        url: url,
                        lastModified: lastModified,
                    };
                });
        }
        return []; 
    }).filter(Boolean); 

    const filteredTaxonomies = data?.rankMathSettings?.sitemap?.taxonomies?.flatMap((taxonomy: any) => {
        if (!taxonomy.connectedTerms?.nodes || !taxonomy.isInSitemap) {
            return [];
        }
        // Get the first node to check if it's a WorkflowCategory
        const firstNode = taxonomy.connectedTerms.nodes[0];
        // Special handling for WorkflowCategory
        if (taxonomy.type === 'WORKFLOWCATEGORY' && firstNode?.workflowTemplates?.nodes?.length > 0) {
            
            const lastModified = `${firstNode.workflowTemplates.nodes[0]?.modified || new Date().toISOString()}+00:00`;
            const url = `${origin}/sitemap-${getFriendlyTypeName(taxonomy.type)}.xml`;
            return [{
                url,
                lastModified,
            }];
        }
        
        // Default handling for other taxonomies
        return taxonomy.connectedTerms.nodes
            .filter((node: any) => node.modified)
            .map((node: any) => ({
                url: `${origin}/sitemap-${getFriendlyTypeName(taxonomy.type)}.xml`,
                lastModified: `${node.modified}+00:00`,
            }));
    }).filter(Boolean) || [];

    // Add static sitemap entries
    const staticSitemaps = [{
        url: `${origin}/sitemap-compare.xml`,
        lastModified: new Date().toISOString()
    }];

    const allSitemaps = [...filteredPages, ...filteredTaxonomies, ...staticSitemaps];
    
    const pagesSitemapXML = await buildPagesSitemap(allSitemaps);

    return new Response(pagesSitemapXML, {
        headers: {
            "Content-Type": "application/xml",
        },
    });
}

async function buildPagesSitemap(pages : any) {
    let xml = '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="sitemap.xsl"?>';
    xml += '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    for (const pageURL of pages) {
        xml += "<sitemap>";
        xml += `<loc>${pageURL.url}</loc>`;
        xml += `<lastmod>${pageURL.lastModified}</lastmod>`;
        xml += "</sitemap>";
    }

    xml += "</sitemapindex>";
    return xml;
}

function getFriendlyTypeName(type: string): string {
    switch (type) {
        default:
            return type.toLowerCase(); // Default to lowercase if no specific transformation
    }
} 