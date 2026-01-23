import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";

export const SingleColumnMenu = ({
  menu,
  onLinkClick,
  currentPath,
}: {
  menu: any;
  onLinkClick: () => void;
  currentPath: string;
}) => {
  const [isClient, setIsClient] = useState(false);

  useEffect(() => {
    setIsClient(true);
  }, []);

  const isActive = (url: string) => {
    if (!isClient || url === "#") return false;
    try {
      const menuItemPath = new URL(url, window.location.origin).pathname;
      return menuItemPath === currentPath;
    } catch (error) {
      console.error("Error parsing URL:", error);
      return false;
    }
  };

  return (
    <div className="pt-0  xl:pt-3 first-child-menu">
      <div
        className="sub-menu 
        relative min-[1023px]:absolute 
        left-0
        min-[1023px]:w-[240px]
        min-[1023px]:shadow-[0px_11.43px_34.28px_0px_rgba(0,0,0,0.1)] 
        rounded-[15px] 
        min-[1023px]:border min-[1023px]:border-[#E3E3E3] 
        min-[1023px]:hidden min-[1023px]:group-hover:block
        min-[1023px]:opacity-0 min-[1023px]:group-hover:opacity-100 
        min-[1023px]:transform min-[1023px]:translate-y-5  min-[1023px]:group-hover:translate-y-0 transition-all duration-300 ease-in-out  bg-white"
      >
        <ul className="min-[1023px]:rounded-lg px-[5px] min-[1023px]:px-[10px] min-[1023px]:py-[10px]">
          {menu.map((submenuItem: any, index: number) => (
            <li key={submenuItem.id} className="mb-1 ">
              <Link
                title={submenuItem.label}
                onClick={onLinkClick}
                href={submenuItem.url}
                className={`flex items-center gap-2 py-[7px] px-[15px] rounded-[11px] text-sm text-[#232529] min-[1024px]:hover:bg-[#F5F5F5] min-[1024px]:hover:border-[#E3E3E3] decoration-transparent ${
                  isActive(submenuItem.url) ? "current-menu-item" : ""
                }`}
              >
                {submenuItem?.menuACF?.icon?.node?.sourceUrl && (<Image src={submenuItem?.menuACF?.icon?.node?.sourceUrl} alt={submenuItem?.menuACF?.icon?.node?.altText} width={20} height={20} />)}
                {submenuItem.label}
                <span className="block text-xs text-[#667085] pt-1">
                  {submenuItem.menuACF.subTitle}
                </span>
              </Link>
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
};
