import { getUserBySlug, getPAGESEO } from "@/lib/api";
import React from "react";
import AuthorLoadMore from "./authorloadmore";
import { notFound } from "next/navigation";
import Image from "next/image";
import Link from "next/link";

export default async function author({ params }: { params: any }) {
  const data = await getUserBySlug(params.uid, null, 10).catch(() => notFound());

  if (data?.user === null) {
    return notFound();
  }

  // Get separate Twitter and LinkedIn URLs from backend fields
  const twitterLink = data?.user?.userDetails?.socialProfielLinktwitter || "";
  const linkedinLink = data?.user?.userDetails?.socialProfielLinklinkedin || "";

  return (
    <>
      <div className="author-details pt-[70px] md:pt-[100px] max-w-1150">
        <div className=" md:flex xl:flex items-center gap-x-5 pb-3 md:pb-8 mb-8 border-b md:px-5 xl:px-0 2xl:px-0 3xl:px-0">
          <div>
            <div className="hidden md:block w-[150px] h-[150px] rounded-full overflow-hidden">
              <Image
                src={data?.user?.userDetails?.authorPic?.node?.sourceUrl || data?.user?.avatar?.url || "https://cms.fieldcamp.ai/wp-content/uploads/2025/01/jeel-patel.png"}
                alt={data?.user?.userDetails?.authorPic?.node?.altText || `${data?.user?.name || "Author"} Image`}
                width={150}
                height={150}
                className="w-full h-full object-cover"
              />
            </div>
            <div className="block md:hidden mb-2.5 w-[150px] h-[150px] rounded-full overflow-hidden">
              <Image
                src={data?.user?.userDetails?.authorPic?.node?.sourceUrl || data?.user?.avatar?.url || "https://cms.fieldcamp.ai/wp-content/uploads/2025/01/jeel-patel.png"}
                alt={data?.user?.userDetails?.authorPic?.node?.altText || `${data?.user?.name || "Author"} Image`}
                width={150}
                height={150}
                className="w-full h-full object-cover"
              />
            </div>
          </div>
          <div>
            <h1 className="author-title text-[26px] md:text-[38px] xl:text-[40px]  2xl:md:text-[40px] 3xl:md:text-[40px] text-[#333333] pb-1.5">
              {data?.user?.name || "Author"}
            </h1>
            <div className="flex items-start gap-x-1 pb-3">
              <span className="tracking-normal">{data?.user?.userDetails?.designation || data?.user?.description || "Team Member"}</span>
              {/* Twitter icon - shows if first link exists */}
              {twitterLink && (
                <Link href={twitterLink} target="_blank" rel="noopener noreferrer" className="ml-2">
                  <Image
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/01/author-twitter.png"
                    alt="Twitter"
                    width={19}
                    height={16}
                    className="cursor-pointer hover:opacity-80 transition-opacity"
                  />
                </Link>
              )}
              {/* LinkedIn icon - shows if second link exists */}
              {linkedinLink && (
                <Link href={linkedinLink} target="_blank" rel="noopener noreferrer" className="ml-1">
                  <Image
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/01/author-linkedin.png"
                    alt="LinkedIn"
                    width={19}
                    height={16}
                    className="cursor-pointer hover:opacity-80 transition-opacity"
                  />
                </Link>
              )}
            </div>
            <p className="author-tagline text-[#333333]">
              {data?.user?.description || `${data?.user?.name} is a member of the Fieldcamp team.`}
            </p>
          </div>
        </div>
        <AuthorLoadMore initialData={data?.user} />
        {data?.user?.seo?.jsonLd?.raw && (
          <div className="px-5 xl:px-0"
            dangerouslySetInnerHTML={{ __html: data?.user?.seo?.jsonLd?.raw }}
          ></div>
        )}
      </div>
    </>
  );
}

export async function generateMetadata({ params }: { params: any }) {
  const data = await getUserBySlug(params.uid, null).catch(() => notFound());

  // Set noindex, nofollow for ai-boss author
  const robots = params.uid === 'ai-boss'
    ? 'noindex, nofollow'
    : data?.user?.seo?.robots.join(",");

  return {
    title: data?.user?.seo?.title,
    description: data?.user?.seo?.description,
    robots: robots,
    alternates: { canonical: data?.user?.seo?.canonicalUrl },
    openGraph: {
      type: data?.user?.seo?.openGraph?.type || "website",
      description:
        data?.user?.seo?.openGraph?.description || data?.user?.seo?.description,
      title: data?.user?.seo?.title,
      url: data?.user?.seo?.canonicalUrl,
      images: [
        {
          url: data?.user?.seo?.openGraph?.image?.url || "",
        },
      ],
    },
  };
}
