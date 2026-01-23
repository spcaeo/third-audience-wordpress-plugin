import { gql } from "@apollo/client";

// Query to get all pages
export const GET_ALL_PAGES = gql`
  query GetAllPages {
    pages {
      nodes {
        slug
        date
        modified
      }
    }
  }
`;

// Query to get all posts
export const GET_ALL_POSTS = gql`
  query GetAllPosts {
    posts {
      nodes {
        slug
        date
        modified
      }
    }
  }
`;

// CRM page
// export const GET_CRM_PAGE = gql`
//   query GetFeaturePage {
//     page(id: "90", idType: DATABASE_ID) {
//       title
//       content
//     }
//   }
// `;

export const GET_CRM_PAGE = gql`
  query GetFeaturePage {
    page(id: "90", idType: DATABASE_ID) {
      title
      content
      seo {
        title  
        description 
      }
    }
  }
`;

export const GET_PAGE_BY_URI = gql`
  query GetPageByUri($slug: ID!) {
    page(id: $slug, idType: URI) {
      id
      title
      content
      slug
    }
  }
`;

