import { gql } from "@apollo/client";

export const getPageBySlug = (slug: string) => gql`
  query GetPageBySlug {
    page(id: "${slug}", idType: URI) {
      title
      content
      slug
      status
      seo {
        jsonLd {
          raw
        }
      }
      
    }
  }
`;
