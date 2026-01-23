const API_URL: string = process.env.WORDPRESS_API_URL || '';
const BASE_URL: string = process.env.WORDPRESS_BASE_URL || '';
const FRONTEND_URL: string = process.env.NEXT_PUBLIC_FRONTEND_URL || '';

async function fetchAPI(query: string = '', variables: Record<string, any> = {}): Promise<any> {
  const headers: Record<string, string> = { 'Content-Type': 'application/json' };

  if (process.env.WORDPRESS_AUTH_REFRESH_TOKEN) {
    headers['Authorization'] = `Bearer ${process.env.WORDPRESS_AUTH_REFRESH_TOKEN}`;
  }

  const response = await fetch(API_URL, {
    next: { tags: ['wpchange'], revalidate: 3600 }, // Cache for 1 hour, revalidate on demand with tags
    headers,
    method: 'POST',
    body: JSON.stringify({
      query,
      variables,
    }),

  });

  const json = await response.json();
  if (json.errors) {
    console.error('GraphQL Errors:', json.errors);
    const errorMessages = json.errors.map((error: any) => error.message).join(', ');
    throw new Error(`GraphQL API Error: ${errorMessages}`);
  }
  return json.data;
}

// Extract image URLs from HTML content
export function extractImageUrls(content: string): string[] {
  const imgRegex = /<img[^>]+src=["']([^"']+)["']/gi;
  const urls: string[] = [];
  let match;
  while ((match = imgRegex.exec(content)) !== null) {
    urls.push(match[1]);
  }
  return [...new Set(urls)];
}

// Extract filename from URL for matching
function getFilenameFromUrl(url: string): string {
  try {
    const pathname = new URL(url).pathname;
    return pathname.split('/').pop() || '';
  } catch {
    return url.split('/').pop() || '';
  }
}

// Fetch alt text from WordPress media library by URLs (optimized with parallel queries)
export async function getMediaAltByUrls(urls: string[]): Promise<Record<string, string>> {
  if (!urls.length) return {};

  const altMap: Record<string, string> = {};

  // Helper function to fetch media by filename search
  const fetchMediaByFilename = async (url: string, filename: string): Promise<void> => {
    try {
      // Extract search term from filename (remove extension and size suffixes like -300x200)
      const searchTerm = filename
        .replace(/\.[^.]+$/, '') // Remove extension
        .replace(/-\d+x\d+$/, '') // Remove size suffix like -300x200
        .replace(/[-_]/g, ' '); // Replace dashes/underscores with spaces for better search

      const data = await fetchAPI(`{
        mediaItems(first: 10, where: {search: "${searchTerm}"}) {
          nodes {
            sourceUrl
            altText
          }
        }
      }`);

      const nodes = data?.mediaItems?.nodes || [];

      // Find matching media using the same logic as before
      const media = nodes.find((m: any) => {
        if (!m.sourceUrl) return false;
        const mediaFilename = getFilenameFromUrl(m.sourceUrl);
        return filename === mediaFilename || url.includes(m.sourceUrl) || m.sourceUrl.includes(url);
      });

      if (media?.altText) {
        altMap[url] = media.altText;
      }
    } catch (error) {
      // Silently fail for individual media queries - alt text is not critical
      console.warn(`Failed to fetch alt text for: ${url}`);
    }
  };

  // Process all URLs in parallel for maximum speed
  const fetchPromises = urls.map((url) => {
    const filename = getFilenameFromUrl(url);
    if (!filename) return Promise.resolve();
    return fetchMediaByFilename(url, filename);
  });

  await Promise.all(fetchPromises);
  return altMap;
}

export async function getMenu(menuName: string) {
  const data = await fetchAPI(`{menus(where: {location: ${menuName}}) {
    nodes {
      menuItems(first: 100, where: { parentId: 0 }) {
        nodes {
          id
          label
          order
          url
          cssClasses
          menuACF {
            menuType
            isButton
            subTitle
            menuDesc
            icon {
              node {
                sourceUrl
                altText
              }
            }
          }
          childItems(first: 100) {
            nodes {
              id
              menuItemId
              url
              label
              title
              cssClasses
              menuACF {
                menuType
                isButton
                subTitle
                menuDesc
                icon {
                  node {
                    sourceUrl
                    altText
                  }
                }
              }
              childItems(first: 100) {
                nodes {
                  id
                  menuItemId
                  url
                  label
                  title
                  cssClasses
                  menuACF {
                    menuType
                    isButton
                    subTitle
                    menuDesc
                    icon {
                      node {
                        sourceUrl
                        altText
                      }
                    }
                  }
                  childItems(first: 100) {
                    nodes {
                      id
                      menuItemId
                      url
                      label
                      title
                      cssClasses
                      menuACF {
                        menuType
                        isButton
                        subTitle
                        menuDesc
                        icon {
                          node {
                            sourceUrl
                            altText
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
          parentId
        }
      }
    }
  }
}`);

if (data.menus?.nodes?.[0]?.menuItems?.nodes) {
  for (var i in data.menus.nodes[0].menuItems.nodes) {
    if (data.menus.nodes[0].menuItems.nodes[i]?.url) {
    data.menus.nodes[0].menuItems.nodes[i].url = String(data.menus.nodes[0].menuItems.nodes[i].url).replaceAll(BASE_URL, FRONTEND_URL);
    }
    if (data.menus.nodes[0].menuItems.nodes[i]?.childItems?.nodes) {
    for (var j in data.menus.nodes[0].menuItems.nodes[i].childItems.nodes) {
        if (data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j]?.url) {
      data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].url = String(data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].url).replaceAll(BASE_URL, FRONTEND_URL);
        }
        if (data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j]?.childItems?.nodes) {
      for (var k in data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes) {
            if (data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k]?.url) {
        data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k].url = String(data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k].url).replaceAll(BASE_URL, FRONTEND_URL);
            }
            if (data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k]?.childItems?.nodes) {
        for (var l in data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k].childItems.nodes) {
                if (data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k].childItems.nodes[l]?.url) {
          data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k].childItems.nodes[l].url = String(data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k].childItems.nodes[l].url).replaceAll(BASE_URL, FRONTEND_URL);
                }
                if (data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k].childItems.nodes[l]?.childItems?.nodes) {
          for (var m in data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k].childItems.nodes[l].childItems.nodes) {
                    if (data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k].childItems.nodes[l].childItems.nodes[m]?.url) {
            data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k].childItems.nodes[l].childItems.nodes[m].url = String(data.menus.nodes[0].menuItems.nodes[i].childItems.nodes[j].childItems.nodes[k].childItems.nodes[l].childItems.nodes[m].url).replaceAll(BASE_URL, FRONTEND_URL);
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
  return data?.menus?.nodes[0];
}


export async function getPageBySlug(slug: string, preview?: boolean, p?: string, page_type: string = 'page') {

    const isPreview = Boolean(preview) === true;
    const query = isPreview
    ? `id: "${p}", idType: DATABASE_ID, asPreview: true`
    : `id: "${slug}", idType: URI, asPreview: false`;

    // SEO fields cause "Internal server error" for draft posts, so exclude them in preview mode
    const seoFields = isPreview ? '' : `
          seo {
            canonicalUrl
            title
            description
            jsonLd {
              raw
            }
          }`;

    const data = await fetchAPI(`query GetPageBySlug {
    ${page_type}(${query}) {
          title
          content
          slug
          status
          featuredImage {
            node {
              altText
              sourceUrl
            }
          }
          ${seoFields}
          ${page_type === 'template' ? `templateFile {
            templateFile {
              node {
                mediaItemUrl
              }
            }
            preFilledData {
              serviceName1
              serviceDescription1
              serviceCost1
              serviceName2
              serviceDescription2
              serviceCost2
              serviceName3
              serviceDescription3
              serviceCost3
            }
          }` : ''}
          ${page_type === 'workflowTemplate' ? `
            workflowCategories {
              nodes {
                name
              }
            }` : ''}
          ${page_type === 'playbook' ? `
            labelCategories {
              nodes {
                id
                name
                playbooks {
                  nodes {
                    id
                    title
                    uri
                  }
                }
              }
            }
            author {
              node {
                name
                slug
                uri
                firstName
                lastName
                description
                userDetails {
                  authorPic {
                    node {
                      sourceUrl
                    }
                  }
                  designation
                }
              }
            }` : ''}
        }
      }
    `);
    


    const pageData = data?.[page_type];

    
    if (pageData?.slug == 'sitemap') {
      pageData.content = String(pageData.content).replaceAll(BASE_URL, FRONTEND_URL);
    } else if (pageData?.content) {
      const regex = /<a\s+(?:[^>]*?\s+)?href=(["'])(.*?)\1/gi;
      pageData.content = pageData.content.replace(regex, (match: any, p1: any, url: any) => {
        if (url.startsWith(BASE_URL)) {
          return match.replace(url, url.replace(BASE_URL, FRONTEND_URL));
        }
        // Check if the URL is a relative path
        else if (url.startsWith('/')) {
          return match;
        }
        return match;
      });
    }

    if (pageData?.seo) {
      // Update jsonLd.raw URLs
      if (pageData.seo.jsonLd?.raw) {
        const regex = /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.\n])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[A-Z0-9+&@#\/%=~_|$])/igm;
        pageData.seo.jsonLd.raw = pageData.seo.jsonLd.raw.replace(regex, (match: any) => {
          if (!match.includes("uploads")) {
            return match.replace(BASE_URL, FRONTEND_URL);
          }
          return match;
        });
      }
  
      // Update canonicalUrl
      if (pageData.seo.canonicalUrl) {
        pageData.seo.canonicalUrl = pageData.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
      }
      console.log(pageData.seo.canonicalUrl);
    }

  return pageData;
}

export async function getPAGESEO(slug: string, page_type: string = 'page') {
  const data = await fetchAPI(` {
    ${page_type}(id: "${slug}", idType: URI) {
      seo {
        canonicalUrl
        description
        focusKeywords
        openGraph {
          type
          title
          description
          image {
            url
            width
            height
          }
        }
        title
        robots
        jsonLd {
          raw
        }
      }
    }
  }
  `);

  if (data?.[page_type]?.seo?.canonicalUrl) {
    data[page_type].seo.canonicalUrl = data[page_type].seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
  }

  return data?.[page_type];
}

export async function getAllPosts(searchQuery: string = '') {
  const data = await fetchAPI(`
   query getAllPost {
      posts(first: 1000, where: {status: PUBLISH, search: "${searchQuery}"}) {
      nodes {
            title
            date
            modified
            excerpt
            uri
            categories {
              edges {
                node {
                  name
                  slug
                }
              }
            }
            slug
            featuredImage {
              node {
                altText
                sourceUrl
              }
            }
            seo {
              robots
            }
          }
        
        pageInfo {
          hasNextPage
          hasPreviousPage
          startCursor
          endCursor
        }
      }
      
    }
  `)

  return data?.posts
}
 
export async function getSinglePost(page_type: string, slug: string, preview?: boolean, p?: string) {

  const isPreview = Boolean(preview) === true;
  const query = isPreview
  ? `id: "${p}", idType: DATABASE_ID, asPreview: true`
  : `id: "${slug}", idType: SLUG, asPreview: false`;

  // SEO fields cause "Internal server error" for draft posts, so exclude them in preview mode
  const seoFields = isPreview ? '' : `
      seo {
        jsonLd {
          raw
        }
        title
        focusKeywords
        canonicalUrl
        description
      }`;

  const data = await fetchAPI(` {
    ${page_type}(${query}) {
      title
      ${page_type}Id
      excerpt
      content
      status
      slug
      id
      author {
        node {
          name
          slug
          uri
          firstName
          lastName
          description
          userDetails {
            authorPic {
              node {
                sourceUrl
              }
            }
            designation
          }
        }
      }

      date
      modified
      featuredImage {
        node {
          altText
          sourceUrl
        }
      }
      ${seoFields}
      layout {
        layout
      }
    }
  }
  `);

  const pageData = data?.[page_type];

  if (!pageData) {
    return null;
  }

  if (pageData.content) {
    const regex = /<a\s+(?:[^>]*?\s+)?href=(["'])(.*?)\1/gi;
    pageData.content = pageData.content.replace(regex, (match: any, p1: any, url: any) => {
      if (/\.(pdf|docx?)$/i.test(url)) {
        return match;
      }
      if (url.startsWith(BASE_URL)) {
        return match.replace(url, url.replace(BASE_URL, FRONTEND_URL));
      }
      // Check if the URL is a relative path
      else if (url.startsWith('/')) {
        return match.replace(url, FRONTEND_URL + url);
      }
      return match;
    });
  }

  if (pageData?.seo) {
    // Update jsonLd.raw URLs
    if (pageData.seo.jsonLd?.raw) {
      const regex = /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.\n])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[A-Z0-9+&@#\/%=~_|$])/igm;
      pageData.seo.jsonLd.raw = pageData.seo.jsonLd.raw.replace(regex, (match: any) => {
        if (!match.includes("uploads")) {
          return match.replace(BASE_URL, FRONTEND_URL);
        }
        return match;
      });
    }

    // Update canonicalUrl
    if (pageData.seo.canonicalUrl) {
      pageData.seo.canonicalUrl = pageData.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
    }
  }

  return pageData;
}

export async function getPOSTSEO(post_type: string, slug: string) {
  const data = await fetchAPI(` {
    ${post_type}(id: "${slug}", idType: SLUG) {
      seo {
        canonicalUrl
        description
        focusKeywords
        openGraph {
          type
          title
          description
          image {
            url
            width
            height
          }
        }
        title
        robots
      }
    }
  }
  `);
  const postData = data?.[post_type];
  if (postData?.seo?.canonicalUrl) {
    postData.seo.canonicalUrl = postData.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
  }
  return postData;
}

export async function getWorkflowTemplates() {
  const data = await fetchAPI(`
  {
    workflowTemplates(first: 100) {
      nodes {
        id
        title
        slug
        featuredImage {
          node {
            altText
            sourceUrl
          }
        }
        workflowCategories {
          nodes {
            name
            slug
          }
        }
        seo {
          canonicalUrl
          title
          robots
          description
          jsonLd {
            raw
          }
          canonicalUrl       
        }
      }
    }
  }
  `);
  return data?.workflowTemplates?.nodes;
}

export async function getWorkflowCategories() {
  const data = await fetchAPI(`
  {
    workflowCategories(first: 100) {
      nodes {
        name
        slug
      }
    }
  }
  `);
  return data?.workflowCategories?.nodes;
}



export async function getProductWorkflows() {
  const data = await fetchAPI(`
  {
    productWorkflows(first: 100) {
      nodes {
        id
        title
        slug
        featuredImage {
          node {
            altText
            sourceUrl
          }
        }
        productWorkflowCategories {
          nodes {
            name
            slug
          }
        }
        seo {
          canonicalUrl
          title
          robots
          description
          jsonLd {
            raw
          }
          canonicalUrl       
        }
      }
    }
  }
  `);
  return data?.productWorkflows?.nodes;
}

export async function getProductWorkflowCategories() {
  const data = await fetchAPI(`
  {
    productWorkflowCategories(first: 100) {
      nodes {
        name
        slug
      }
    }
  }
  `);
  return data?.productWorkflowCategories?.nodes;
}

export async function getUsecases() {
  const data = await fetchAPI(`
  {
  usecases {
    nodes {
      id
      title
      uri
      slug
      featuredImage {
        node {
          sourceUrl
          altText
        }
      }
      seo {
        canonicalUrl
        robots
        description
        title
        jsonLd {
          raw
        }
      }
    }
  }
}
  `);
  return data?.usecases?.nodes;
}

export async function getFieldStories() {
  const data = await fetchAPI(`
  {
  fieldStories {
    nodes {
      id
      uri
      title
      slug
      seo {
        canonicalUrl
        robots
        description
        title
        jsonLd {
          raw
        }
      }
      fieldStorieslable {
        categoryLable
      }
    }
  }
}
  `);
  return data?.fieldStories?.nodes;
}

export async function getUsecaseBySlug(slug: string, preview?: boolean, p?: string) {
  const query = (Boolean(preview) === true)
    ? `id: "${p}", idType: DATABASE_ID, asPreview: true`
    : `id: "${slug}", idType: URI, asPreview: false`;

  const data = await fetchAPI(` {
    usecase(${query}) {
      title
      content
      slug
      featuredImage {
        node {
          altText
          sourceUrl
        }
      }
      seo {
        canonicalUrl
        title
        robots
        description
        jsonLd {
          raw
        }
        openGraph {
          type
          title
          description
          image {
            url
            width
            height
          }
        }
      }
    }
  }`);
  
  const pageData = data?.usecase;
  
  if (pageData?.content) {
    const regex = /<a\s+(?:[^>]*?\s+)?href=(["'])(.*?)\1/gi;
    pageData.content = pageData.content.replace(regex, (match: any, p1: any, url: any) => {
      if (url.startsWith(BASE_URL)) {
        return match.replace(url, url.replace(BASE_URL, FRONTEND_URL));
      }
      else if (url.startsWith('/')) {
        return match;
      }
      return match;
    });
  }

  if (pageData?.seo) {
    if (pageData.seo.jsonLd?.raw) {
      const regex = /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.\n])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[A-Z0-9+&@#\/%=~_|$])/igm;
      pageData.seo.jsonLd.raw = pageData.seo.jsonLd.raw.replace(regex, (match: any) => {
        if (!match.includes("uploads")) {
          return match.replace(BASE_URL, FRONTEND_URL);
        }
        return match;
      });
    }

    if (pageData.seo.canonicalUrl) {
      pageData.seo.canonicalUrl = pageData.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
    }
  }
  
  return pageData;
}

export async function getUsecaseSEO(slug: string) {
  const data = await fetchAPI(` {
    usecase(id: "${slug}", idType: URI) {
      seo {
        canonicalUrl
        description
        robots
        title
        openGraph {
          type
          title
          description
          image {
            url
            width
            height
          }
        }
      }
    }
  }`);

  const pageData = data?.usecase;
  if (pageData?.seo?.canonicalUrl) {
    pageData.seo.canonicalUrl = pageData.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
  }

  return pageData;
}

export async function getFieldStoryBySlug(slug: string, preview?: boolean, p?: string) {
  const query = (Boolean(preview) === true)
    ? `id: "${p}", idType: DATABASE_ID, asPreview: true`
    : `id: "${slug}", idType: URI, asPreview: false`;

  const data = await fetchAPI(` {
    fieldStory(${query}) {
      title
      content
      slug
      seo {
        canonicalUrl
        title
        robots
        description
        jsonLd {
          raw
        }
        openGraph {
          type
          title
          description
          image {
            url
            width
            height
          }
        }
      }
    }
  }`);

  const pageData = data?.fieldStory;

  if (pageData?.content) {
    const regex = /<a\s+(?:[^>]*?\s+)?href=(["'])(.*?)\1/gi;
    pageData.content = pageData.content.replace(regex, (match: any, p1: any, url: any) => {
      if (url.startsWith(BASE_URL)) {
        return match.replace(url, url.replace(BASE_URL, FRONTEND_URL));
      }
      else if (url.startsWith('/')) {
        return match;
      }
      return match;
    });
  }

  if (pageData?.seo) {
    if (pageData.seo.jsonLd?.raw) {
      const regex = /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.\n])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[A-Z0-9+&@#\/%=~_|$])/igm;
      pageData.seo.jsonLd.raw = pageData.seo.jsonLd.raw.replace(regex, (match: any) => {
        if (!match.includes("uploads")) {
          return match.replace(BASE_URL, FRONTEND_URL);
        }
        return match;
      });
    }

    if (pageData.seo.canonicalUrl) {
      pageData.seo.canonicalUrl = pageData.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
    }
  }

  return pageData;
}

export async function getFieldStorySEO(slug: string) {
  const data = await fetchAPI(` {
    fieldStory(id: "${slug}", idType: URI) {
      seo {
        canonicalUrl
        description
        robots
        title
        openGraph {
          type
          title
          description
          image {
            url
            width
            height
          }
        }
      }
    }
  }`);

  const pageData = data?.fieldStory;
  if (pageData?.seo?.canonicalUrl) {
    pageData.seo.canonicalUrl = pageData.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
  }

  return pageData;
}

export async function getTeams() {
  const data = await fetchAPI(`
  {
  teams {
    nodes {
      id
      title
      uri
      slug
      featuredImage {
        node {
          sourceUrl
          altText
        }
      }
      seo {
        canonicalUrl
        robots
        description
        title
        jsonLd {
          raw
        }
      }
    }
  }
}
  `);
  return data?.teams?.nodes;
}

export async function getCustomers() {
  const data = await fetchAPI(`
  {
  customers {
    nodes {
      id
      title
      uri
      slug
      featuredImage {
        node {
          sourceUrl
          altText
        }
      }
      seo {
        canonicalUrl
        robots
        description
        title
        jsonLd {
          raw
        }
      }
    }
  }
}
  `);
  return data?.customers?.nodes;
}

export async function getTeamBySlug(slug: string, preview?: boolean, p?: string) {
  const query = (Boolean(preview) === true)
    ? `id: "${p}", idType: DATABASE_ID, asPreview: true`
    : `id: "${slug}", idType: URI, asPreview: false`;

  const data = await fetchAPI(` {
    team(${query}) {
      title
      content
      slug
      featuredImage {
        node {
          altText
          sourceUrl
        }
      }
      seo {
        canonicalUrl
        title
        robots
        description
        jsonLd {
          raw
        }
        openGraph {
          type
          title
          description
          image {
            url
            width
            height
          }
        }
      }
    }
  }`);
  
  const pageData = data?.team;
  
  if (pageData?.content) {
    const regex = /<a\s+(?:[^>]*?\s+)?href=(["'])(.*?)\1/gi;
    pageData.content = pageData.content.replace(regex, (match: any, p1: any, url: any) => {
      if (url.startsWith(BASE_URL)) {
        return match.replace(url, url.replace(BASE_URL, FRONTEND_URL));
      }
      else if (url.startsWith('/')) {
        return match;
      }
      return match;
    });
  }

  if (pageData?.seo) {
    if (pageData.seo.jsonLd?.raw) {
      const regex = /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.\n])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[A-Z0-9+&@#\/%=~_|$])/igm;
      pageData.seo.jsonLd.raw = pageData.seo.jsonLd.raw.replace(regex, (match: any) => {
        if (!match.includes("uploads")) {
          return match.replace(BASE_URL, FRONTEND_URL);
        }
        return match;
      });
    }

    if (pageData.seo.canonicalUrl) {
      pageData.seo.canonicalUrl = pageData.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
    }
  }
  
  return pageData;
}

export async function getTeamSEO(slug: string) {
  const data = await fetchAPI(` {
    team(id: "${slug}", idType: URI) {
      seo {
        canonicalUrl
        description
        robots
        title
        openGraph {
          type
          title
          description
          image {
            url
            width
            height
          }
        }
      }
    }
  }`);

  const pageData = data?.team;
  if (pageData?.seo?.canonicalUrl) {
    pageData.seo.canonicalUrl = pageData.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
  }

  return pageData;
}

export async function getCustomerBySlug(slug: string, preview?: boolean, p?: string) {
  const query = (Boolean(preview) === true)
    ? `id: "${p}", idType: DATABASE_ID, asPreview: true`
    : `id: "${slug}", idType: URI, asPreview: false`;

  const data = await fetchAPI(` {
    customer(${query}) {
      title
      content
      slug
      featuredImage {
        node {
          altText
          sourceUrl
        }
      }
      seo {
        canonicalUrl
        title
        robots
        description
        jsonLd {
          raw
        }
        openGraph {
          type
          title
          description
          image {
            url
            width
            height
          }
        }
      }
    }
  }`);

  const pageData = data?.customer;

  if (pageData?.content) {
    const regex = /<a\s+(?:[^>]*?\s+)?href=(["'])(.*?)\1/gi;
    pageData.content = pageData.content.replace(regex, (match: any, p1: any, url: any) => {
      if (url.startsWith(BASE_URL)) {
        return match.replace(url, url.replace(BASE_URL, FRONTEND_URL));
      }
      else if (url.startsWith('/')) {
        return match;
      }
      return match;
    });
  }

  if (pageData?.seo) {
    if (pageData.seo.jsonLd?.raw) {
      const regex = /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.\n])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.\n]*\)|[A-Z0-9+&@#\/%=~_|$])/igm;
      pageData.seo.jsonLd.raw = pageData.seo.jsonLd.raw.replace(regex, (match: any) => {
        if (!match.includes("uploads")) {
          return match.replace(BASE_URL, FRONTEND_URL);
        }
        return match;
      });
    }

    if (pageData.seo.canonicalUrl) {
      pageData.seo.canonicalUrl = pageData.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
    }
  }

  return pageData;
}

export async function getCustomerSEO(slug: string) {
  const data = await fetchAPI(` {
    customer(id: "${slug}", idType: URI) {
      seo {
        canonicalUrl
        description
        robots
        title
        openGraph {
          type
          title
          description
          image {
            url
            width
            height
          }
        }
      }
    }
  }`);

  const pageData = data?.customer;
  if (pageData?.seo?.canonicalUrl) {
    pageData.seo.canonicalUrl = pageData.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
  }

  return pageData;
}

export async function getPlaybooks() {
  const data = await fetchAPI(`
  {
    playbooks(first: 100) {
      nodes {
        id
        title
        slug
        uri
        content
        featuredImage {
          node {
            altText
            sourceUrl
          }
        }
        seo {
          canonicalUrl
          title
          robots
          description
          jsonLd {
            raw
          }
        }
      }
    }
  }
  `);
  return data?.playbooks?.nodes;
}

export async function getAllLabelCategories() {
  try {
    const data = await fetchAPI(`
    {
      labelCategories(first: 100) {
        nodes {
          id
          name
          parent {
            node {
              id
              name
            }
          }
          playbooks(first: 100) {
            nodes {
              id
              title
              uri
              date
            }
          }
        }
      }
    }
    `);
    return data?.labelCategories || null;
  } catch (error) {
    console.error('Failed to fetch all label categories:', error);
    return null;
  }
}

// playbookCategories has been removed from the schema
// This function is no longer used
/*
export async function getPlaybookCategories() {
  return [];
}
*/

export async function getPlaybookBySlug(slug: string, preview?: boolean, p?: string) {
    const isPreview = Boolean(preview) === true;
    const query = isPreview
      ? `id: "${p}", idType: DATABASE_ID, asPreview: true`
      : `id: "${slug}", idType: URI, asPreview: false`;

    // SEO fields cause "Internal server error" for draft posts, so exclude them in preview mode
    const seoFields = isPreview ? '' : `
        seo {
          canonicalUrl
          title
          description
          jsonLd {
            raw
          }
        }`;

    const data = await fetchAPI(` {
      playbook(${query}) {
        title
        content
        slug
        status
        featuredImage {
          node {
            altText
            sourceUrl
          }
        }
        author {
          node {
            name
            slug
            uri
            firstName
            lastName
            description
            userDetails {
              authorPic {
                node {
                  sourceUrl
                }
              }
              designation
            }
          }
        }
        ${seoFields}
      }
    }`);
    return data?.playbook;
  }

export async function getPlaybookSEO(slug: string) {
  const data = await fetchAPI(` {
    playbook(id: "${slug}", idType: URI) {
      seo {
        canonicalUrl
        description
        robots
        title
        openGraph {
          type
          title
          description
          image {
            url
            width
            height
          }
        }
      }
    }
  }`);
  return data?.playbook;
}


export async function getUserBySlug(slug: any, endCursor: any, items : any = 9) {
  // Fetch user data with posts
  const userData = await fetchAPI(`
  {
    user(id: "${slug}", idType: SLUG) {
      name
      description
      slug
      avatar {
        url
      }
      userDetails {
        designation
        socialProfielLinktwitter
        socialProfielLinklinkedin
        authorPic {
          node {
            sourceUrl
            altText
          }
        }
      }
      seo {
        canonicalUrl
        title
        robots
        description
        jsonLd {
          raw
        }
        canonicalUrl
      }
      posts(first: 100, where: {status: PUBLISH}) {
        nodes {
          title
          date
          modified
          slug
          excerpt
          featuredImage {
            node {
              altText
              sourceUrl
            }
          }
          categories {
            edges {
              node {
                name
                slug
              }
            }
          }
        }
      }
    }
  }
  `);

  // Fetch all playbooks
  const playbooksData = await fetchAPI(`
  {
    playbooks(first: 100, where: {status: PUBLISH}) {
      nodes {
        title
        date
        modified
        slug
        uri
        excerpt
        featuredImage {
          node {
            altText
            sourceUrl
          }
        }
        author {
          node {
            slug
          }
        }
      }
    }
  }
  `);

  // Filter playbooks by author
  const userPlaybooks = playbooksData?.playbooks?.nodes?.filter(
    (playbook: any) => playbook?.author?.node?.slug === slug
  ) || [];

  // Combine posts and playbooks
  const allContent = [
    ...(userData?.user?.posts?.nodes || []).map((post: any) => ({...post, contentType: 'post'})),
    ...userPlaybooks.map((playbook: any) => ({...playbook, contentType: 'playbook'}))
  ];

  // Sort by date (newest first)
  allContent.sort((a, b) => {
    const dateA = new Date(a.modified || a.date).getTime();
    const dateB = new Date(b.modified || b.date).getTime();
    return dateB - dateA;
  });

  // Handle pagination
  const currentEndCursor = parseInt(endCursor || '0');
  const startIndex = currentEndCursor;
  const endIndex = startIndex + items;
  const paginatedContent = allContent.slice(startIndex, endIndex);

  // Update user data with combined content
  if (userData?.user) {
    userData.user.posts = {
      nodes: paginatedContent,
      pageInfo: {
        hasNextPage: allContent.length > endIndex,
        endCursor: endIndex.toString(),
        hasPreviousPage: startIndex > 0,
        startCursor: startIndex.toString()
      }
    };
  }

  const data = userData;

  if (data?.user?.seo?.canonicalUrl) {
    data.user.seo.canonicalUrl = data.user.seo.canonicalUrl.replace(BASE_URL, FRONTEND_URL);
  }

  if (data?.user?.seo?.jsonLd?.raw) {
    const regex = /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/igm
      ;
    data.user.seo.jsonLd.raw = data.user.seo.jsonLd.raw.replace(regex, (match: any) => {

      if (!match.includes("uploads")) {
        return match.replace(BASE_URL, FRONTEND_URL);
      }
      return match;
    });
  }

  return data;
}

export async function getSitemapPostType() {
  const data = await fetchAPI(`
      {
        rankMathSettings {
          sitemap {
            contentTypes {
              type
              isInSitemap
              connectedContentNodes(first: 1) {
                nodes {
                  modified
                }
              }
            }
            author {
              connectedAuthors {
                nodes {
                  posts(first: 1) {
                    nodes {
                      modified
                    }
                  }
                }
              }
            }
            taxonomies {
              isInSitemap
              type
              connectedTerms(first: 1) {
                nodes {
                  ... on Category {
                    posts(first: 1) {
                      nodes {
                        modified
                      }
                    }
                  }
                  ... on WorkflowCategory {
                    id
                    name
                    workflowTemplates(first: 1) {
                      nodes {
                        modified
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
  `);
  return data;
}

export async function getSitemapURLByPostType(page_type: string) {
  const data = await fetchAPI(`
      {
      rankMathSettings {
        sitemap {
          contentTypes(include: ${page_type}) {
            type
            isInSitemap
            connectedContentNodes(first: 1000) {
              nodes {
                uri
                modified
                seo {
                  robots
                }
              }
            }
          }
        }
      }
    }
  `);
  return data;
}


export async function getSitemapURLByTaxonomyType(taxo_type: string) {
  const data = await fetchAPI(`
      {
      rankMathSettings {
        sitemap {
          taxonomies(include: ${taxo_type}) {
            isInSitemap
            type
            connectedTerms(first: 1000) {
              nodes {
                ... on Category {
                  uri
                  posts(first: 1) {
                    nodes {
                      modified
                    }
                  }
                }
                ... on WorkflowCategory {
                  id
                  name
                  uri
                  workflowTemplates(first: 1) {
                    nodes {
                      modified
                    }
                  }
                }
              }
            }
            
          }
        }
      }
    }
  `);
  return data;
}

export async function fetchRedirections(): Promise<any[]> {
  console.log('Redirection function called');
  const data = await fetchAPI(`
    {
      redirections(first: 500) {
        edges {
          node {
            sources {
              comparison
              pattern
            }
            status
            type
            redirectToUrl
          }
        }
      }
    }
`);
  return data.redirections.edges.map((edge: any) => edge.node);
}