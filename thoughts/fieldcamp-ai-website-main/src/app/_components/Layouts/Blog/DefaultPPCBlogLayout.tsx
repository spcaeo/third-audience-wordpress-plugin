import parse from "html-react-parser";
import Image from "next/image";
import Link from "next/link";
import { createTransformFunction } from "../../General/TransformHTML";
import { tocItems } from "../../General/TransformHTML";
import PPCBreadcrumb from "../../General/PPCBreadcrumb";

export default function DefaultPPCBlogLayout({ data, postType, altTextMap }: any) {

  const readingTime = getReadingTime(data.content);
  tocItems.length = 0;
  const replacedContent = parse(data?.content, { transform: createTransformFunction(altTextMap)});
    return (
      <>
        <div className="ppc-blog-artical-top-wrapper min-[1280px]:pt-[70px] mt-[80px] md:mt-[100px] lg:mt-[120px] xl:mt-14 max-w-4xl mx-auto px-6">
            <PPCBreadcrumb postType={postType} title={data.seo.title} />
            <h1 className="text-4xl md:text-5xl font-medium text-gray-900 leading-tight">
                {data.title}
            </h1>
            <div className="flex flex-col sm:flex-row sm:items-center gap-4 text-gray-600 mt-4 md:mt-6">
                <div className="authordetails inline-flex items-center gap-2">
                <div className="authorImage">
                    <Image src={data.author?.node?.userDetails?.authorPic?.node?.sourceUrl || ''} alt={data.author?.node?.userDetails?.authorPic?.node?.altText || data.author?.node?.name || ''} width={50} height={50} />
                </div>
                <div className="author">
                    <div className="authorName font-medium text-gray-900"><Link href={data.author?.node?.uri}>{data.author?.node?.name || 'Anonymous'}</Link></div>
                    <div className="authorpost text-sm text-gray-500">{data.author?.node?.userDetails?.designation || 'Fieldcamp CEO'}</div>
                </div>
                </div>

                <div className="hidden sm:block w-px h-8 bg-gray-200"></div>
                <div className="text-sm text-gray-500">
                    <p className="publish-date">
                        <span className="font-medium">Published on </span>
                        {new Date(data.date).toLocaleDateString("en-US", {
                            year: "numeric",
                            month: "long",
                            day: "numeric",
                            })}{" "}
                        </p>
                    </div>
                </div>

            {data.featuredImage && data.featuredImage.node && (
                <div className="mt-6 md:mt-8 md:mb-16 mb-8 featured-image rounded-2xl overflow-hidden">
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
        </div>
        <div className="ppc-blog-artical-bottom-wrapper">
          <div className="blog-artical blog_content_wrapper" >
            {replacedContent}
          </div>
        </div>
      </>
    );
  }

  function getReadingTime(content: string): number {
    const wordsPerMinute = 200;
    const wordCount = content.trim().split(/\s+/).length;
    return Math.ceil(wordCount / wordsPerMinute);
  }