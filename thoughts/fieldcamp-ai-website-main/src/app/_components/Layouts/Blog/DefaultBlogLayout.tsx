import parse from "html-react-parser";
import FieldServiceCTA from "../../General/FieldServiceCTA";
import Image from "next/image";
import Link from "next/link";
import { createTransformFunction } from "../../General/TransformHTML";
import { tocItems } from "../../General/TransformHTML";
import BlogTOC from "../../General/BlogTOC";
import Breadrum from "../../General/Breadcrumb";

export default function DefaultBlogLayout({ data, postType, altTextMap }: any) {

  const readingTime = getReadingTime(data.content);
  tocItems.length = 0;
  const replacedContent = parse(data?.content, { transform: createTransformFunction(altTextMap)});
    return (
      <>
      <article className="pt-[50px] md:pt-[70px] mt-[80px] md:mt-[100px] lg:mt-[120px] xl:mt-14 lg:px-5 xl:px-0">
          <Breadrum postType={postType} title={data.seo?.title || data.title} />
          <h1 className="text-[34px] md:text-4xl font-medium mb-4 lg:mb-8 max-w-[733px] mx-auto text-left px-[20px] lg:px-[0px]">
            {data.title}
          </h1>
          <div className="flex items-center gap-4 blog-time-header">
            <div>
              <p className="publish-date">
                {new Date(data.date).toLocaleDateString("en-US", {
                  year: "numeric",
                  month: "long",
                  day: "numeric",
                })}{" "}
                - {readingTime} min read
              </p>
            </div>
          </div>
          {data.featuredImage && data.featuredImage.node && (
            <div className="my-8 featured-image px-[20px] md:mx-10 lg:mx-auto">
              <Image
                src={data.featuredImage.node.sourceUrl}
                alt={data.featuredImage.node.altText || data.title}
                width={800}
                height={400}
                layout="responsive"
                objectFit="cover"
                priority={true}
                loading="eager" 
              />
            </div>
          )}
        </article>
        <article className="blog-artical-wrapper max-w-[1245px] mx-auto flex flex-col xl:flex-row  items-normal gap-[20px] xl:gap-[50px] md:px-5 xl:px-0">
          
          <div className="blogtoc-wrapper">
            <div className="authordetails flex items-center gap-2 mb-5 xl:mb-8 mx-auto max-w-[733px] w-full">
              <div className="authorImage">
                <Image src={data.author?.node?.userDetails?.authorPic?.node?.sourceUrl || ''} alt={data.author?.node?.userDetails?.authorPic?.node?.altText || data.author?.node?.name || ''} width={60} height={60} />
              </div>
              <div className="author">
                <div className="authorName text-base text-gray-900 font-medium"><Link href={data.author?.node?.uri}>{data.author?.node?.name || 'Anonymous'}</Link></div>
                <div className="authorpost text-sm text-gray-900">{data.author?.node?.userDetails?.designation || 'Fieldcamp CEO'}</div>
              </div>
            </div>
            <BlogTOC content={tocItems} />
          </div>
          
          <div className="blog-artical blog_content_wrapper" >
            {replacedContent}
          </div>
          <FieldServiceCTA />
        </article>
      </>
    );
  }

  function getReadingTime(content: string): number {
    const wordsPerMinute = 200;
    const wordCount = content.trim().split(/\s+/).length;
    return Math.ceil(wordCount / wordsPerMinute);
  }