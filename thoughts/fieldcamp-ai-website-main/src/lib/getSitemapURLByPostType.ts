import { gql } from "@apollo/client";

export const getSitemapURLByPostType = (page_type: string) => gql`
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
`;
