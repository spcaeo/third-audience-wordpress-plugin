"use client";
import { useState } from "react";

import Link from "next/link";
import Image from "next/image";
import IcrightArrow from "../../../../public/ic_right_arrow-1.svg";

export default function AuthorLoadMore({ initialData }: { initialData: any }) {
  const [reachedLastPage, setReachedLastPage] = useState(
    !initialData?.posts?.pageInfo?.hasNextPage
  );
  const [noMorePost, setnoMorePost] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [endCursor, setendCursor] = useState(
    initialData?.posts?.pageInfo?.endCursor || null
  );
  const [searchQuery, setSearchQuery] = useState("");
  const [items, setItems] = useState(initialData?.posts?.nodes || []);
  const [totalItems, setTotalItems] = useState(0);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const origin = process.env.NEXT_PUBLIC_FRONTEND_URL || "";

  const fetchMoreItems = async () => {
    if (isLoading || reachedLastPage) return;

    setIsLoading(true);
    setError(null);

    try {
      const response = await fetch(
        origin +
          `/api/fetchuserblogs?endCursor=${encodeURIComponent(
            endCursor || '0'
          )}&user=${initialData?.slug}&items=9`,
        {
          method: "GET",
        }
      );

      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }

      const data = await response.json();

      if (data?.data?.user?.posts?.nodes) {
        setendCursor(data.data.user.posts.pageInfo.endCursor);
        setItems([...items, ...data.data.user.posts.nodes]);

        if (!data.data.user.posts.pageInfo.hasNextPage) {
          setReachedLastPage(true);
        }
      } else {
        setReachedLastPage(true);
      }
    } catch (error) {
      console.error("Error fetching more items:", error);
      setError("Failed to load more content. Please try again.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      <section className=" bg-white">
        <div className="w-full max-w-[80rem] mx-auto">
          <div className="blogListWrapper">
            <div className="mb-5 xl:mb-40 md:px-5">
              <p className="text-2xl text-[#333333] pb-10">
                Latest content by {initialData?.name || "Author"}
              </p>
              {items && items.length > 0 ? (
                <>
                  <div className="grid xl:grid-cols-3 gap-x-10">
                    <div className="col-span-2">
                      {items[0]?.featuredImage?.node?.sourceUrl ? (
                        <Image
                          src={items[0].featuredImage.node.sourceUrl}
                          alt={items[0]?.title || "Blog post"}
                          width={800}
                          height={400}
                        />
                      ) : (
                        <div className="bg-gray-200 flex items-center justify-center" style={{ height: "400px" }}>
                          <span className="text-gray-500">No featured image</span>
                        </div>
                      )}
                    </div>
                    <div className="...">
                      <p className="text-sm text-black pt-5 xl:pt-0">
                        {formatPostDate(items[0]?.modified || items[0]?.date || new Date().toISOString())}
                      </p>
                      <p className="text-2xl text-black font-semibold">
                        <Link
                          href={items[0]?.contentType === 'playbook' ? (items[0]?.uri || `/playbook/${items[0]?.slug || '#'}`) : `/blog/${items[0]?.slug || '#'}`}
                          className="hover:text-primary transition-colors"
                        >
                          {items[0]?.title || "Untitled"}
                        </Link>
                      </p>
                      <div className="flex items-center gap-x-3 mt-2 md:mt-5">
                        <div>
                          <Image
                            src={initialData?.userDetails?.authorPic?.node?.sourceUrl || initialData?.avatar?.url || "https://cms.fieldcamp.ai/wp-content/uploads/2025/01/jeel-patel.png"}
                            alt={initialData?.userDetails?.authorPic?.node?.altText || initialData?.name || "Author"}
                            width={60}
                            height={60}
                            className="rounded-full object-cover"
                          />
                        </div>
                        <div>
                          <p className="text-black text-[18px] pb-0.5 font-medium">
                            {initialData?.name || "Author"}
                          </p>
                          <p className="text-black text-sm pb-1">{initialData?.userDetails?.designation || initialData?.description || "Team Member"}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </>
              ) : (
                <div className="text-center py-16">
                  <p className="text-gray-600 text-lg">No content found for this author.</p>
                </div>
              )}
            </div>
            {items && items.length > 1 && (
              <div className="all-blogs_list grid gap-y-8 gap-x-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 auto-cols-[1fr] ">
                <ListItem items={items.slice(1)} />
                {noMorePost && (
                  <div className="w-pagination-wrapper all-blogs_pagination">
                    <p>No More Items!</p>
                  </div>
                )}
              </div>
            )}
            {isLoading && (
              <div className="all-blogs_list grid gap-y-8 gap-x-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 auto-cols-[1fr] ">
                {[...Array(3)].map((_, index) => (
                  <div key={index} className="all-blogs_item border border-[#E5E7EB] p-3 rounded-[10px] animate-pulse">
                    <div className="bg-gray-200 rounded h-48 mb-4"></div>
                    <div className="bg-gray-200 rounded h-4 mb-2"></div>
                    <div className="bg-gray-200 rounded h-6 mb-2"></div>
                    <div className="bg-gray-200 rounded h-4 w-20"></div>
                  </div>
                ))}
              </div>
            )}
            {error && (
              <div className="mt-8 mb-4 flex items-center justify-center">
                <div className="text-red-600 text-center">
                  <p>{error}</p>
                  <button
                    onClick={() => {setError(null); fetchMoreItems();}}
                    className="mt-2 text-blue-600 underline hover:text-blue-800"
                  >
                    Try again
                  </button>
                </div>
              </div>
            )}
            {!reachedLastPage && items && items.length > 0 && (
              <div className="mt-16 mb-28 flex items-center justify-center">
                <button
                  type="button"
                  className="flex button is-text bg-[var(--primary)] text-black py-3 px-6 gap-4 items-center justify-center rounded-full border border-solid border-black border-1 disabled:opacity-50 disabled:cursor-not-allowed"
                  onClick={fetchMoreItems}
                  disabled={isLoading}
                >
                  <span className="text-base leading-[1.45]">
                    {isLoading ? "Loading..." : "Explore more"}
                  </span>
                  {/* <span className="button-icon w-embed">
                    <Image
                      alt="Arrow"
                      className="invert"
                      width={16}
                      height={12}
                      src={IcrightArrow}
                    />
                  </span> */}
                </button>
              </div>
            )}
            <div className="mb-16 md:mb-24 lg:mb-32"></div>
          </div>
        </div>
      </section>
    </>
  );
}

// Helper function for consistent date formatting
const formatPostDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', { 
    month: 'long', 
    day: 'numeric', 
    year: 'numeric' 
  });
};

export function ListItem({ items }: { items: any }) {
  return (
    <>
      {items && items.length > 0 && items.map((post: any, index: number) => {
        const formattedDate = formatPostDate(post?.modified || post?.date || new Date().toISOString());
        const alternateText = post?.featuredImage?.node?.altText || post?.title || "Blog post";
        const imageUrl = post?.featuredImage?.node?.sourceUrl;

        return (
          <div className="all-blogs_item border border-[#E5E7EB] p-3 rounded-[10px]" key={index}>
            <div className="all-blogs-card_link">
              <Link
                href={post?.contentType === 'playbook' ? (post?.uri || `/playbook/${post?.slug || '#'}`) : `/blog/${post?.slug || '#'}`}
                className="relative" title={alternateText}
              >
                {imageUrl ? (
                  <Image
                    alt={alternateText}
                    src={imageUrl}
                    className="all-blogs-card_image"
                    style={{ objectFit: "contain" }}
                    width={2000}
                    height={2000}
                  />
                ) : (
                  <div className="all-blogs-card_image bg-gray-200 flex items-center justify-center" style={{ height: "200px" }}>
                    <span className="text-gray-500">No image available</span>
                  </div>
                )}

              </Link>
              <div className="all-blogs-card_content bg-white flex gap-y-2 flex-col text-[#222] flex-1 pt-6 px-0">
                <div className="published-date">{formattedDate}</div>
                <Link
                  href={post?.contentType === 'playbook' ? (post?.uri || `/playbook/${post?.slug || '#'}`) : `/blog/${post?.slug || '#'}`}
                  className="border-b-0 mb-2"
                  title={post?.title || "Content"}
                >
                  <h2 className="text-[1.5rem]">
                    {post?.contentType === 'playbook' && <span className="text-sm font-normal text-gray-600">[Playbook] </span>}
                    {post?.title || "Untitled"}
                  </h2>
                </Link>
                <div className="margin-top margin-xsmall">
                  <div className="button-group">
                  <Link
                  href={post?.contentType === 'playbook' ? (post?.uri || `/playbook/${post?.slug || '#'}`) : `/blog/${post?.slug || '#'}`}
                  className="read__more-text !mt-4 border-b-0"
                  title="Read More"
                >
                  Read More
                </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        );
      })}
      {!items && (
        <div className="mt-16 mb-28 flex items-center justify-center">
          <button className="flex button is-text bg-[var(--primary)] text-white py-3 px-6 gap-4 items-center justify-center rounded-full border border-solid border-black border-1">
            <span className="text-base leading-[1.45]">Explore more</span>
            {/* <span className="button-icon w-embed">
              <Image
                alt="Arrow"
                className="invert"
                width={16}
                height={12}
                src={IcrightArrow}
              />
            </span> */}
          </button>
        </div>
      )}
    </>
  );
}