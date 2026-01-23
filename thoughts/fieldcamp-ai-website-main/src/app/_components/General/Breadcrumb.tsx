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
    <div className="breadcrumb mb-4 text-sm max-w-[733px] mx-auto px-[20px] lg:px-[0px] ">
      <Link href="/" className="text-gray-600 hover:text-gray-900">Home</Link>
      <span className="mx-2 text-gray-400">&gt;</span>
      <Link href={getBreadcrumbLink(postType)} className="text-gray-600 hover:text-gray-900">{postType.charAt(0).toUpperCase() + postType.slice(1)}</Link>
      <span className="mx-2 text-gray-400">&gt;</span>
      <span className="text-gray-900">{title}</span>
    </div>
  );
};

export default Breadrum;
