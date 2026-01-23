import React from "react";
import Link from "next/link";
import Image from "next/image";

import "@/app/blog.scss";
import { getAllPosts, getPAGESEO } from "@/lib/api";


interface Post {
  id: string;
  slug: string;
  title: string;
  date: string;
  excerpt: string;
  uri : string;
  featuredImage: {
    node: {
      sourceUrl: string;
      altText?: string;
    };
  };
  seo?: {
      robots?: string[]  
    };
}

// async function getPosts() {
//   const { data } = await getClient().query({ query: GET_POSTS });
//   console.log(data.posts.nodes);
//   return data.posts.nodes;
// }

export default async function Page() {
  const allPosts = await getAllPosts();
  const filteredPosts = allPosts.nodes.filter((post: Post) => post.seo?.robots?.includes("index"));

  return (
    <div className="max-w-1245 mx-auto px-4 py-8 blog-section ">
      <h1 className="text-6xl  mb-12 mt-[95px] text-center">Blog</h1>
      <div className="xl:min-h-[466px] 2xl:min-h-[681px]">
      <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3 ">
        {filteredPosts.map((post: Post, index: number) => (
          <Link
            href={post.uri}
            key={post.id || index}
            className="border rounded-lg overflow-hidden shadow-md px-[11px] pt-[11px]"
          >
            {post.featuredImage && post.featuredImage.node && (
              <div className="featured-image">
                <Image
                  src={post.featuredImage.node.sourceUrl}
                  alt={post.featuredImage.node.altText || post.title}
                  width={400}
                  height={200}
                  layout="responsive"
                  objectFit="cover"
                />
              </div>
            )}
            <div className="p-6">
              <h2 className="text-xl font-semibold mb-2">{post.title}</h2>
              <p className="text-sm text-gray-500 mb-4">
                {new Date(post.date).toLocaleDateString()}
              </p>
              <div
                dangerouslySetInnerHTML={{ __html: post.excerpt }}
                className="text-gray-700"
              />
            </div>
          </Link>
        ))}
      </div>
      </div>
    </div>
  );
}

// Function to generate dynamic metadata
export async function generateMetadata({ params}:{params:any}) {
  const data = await getPAGESEO('blog') 
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


