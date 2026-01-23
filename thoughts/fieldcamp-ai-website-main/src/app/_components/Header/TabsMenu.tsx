"use client";
import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";

export const TabsMenu = ({
  menu,
  onLinkClick,
  currentPath,
}: {
  menu: any;
  onLinkClick: () => void;
  currentPath: string;
}) => {
  const [activeIndex, setActiveIndex] = useState<number | null>(0);
  const [openinnerSubMenu, setOpeninnerSubMenu] = useState<number | null>(null);
  const [isLargeScreen, setIsLargeScreen] = useState<boolean | null>(null);

  const handleInnerMenuClick = (id: number) => {
    {
      !isLargeScreen &&
        setOpeninnerSubMenu(openinnerSubMenu === id ? null : id);
    }
  };

  useEffect(() => {
    if (typeof window !== "undefined") {
      const handleResize = () => {
        setIsLargeScreen(window.innerWidth > 992);
      };
      handleResize();
      window.addEventListener("resize", handleResize);

      return () => {
        window.removeEventListener("resize", handleResize);
      };
    }
  }, []);

  const handleMouseEnter = (index: number) => {
    if (isLargeScreen) {
      setActiveIndex(index);
    }
  };

  const isActive = (url: string) => {
    if (url === "#") return false;
    if (typeof window === "undefined") return false; // Ensure window is defined
    try {
      const menuItemPath = new URL(url, window.location.origin).pathname;
      return menuItemPath === currentPath;
    } catch (error) {
      console.error("Error parsing URL:", error);
      return false;
    }
  };

  return (
    <div className="pt-0 xl:pt-3 first-child-menu">
      <div
        className="sub-menu tab-submenu
        relative min-[1023px]:absolute 
        min-[1023px]:left-1/2 min-[1023px]:-translate-x-1/2
        min-[1023px]:w-[240px]
        min-[1023px]:shadow-[0px_11.43px_34.28px_0px_rgba(0,0,0,0.1)] 
        rounded-[15px] 
        min-[1023px]:border min-[1023px]:border-[#E3E3E3] 
        min-[1023px]:hidden min-[1023px]:group-hover:block
        min-[1023px]:opacity-0 min-[1023px]:group-hover:opacity-100 
        min-[1023px]:transform min-[1023px]:translate-y-5  min-[1023px]:group-hover:translate-y-0 transition-all duration-300 ease-in-out bg-white"
      >
        <ul className=" flex  flex-col before:absolute before:content-[''] before:w-px before:left-2/4 before:inset-y-0 before:bg-[#ddd] before:hidden relative px-[5px] py-[5px] min-[1023px]:px-[10px] min-[1023px]:py-[10px]">
          {menu.map((submenuItem: any, index: number) => (
            <li
              key={submenuItem.id}
              className={`tab-submenu-li xl:pb-0 w-full ${
                Array.isArray(submenuItem.cssClasses)
                  ? submenuItem.cssClasses.join(" ")
                  : submenuItem.cssClasses || ""
              }  ${
                !isLargeScreen && submenuItem?.childItems?.nodes.length > 0
                  ? "has-children relative"
                  : ""
              } ${openinnerSubMenu === submenuItem.id ? "active" : ""}`}
            >
              <div className="flex items-center justify-between relative z-10">
                <Link
                  title={submenuItem.title}
                  href={
                    submenuItem.url && submenuItem.url != "null"
                      ? submenuItem.url
                      : "#"
                  }
                  className={` flex items-center  gap-2 peer  py-[7px] px-[15px] rounded-[11px] text-sm  w-auto min-[1024px]:w-[217px] text-[#232529]  min-[1024px]:hover:bg-[#F5F5F5] min-[1024px]:hover:border-[#E3E3E3]  decoration-transparent block ${
                    activeIndex === index && isLargeScreen ? "active" : ""
                  }  ${openinnerSubMenu === submenuItem.id && "active"} ${
                    isActive(submenuItem.url) ? "current-menu-item" : ""
                  }`}
                  onMouseEnter={() => handleMouseEnter(index)}
                >
                  {submenuItem?.menuACF?.icon?.node?.sourceUrl && (<Image src={submenuItem?.menuACF?.icon?.node?.sourceUrl} alt={submenuItem?.menuACF?.icon?.node?.altText} width={20} height={20} />)}
                  {submenuItem?.label}
                  {submenuItem.menuACF.subTitle && (
                    <span className="block text-xs text-[#9ca3af] pt-1">
                      {submenuItem.menuACF.subTitle}
                    </span>
                  )}
                </Link>
                {submenuItem?.childItems?.nodes.length > 0 && (
                  <span
                    className="relative min-[1024px]:hidden flex items-center justify-center right-[10px] w-[20px] h-[20px] cursor-pointer"
                    onClick={() => handleInnerMenuClick(submenuItem?.id)}
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="13"
                      height="7"
                      viewBox="0 0 13 7"
                      fill="none"
                    >
                      <path
                        fillRule="evenodd"
                        clipRule="evenodd"
                        d="M6.50009 6.55644L12.0402 1.01775L11.0239 0L6.50009 4.52381L1.97771 0L0.959961 1.01775L6.50009 6.55644Z"
                        fill="#252337"
                      />
                    </svg>
                  </span>
                )}
              </div>
              {submenuItem?.childItems?.nodes.length > 0 && (
                <ul
                  className={`mb-4 min-[1024px]:mb-2 tab-submenu-grand-ul z-20 relative min-[1024px]:absolute left-0 pl-[10px]  min-[1024px]:left-[245px] w-full  min-[1024px]:max-w-[450px] min-[1280px]:max-w-[500px] top-3 min-[1024px]:px-[15px] bg-white min-[1024px]:border-l border-[#E3E3E3] min-[1024px]:h-[91%] ${
                    (!isLargeScreen && openinnerSubMenu === submenuItem.id || (index === 0 && isLargeScreen))
                      ? "block"
                      : "hidden"
                  }`}
                >
                  {submenuItem.childItems?.nodes.map(
                    (submenuItem: any, index: number) => (
                      <li
                        key={submenuItem.id}
                        className={`xl:pt-1 grid-custom-li min-[1024px]:mb-2 ${
                          Array.isArray(submenuItem.cssClasses)
                            ? submenuItem.cssClasses.join(" ")
                            : submenuItem.cssClasses || ""
                        }`}
                      >
                        {/* <Link
                          title={submenuItem.label}
                          onClick={onLinkClick}
                          href={submenuItem.url}
                          className={`text-sm  text-gray-400 pl-3 pb-0 min-[1024px]:pb-3 block${
                            isActive(submenuItem.url) ? "current-menu-item" : ""
                          }`}
                        >
                          {submenuItem.label}
                        </Link> */}
                        {submenuItem?.childItems?.nodes.length > 0 && (
                          <ul className="grid grid-cols-1 min-[1024px]:grid-cols-3">
                            {submenuItem.childItems?.nodes.map(
                              (submenuItem: any, index: number) => (
                                <li
                                  key={submenuItem.id}
                                  className={`break-inside-avoid block py-1 my-0.5 px-3 min-[1024px]:hover:bg-[#F5F5F5] rounded-[0px] min-[1024px]:rounded-[7px]  ${
                                    Array.isArray(submenuItem.cssClasses)
                                      ? submenuItem.cssClasses.join(" ")
                                      : submenuItem.cssClasses || ""
                                  }`}
                                >
                                  <Link
                                    title={submenuItem.label}
                                    onClick={onLinkClick}
                                    href={submenuItem.url}
                                    className={`text-sm text-[#333333]  cursor-pointer  ${
                                      isActive(submenuItem.url)
                                        ? "current-menu-item"
                                        : ""
                                    }`}
                                  >
                                    {submenuItem.label}
                                  </Link>
                                </li>
                              )
                            )}
                          </ul>
                        )}
                      </li>
                    )
                  )}
                </ul>
              )}
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
};
