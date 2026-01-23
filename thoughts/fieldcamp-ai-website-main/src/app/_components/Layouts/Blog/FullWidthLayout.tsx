import { createTransformFunction } from "@/app/_components/General/TransformHTML";
import parse from "html-react-parser";

export default function FullWidthLayout({ data, altTextMap }: any) {

    const replacedContent = parse(data?.content, { transform: createTransformFunction(altTextMap)});
    return (
        <div className="bg-white w-full border-b border-[#ECECEC]">
          <div className="w-full">
            <div id="blog_content_wrapper" className="mt-[90px] sm:mt-[72px] blog_content_wrapper flex relative w-full">
              <div className={`w-full max-w-full grow postid-${data.postId}`}>
                <div className="listicles-full-width">
                  {replacedContent}
                </div>
              </div>
            </div>
          </div>
        </div>
    );
}