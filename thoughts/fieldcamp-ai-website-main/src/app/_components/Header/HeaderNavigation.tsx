"use client";
import Link from "next/link";
import Image from "next/image";
import React, { useState, useEffect, useRef } from "react";
import { SingleColumnMenu } from "./SingleColumnMenu";
import { TwoColumnMenu } from "./TwoColumnMenu";
import { TabsMenu } from "./TabsMenu";
import { TabsMenuNew } from "./TabsMenuNew";
import { SolutionMenu } from "./SolutionMenu";
import { SolutionMenuLayout } from "./SolutionMenuLayout";
import { ResourceMenu } from "./ResourceMenu";
import { PlatformMenu } from "./PlatformMenu";
import { NewSolutionMenu } from "./NewSolutionMenu";
import { NewResourceMenu } from "./NewResourceMenu";
import { usePathname } from "next/navigation";
import { CalendlyEmbed } from "../General/Custom";

export default function HeaderNavigation({ menuItems, CustomerLoginMenuItems }: { menuItems: any, CustomerLoginMenuItems: any }) {
  const [openSubmenu, setOpenSubmenu] = useState<number | null>(null);
  const [openMobileMenu, setopenMobileMenu] = useState<boolean | null>(false);
  const [isSolutionMenuOpen, setIsSolutionMenuOpen] = useState<boolean>(false);
  const [isTabsMenuNewOpen, setIsTabsMenuNewOpen] = useState<boolean>(false);
  const menuRef = useRef<HTMLDivElement>(null);
  const hoverTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const [isLargeScreen, setIsLargeScreen] = useState<boolean | null>(false);
  const pathname = usePathname();
  const [isClient, setIsClient] = useState(false);

  const handleMenuClick = (id: number) => {
    setOpenSubmenu(openSubmenu === id ? null : id);
  };

  // Hover handlers for desktop
  const handleMenuEnter = (id: number) => {
    if (hoverTimeoutRef.current) {
      clearTimeout(hoverTimeoutRef.current);
      hoverTimeoutRef.current = null;
    }
    setOpenSubmenu(id);
  };

  const handleMenuLeave = () => {
    hoverTimeoutRef.current = setTimeout(() => {
      setOpenSubmenu(null);
      setIsSolutionMenuOpen(false);
      setIsTabsMenuNewOpen(false);
    }, 150);
  };

  const toggleTabsMenuNew = (menuId: number) => {
    setIsTabsMenuNewOpen(!isTabsMenuNewOpen);
    setIsTabsMenuNewOpen(false);
    setOpenSubmenu(openSubmenu === menuId ? null : menuId);
  };

  const handleDocumentClick = (event: MouseEvent) => {
    if (menuRef.current && !menuRef.current.contains(event.target as Node)) {
      setOpenSubmenu(null);
        setIsSolutionMenuOpen(false);
        setIsTabsMenuNewOpen(false);
    }
  };

  const toggleopenMobileMenu = () => {
    setopenMobileMenu(!openMobileMenu);
  };

  const handleSubmenuLinkClick = () => {
    setOpenSubmenu(null);
    setopenMobileMenu(false);
    setIsSolutionMenuOpen(false);
    setIsTabsMenuNewOpen(false);
  };
  
  const toggleSolutionMenu = () => {
    setIsSolutionMenuOpen(!isSolutionMenuOpen);
  };






  useEffect(() => {
    {
      isLargeScreen && document.addEventListener("click", handleDocumentClick);
      return () => {
        document.removeEventListener("click", handleDocumentClick);
      };
    }
  }, [isLargeScreen]);

  useEffect(() => {
    if (typeof window !== "undefined") {
      const handleResize = () => {
        setIsLargeScreen(window.innerWidth > 1024);
      };
      handleResize();
      window.addEventListener("resize", handleResize);

      return () => {
        window.removeEventListener("resize", handleResize);
      };
    }
  }, []);

  useEffect(() => {
    // This ensures the code only runs on the client
    setIsClient(true);
  }, []);

  // Cleanup hover timeout on unmount
  useEffect(() => {
    return () => {
      if (hoverTimeoutRef.current) {
        clearTimeout(hoverTimeoutRef.current);
      }
    };
  }, []);

  // Recursively check for active menu in all levels
  const isActiveMenu = (childItems: any[]): boolean => {
    if (!isClient) return false; // Ensure it's client-side

    const checkChildItems = (items: any[]): boolean => {
      return items.some((child: any) => {
        if (!child.url) return false;

        try {
          const childUrl = new URL(child.url, window.location.origin);

          // Match pathname with current child item's URL
          if (childUrl.pathname === pathname && "/" !== pathname) {
            return true;
          }

          // Recursively check child items if they exist (second and third levels)
          if (
            child.childItems &&
            child.childItems.nodes &&
            child.childItems.nodes.length > 0
          ) {
            return checkChildItems(child.childItems.nodes);
          }
        } catch (err) {
          console.error(`Invalid URL encountered: ${child.url}`, err);
        }

        return false;
      });
    };

    return checkChildItems(childItems);
  };

  return (
    <>
      {!isLargeScreen && (
        <button
          className={`menu-toggle min-[1023px]:hidden ml-auto`}
          aria-controls="primary-menu"
          onClick={toggleopenMobileMenu}
        >
          <span className="gp-icon icon-menu-bars flex">
            <svg
              viewBox="0 0 512 512"
              xmlns="http://www.w3.org/2000/svg"
              className={`w-[20px] h-[20px] top-2 block`}
            >
              {!openMobileMenu ? (
                <path d="M0 96c0-13.255 10.745-24 24-24h464c13.255 0 24 10.745 24 24s-10.745 24-24 24H24c-13.255 0-24-10.745-24-24zm0 160c0-13.255 10.745-24 24-24h464c13.255 0 24 10.745 24 24s-10.745 24-24 24H24c-13.255 0-24-10.745-24-24zm0 160c0-13.255 10.745-24 24-24h464c13.255 0 24 10.745 24 24s-10.745 24-24 24H24c-13.255 0-24-10.745-24-24z" />
              ) : (
                <path d="M71.029 71.029c9.373-9.372 24.569-9.372 33.942 0L256 222.059l151.029-151.03c9.373-9.372 24.569-9.372 33.942 0 9.372 9.373 9.372 24.569 0 33.942L289.941 256l151.03 151.029c9.372 9.373 9.372 24.569 0 33.942-9.373 9.372-24.569 9.372-33.942 0L256 289.941l-151.029 151.03c-9.373 9.372-24.569 9.372-33.942 0-9.372-9.373-9.372-24.569 0-33.942L222.059 256 71.029 104.971c-9.372-9.373-9.372-24.569 0-33.942z" />
              )}
            </svg>
          </span>
        </button>
      )}
      <nav
        ref={menuRef}
        className={`${
          !openMobileMenu ? "hidden" : "block"
        } p-[5px] xl:mt-[15px] min-[1023px]:p-0 fixed z-[1] bg-white min-[1023px]:bg-transparent top-[51px] xl:top-20 shadow-lg min-[1023px]:shadow-none min-[1023px]:rounded-2xl rounded-none h-[calc(100vh_-_50px)] overflow-y-auto left-0 right-0 min-[1023px]:static min-[1023px]:overflow-visible min-[1023px]:h-auto min-[1023px]:flex justify-between`}
      >
        <ul className="nav-ul flex flex-col min-[1023px]:flex-row *:border-b border-slate-200 min-[1023px]:*:border-none desktop-menu">
          {menuItems.menuItems.nodes.map(
            (menu: any, index: number) => {
              
              return menu.parentId == null && (
                <li
                  key={menu.id}
                  className={`main-menu py-1.5 lg:py-0 group  ${
                    menu.menuACF.isButton == "yes" && isLargeScreen
                      ? "menu-contact-us-btn mt-3"
                      : "relative"
                  }  ${
                    menu.childItems.nodes.length > 0
                      ? "has-children"
                      : "no-children"
                  } ${openSubmenu === menu.id ? "active" : "not-active"} ${
                    isActiveMenu(menu.childItems.nodes) ? "border-active" : ""
                  }`}
                  onMouseEnter={() => isLargeScreen && menu.childItems.nodes.length > 0 && handleMenuEnter(menu.id)}
                  onMouseLeave={() => isLargeScreen && menu.childItems.nodes.length > 0 && handleMenuLeave()}
                >
                  {Array.isArray(menu.menuACF?.menuType) &&
                    menu.menuACF?.menuType[0] == "tabsMenuNew" ? (
                    <button
                      onClick={() => !isLargeScreen && toggleTabsMenuNew(menu.id)}
                      className="min-[1023px]:py-1 justify-between w-full text-[#232529] decoration-transparent leading-[32px] block main-menu px-3 min-[1023px]:px-3 rounded-[5px] flex items-center cursor-pointer"
                    >
                      {menu.label}
                      <svg className="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </button>
                  ) : ((Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType.some((type: string) =>
                          type.toLowerCase() === "solutionmenulayout")) ||
                       (typeof menu.menuACF?.menuType === 'string' &&
                        menu.menuACF?.menuType.toLowerCase() === "solutionmenulayout")) ? (
                    <button
                      onClick={() => !isLargeScreen && handleMenuClick(menu.id)}
                      className="min-[1023px]:py-1 justify-between w-full text-[#232529] decoration-transparent leading-[32px] block main-menu px-3 min-[1023px]:px-3 rounded-[5px] flex items-center cursor-pointer"
                    >
                      {menu.label}
                      <svg className="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </button>
                  ) : Array.isArray(menu.menuACF?.menuType) &&
                    menu.menuACF?.menuType[0] == "resourceMenu" ? (
                    <button
                      onClick={() => !isLargeScreen && handleMenuClick(menu.id)}
                      className="min-[1023px]:py-1 justify-between w-full text-[#232529] decoration-transparent leading-[32px] block main-menu px-3 min-[1023px]:px-3 rounded-[5px] flex items-center cursor-pointer"
                    >
                      {menu.label}
                      <svg className="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </button>
                  ) : Array.isArray(menu.menuACF?.menuType) &&
                    menu.menuACF?.menuType[0] == "platformMenu" ? (
                    <button
                      onClick={() => !isLargeScreen && handleMenuClick(menu.id)}
                      className="min-[1023px]:py-1 justify-between w-full text-[#232529] decoration-transparent leading-[32px] block main-menu px-3 min-[1023px]:px-3 rounded-[5px] flex items-center cursor-pointer"
                    >
                      {menu.label}
                      <svg className="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </button>
                  ) : Array.isArray(menu.menuACF?.menuType) &&
                    menu.menuACF?.menuType[0] == "newSolutionMenu" ? (
                    <button
                      onClick={() => !isLargeScreen && handleMenuClick(menu.id)}
                      className="min-[1023px]:py-1 justify-between w-full text-[#232529] decoration-transparent leading-[32px] block main-menu px-3 min-[1023px]:px-3 rounded-[5px] flex items-center cursor-pointer"
                    >
                      {menu.label}
                      <svg className="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </button>
                  ) : Array.isArray(menu.menuACF?.menuType) &&
                    menu.menuACF?.menuType[0] == "newResourceMenu" ? (
                    <button
                      onClick={() => !isLargeScreen && handleMenuClick(menu.id)}
                      className="min-[1023px]:py-1 justify-between w-full text-[#232529] decoration-transparent leading-[32px] block main-menu px-3 min-[1023px]:px-3 rounded-[5px] flex items-center cursor-pointer"
                    >
                      {menu.label}
                      <svg className="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </button>
                  ) : (
                    <Link
                      title={menu.label}
                      href={
                        menu.url && menu.childItems.nodes.length < 1
                          ? menu.url
                          : "#"
                      }
                      className={`${
                        menu.childItems.nodes.length > 0 ? "  mr-[15px]" : ""
                      }  ${
                        menu.menuACF.isButton == "yes" && isLargeScreen
                          ? "min-[1023px]:ml-2 lg:ml-4 h-11 rounded-[10px] flex items-center px-4 min-[1023px]:px-6"
                          : " min-[1023px]:py-1 text-[#232529] decoration-transparent leading-[32px] block main-menu px-3 min-[1023px]:px-3  rounded-[5px] "
                      }`}
                      
                    >
                      {menu.label}
                      {menu.childItems.nodes.length > 0 && !Array.isArray(menu.menuACF?.menuType) && (
                        <span className="min-[1023px]:hidden absolute top-[12px] flex items-center justify-center right-[10px] w-[20px] h-[20px]" onClick={() => handleMenuClick(menu.id)}>
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
                    </Link>
                  )}

                  {menu.childItems.nodes.length > 0 && (
                    <>
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF.menuType[0] === "singleColumn" && (
                          <SingleColumnMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "twoColumn" && (
                          <TwoColumnMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "tabsMenu" && (
                          <TabsMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "tabsMenuNew" && (
                          <TabsMenuNew
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                      {((Array.isArray(menu.menuACF?.menuType) && 
                         menu.menuACF?.menuType.some((type: string) => 
                           type.toLowerCase() === "solutionmenulayout")) ||
                        (typeof menu.menuACF?.menuType === 'string' && 
                         menu.menuACF?.menuType.toLowerCase() === "solutionmenulayout")) && (
                          <SolutionMenuLayout
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "resourceMenu" && (
                          <ResourceMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "platformMenu" && (
                          <PlatformMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "newSolutionMenu" && (
                          <NewSolutionMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "newResourceMenu" && (
                          <NewResourceMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                    </>
                  )}
                </li>
              )
            }
          )}



        </ul>
        <div className="min-[1023px]:hidden menuRight">
        <ul className="nav-ul flex flex-col min-[1023px]:flex-row *:border-b border-slate-200 min-[1023px]:*:border-none">
          {CustomerLoginMenuItems.menuItems.nodes.map(
            (menu: any, index: number) =>
              menu.parentId == null && (
                <li
                  key={menu.id}
                  className={`main-menu py-1.5 min-[1023px]:py-0 group  ${
                    menu.menuACF.isButton == "yes" && isLargeScreen
                      ? "menu-contact-us-btn mt-3"
                      : "relative"
                  }  ${
                    menu.childItems.nodes.length > 0
                      ? "has-children"
                      : "no-children"
                  } ${openSubmenu === menu.id ? "active" : "not-active"} ${
                    isActiveMenu(menu.childItems.nodes) ? "border-active" : ""
                  }`}
                >
                  <Link
                    title={menu.label}
                    href={
                      menu.url && menu.childItems.nodes.length < 1
                        ? menu.url
                        : "#"
                    }
                    className={`${menu.url} ${
                      menu.childItems.nodes.length > 0 ? "  px-3 min-[1023px]:px-5 mr-[15px]" : ""
                    }  ${
                      menu.menuACF.isButton == "yes" && isLargeScreen
                        ? "min-[1023px]:ml-2 min-[1023px]:ml-4 h-11 rounded-[10px] flex items-center px-4 min-[1023px]:px-6"
                        : " min-[1023px]:py-1 text-[#232529]  decoration-transparent leading-[32px] block main-menu px-3 min-[1023px]:px-5  rounded-[5px] "
                    }`}
                    
                  >
                    {menu.label}
                    {menu.childItems.nodes.length > 0 && (
                      <span className="min-[1023px]:hidden absolute top-[12px] flex items-center justify-center right-[10px] w-[20px] h-[20px]" onClick={() => handleMenuClick(menu.id)}>
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
                  </Link>

                  {menu.childItems.nodes.length > 0 && (
                    <>
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF.menuType[0] === "singleColumn" && (
                          <SingleColumnMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "twoColumn" && (
                          <TwoColumnMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "tabsMenu" && (
                          <TabsMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "tabsMenuNew" && (
                          <TabsMenuNew
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                      {((Array.isArray(menu.menuACF?.menuType) && 
                         menu.menuACF?.menuType.some((type: string) => 
                           type.toLowerCase() === "solutionmenulayout")) ||
                        (typeof menu.menuACF?.menuType === 'string' && 
                         menu.menuACF?.menuType.toLowerCase() === "solutionmenulayout")) && (
                          <SolutionMenuLayout
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "resourceMenu" && (
                          <ResourceMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                    </>
                  )}
                </li>
              )
          )}
        </ul>
        </div>
      </nav>
      <div className="min-[1023px]:block hidden menuRight xl:mt-[15px]">
      {CustomerLoginMenuItems.menuItems.nodes.length > 0 && (
      <ul className="nav-ul flex flex-col min-[1023px]:flex-row *:border-b border-slate-200 min-[1023px]:*:border-none">
          {CustomerLoginMenuItems.menuItems.nodes.map(
            (menu: any, index: number) =>
              menu.parentId == null && (
                <li
                  key={menu.id}
                  className={`${menu.cssClasses.join(' ')} main-menu login-btn-new py-1.5 min-[1023px]:py-0 group  ${
                    menu.menuACF.isButton == "yes" && isLargeScreen
                      ? "menu-contact-us-btn ml-4"
                      : "relative"
                  }  ${
                    menu.childItems.nodes.length > 0
                      ? "has-children"
                      : "no-children"
                  } ${openSubmenu === menu.id ? "active" : "not-active"} ${
                    isActiveMenu(menu.childItems.nodes) ? "border-active" : ""
                  }`}
                  onMouseEnter={() => menu.childItems.nodes.length > 0 && handleMenuEnter(menu.id)}
                  onMouseLeave={() => menu.childItems.nodes.length > 0 && handleMenuLeave()}
                >
                  <Link
                    title={menu.label}
                    href={
                      menu.url && menu.childItems.nodes.length < 1
                        ? menu.url
                        : "#"
                    }
                    className={`
                      ${menu.url && menu.url.includes('calendly.com') && 'calendly-open'}
                      ${
                      menu.childItems.nodes.length > 0 ? "  mr-[5px]" : ""
                    }  ${
                      menu.menuACF.isButton == "yes" && isLargeScreen
                        ? "hover:opacity-90 transition-opacity shadow-lg bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-[5px] text-white px-3 min-[1023px]:px-3 py-2 hidden min-[1023px]:flex items-center gap-2"
                        : " min-[1023px]:py-1 text-[#232529] decoration-transparent leading-[32px] block main-menu px-3 min-[1023px]:px-3  rounded-[5px] "
                    }`}

                  >
                    {menu.url && menu.url.includes('calendly.com') && (
                      <span className="w-5 h-5 flex-shrink-0">
                        <Image
                          src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/book-calander-ic.svg"
                          alt="Calendar"
                          width={20}
                          height={20}
                          priority
                          className="w-5 h-5"
                        />
                      </span>
                    )}
                    {menu.label}
                    {menu.childItems.nodes.length > 0 && (
                      <span className="min-[1023px]:hidden absolute w-[20px] h-[20px] top-[12px] right-[10px] flex items-center justify-center" onClick={() => handleMenuClick(menu.id)}>
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
                  </Link>

                  {menu.childItems.nodes.length > 0 && (
                    <>
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF.menuType[0] === "singleColumn" && (
                          <SingleColumnMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "twoColumn" && (
                          <TwoColumnMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "tabsMenu" && (
                          <TabsMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "tabsMenuNew" && (
                          <TabsMenuNew
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                      {((Array.isArray(menu.menuACF?.menuType) && 
                         menu.menuACF?.menuType.some((type: string) => 
                           type.toLowerCase() === "solutionmenulayout")) ||
                        (typeof menu.menuACF?.menuType === 'string' && 
                         menu.menuACF?.menuType.toLowerCase() === "solutionmenulayout")) && (
                          <SolutionMenuLayout
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                      {Array.isArray(menu.menuACF?.menuType) &&
                        menu.menuACF?.menuType[0] == "resourceMenu" && (
                          <ResourceMenu
                            menu={menu.childItems.nodes}
                            onLinkClick={handleSubmenuLinkClick}
                            currentPath={pathname}
                            isOpen={openSubmenu === menu.id}
                          />
                        )}
                    </>
                  )}
                  {menu.url && menu.url.includes('calendly.com') && <CalendlyEmbed />}
                </li>
              )
          )}
        </ul>
      )}
      </div>
    </>
  );
}
