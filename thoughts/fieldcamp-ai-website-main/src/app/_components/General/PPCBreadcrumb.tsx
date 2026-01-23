import Link from "next/link"; // Ensure Link is imported

type BreadrumProps = {
  title: string;
  postType?:  "blog" | "review" | "alternative"; // Define postType prop
};

const Breadrum = ({ title, postType = 'blog'}: BreadrumProps) => {
  const getBreadcrumbLink = (postType: string) => {
    switch (postType) {
      case "review":
        return "/reviews";
      case "alternative":
        return "/alternatives";
      default:
        return "/blog";
    }
  };

  return (
    <div className="breadcrumb mb-4 md:mb-6 text-sm">
        <ol className="flex flex-wrap items-center gap-1.5 break-words text-sm text-muted-foreground sm:gap-2.5">
            <li className="inline-flex items-center gap-1.5">
                <Link href="/" className="transition-colors flex items-center gap-1 text-gray-500 hover:text-gray-700"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-house h-4 w-4"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg> Home</Link>
            </li>
           <li role="presentation" aria-hidden="true" className="inline-flex items-center gap-1.5">
           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-chevron-right h-4 w-4"><path d="m9 18 6-6-6-6"></path></svg>
           </li>
           <li className="inline-flex items-center gap-1.5">
                <Link href={getBreadcrumbLink(postType)} className="transition-colors flex items-center gap-1 text-gray-500 hover:text-gray-700">{postType.charAt(0).toUpperCase() + postType.slice(1)}</Link>
            </li>
            <li role="presentation" aria-hidden="true" className="inline-flex items-center gap-1.5">
           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-chevron-right h-4 w-4"><path d="m9 18 6-6-6-6"></path></svg>
           </li>
           <li className="inline-flex items-center gap-1.5">
                <span className="text-gray-900">{title}</span>
            </li>
        </ol>
    </div>
  );
};

export default Breadrum;
