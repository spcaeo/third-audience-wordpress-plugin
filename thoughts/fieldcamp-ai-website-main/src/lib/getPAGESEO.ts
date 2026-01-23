import { gql } from "@apollo/client";

export const getPAGESEO = (slug: string) => gql`
  query getPAGESEO {
    page(id: "${slug}", idType: URI) {
      seo {
        canonicalUrl
        description
        focusKeywords
        openGraph {
          type
          image {
            url
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
`;
