import { gql } from "@apollo/client";

export const getSitemapPostType =  gql`
 query getSitemapPostType {
        rankMathSettings {
          sitemap {
            contentTypes {
              type
              sitemapName: type
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
              type
              isInSitemap
            }
          }
        }
      }
`;
