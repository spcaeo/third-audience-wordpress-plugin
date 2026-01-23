"use client";
import React from "react";
import Link from "next/link";
import Image from "next/image";

export const PlatformMenu = ({
  menu,
  onLinkClick,
  isOpen,
  currentPath
}: {
  menu: any;
  onLinkClick: () => void,
  isOpen: boolean,
  currentPath: string
}) => {

  // Filter top-level menu items - first 3 for cards (left section)
  const columns = menu.filter((item: any) => item.parentId === null || !item.parentId);
  const cardItems = columns.slice(0, 3);

  return (
    <div className="pt-0 xl:pt-3 first-child-menu PlatformMenuWrapper">
      <style jsx>{`
        .inter-font {
          font-family: Inter, sans-serif;
        }
        .PlatformSubMenu {
          width: 100%;
        }
        @media (min-width: 1023px) {
          .PlatformSubMenu {
            width: min(calc(100vw - 40px), 1200px);
          }
        }
        @media (min-width: 1320px) {
          .PlatformSubMenu {
            width: min(calc(100vw - 60px), 1200px);
          }
        }
        @media (min-width: 1440px) {
          .PlatformSubMenu {
            width: min(calc(100vw - 80px), 1200px);
          }
        }
        .PlatformCardImage {
          border-radius: 8px;
          overflow: hidden;
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        .PlatformExploreBtn {
          background: linear-gradient(90deg, rgba(251, 207, 232, 0.6) 0%, rgba(244, 235, 255, 0.6) 100%);
          transition: all 0.3s ease-in-out;
        }
        .PlatformExploreBtn:hover {
          background: linear-gradient(90deg, rgba(251, 207, 232, 0.9) 0%, rgba(244, 235, 255, 0.9) 100%);
        }
      `}</style>

      {/* Desktop View */}
      <div className={`PlatformSubMenu sub-menu relative min-[1023px]:fixed min-[1023px]:left-1/2 min-[1023px]:-translate-x-1/2 shadow-[0px_20px_25px_-5px_rgba(0,0,0,0.1),0px_10px_10px_-5px_rgba(0,0,0,0.04)] rounded-none min-[1023px]:rounded-2xl border border-gray-200 bg-white transition-all duration-300 ease-in-out ${isOpen ? 'block opacity-100 translate-y-0' : 'hidden opacity-0 translate-y-2'}`}>

        <div className="PlatformMenuMain grid grid-cols-1 min-[1023px]:grid-cols-12">

          {/* Left Section - Cards */}
          <div className="PlatformLeftSection col-span-1 min-[1023px]:col-span-9 p-4 min-[1023px]:p-6">
            {/* Header */}
            <p className="PlatformMenuHeader">
              EXPLORE THE PLATFORM
            </p>

            {/* Three Column Cards Grid */}
            <div className="PlatformMenuGrid grid grid-cols-1 min-[1023px]:grid-cols-3 gap-4 min-[1023px]:gap-5">
              {cardItems.map((item: any) => (
                <Link
                  key={item.id}
                  href={item.url || '#'}
                  onClick={onLinkClick}
                  className="PlatformCard block rounded-xl relative overflow-hidden"
                >
                  {/* Card Title */}
                  <p className="PlatformCardTitle">
                    {item.label}
                  </p>

                  {/* Card Image */}
                  <div className="PlatformCardImage bg-gray-50 rounded-lg overflow-hidden border border-gray-200">
                    {item.menuACF?.icon?.node?.sourceUrl ? (
                      <img
                        src={item.menuACF.icon.node.sourceUrl}
                        alt={item.menuACF.icon.node.altText || item.label}
                        className="w-full h-auto object-cover"
                      />
                    ) : (
                      <div className="w-full h-[140px] bg-gray-100 flex items-center justify-center">
                        <span className="text-gray-400 text-sm">No image</span>
                      </div>
                    )}
                  </div>

                  {/* Hover Overlay - Full Card */}
                  <div className="PlatformCardOverlay">
                    {/* Overlay Title */}
                    <p className="PlatformCardOverlayTitle">
                      {item.label}
                    </p>
                    {/* Overlay Subheading - from ACF */}
                    <p className="PlatformCardOverlaySubhead">
                      {item.menuACF?.subhead || `${item.label} every workflow and automate your entire field operations.`}
                    </p>
                    {/* Arrow Icon */}
                    <div className="PlatformCardOverlayArrow">
                      <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                      </svg>
                    </div>
                  </div>
                </Link>
              ))}
            </div>

          </div>

          {/* Right Section - Sidebar (Static) - Using Global Classes */}
          <div className="PlatformRightSection MenuSidebar col-span-1 min-[1023px]:col-span-3 min-[1023px]:flex">
            {/* Header */}
            <p className="MenuSidebarHeader">
              AI-POWERED FEATURES
            </p>

            {/* Sidebar List - Static Links */}
            <div className="MenuSidebarList">
              <Link
                href="https://fieldcamp.ai/features/ai-job-scheduling/"
                onClick={onLinkClick}
                className="MenuSidebarItem"
              >
                AI Scheduling
              </Link>
              <Link
                href="https://fieldcamp.ai/features/ai-dispatch-scheduling/"
                onClick={onLinkClick}
                className="MenuSidebarItem"
              >
                AI Dispatcher
              </Link>
              <Link
                href="https://fieldcamp.ai/ai-receptionist/"
                onClick={onLinkClick}
                className="MenuSidebarItem"
              >
                Personal Assistant
              </Link>
              <Link
                href="https://fieldcamp.ai/features/ai-workflow-builder/"
                onClick={onLinkClick}
                className="MenuSidebarItem"
              >
                AI Workflow Builder
              </Link>
              <Link
                href="https://fieldcamp.ai/online-booking/"
                onClick={onLinkClick}
                className="MenuSidebarItem"
              >
                Conversational Booking Bot
              </Link>
            </div>
          </div>

        </div>

        {/* Full Width Bottom Links Section */}
        <div className="PlatformBottomLinks hidden min-[1023px]:flex justify-between items-center px-6 py-4">
          {/* Left Side - Menu Links */}
          <div className="PlatformBottomLinksLeft flex gap-3">
            <Link
              href="https://fieldcamp.ai/mobile-app-download/"
              onClick={onLinkClick}
              className="PlatformBottomLink"
            >
              Download Mobile App
            </Link>
            <Link
              href="https://calendly.com/jeel-fieldcamp/30min"
              onClick={onLinkClick}
              className="PlatformBottomLink calendly-open"
            >
              Book a Demo
            </Link>
            <Link
              href="https://fieldcamp.ai/integrations/"
              onClick={onLinkClick}
              className="PlatformBottomLink"
            >
              All Integrations
            </Link>
          </div>

          {/* Right Side - Explore Button */}
          <div className="PlatformBottomLinksRight">
            <Link
              href="https://fieldcamp.ai/features/"
              onClick={onLinkClick}
              className="MenuSidebarBtn"
            >
              Explore more features
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};
