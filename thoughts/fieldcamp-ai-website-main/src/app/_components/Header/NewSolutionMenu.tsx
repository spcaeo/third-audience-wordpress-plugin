"use client";
import React from "react";
import Link from "next/link";
import Image from "next/image";

export const NewSolutionMenu = ({
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

  // Filter top-level menu items (sections like BY SIZE, BY ROLE, BY INDUSTRY)
  const sections = menu.filter((item: any) => item.parentId === null || !item.parentId);

  // Separate sections for left and right columns
  // Left column: BY SIZE, BY ROLE (first 2 sections)
  // Right column: BY INDUSTRY (3rd section)
  const leftSections = sections.slice(0, 2);
  const rightSection = sections[2];

  return (
    <div className="pt-0 xl:pt-3 first-child-menu NewSolutionMenuWrapper">
      {/* Desktop View */}
      <div className={`NewSolutionSubMenu sub-menu relative min-[1023px]:fixed min-[1023px]:left-1/2 min-[1023px]:-translate-x-1/2 shadow-[0px_20px_25px_-5px_rgba(0,0,0,0.1),0px_10px_10px_-5px_rgba(0,0,0,0.04)] rounded-none min-[1023px]:rounded-2xl border border-gray-200 bg-white transition-all duration-300 ease-in-out ${isOpen ? 'block opacity-100 translate-y-0' : 'hidden opacity-0 translate-y-2'}`}>

        <div className="NewSolutionMenuMain grid grid-cols-1 min-[1023px]:grid-cols-12">

          {/* Left Section - BY SIZE & BY ROLE */}
          <div className="NewSolutionLeftSection col-span-1 min-[1023px]:col-span-5 p-4 min-[1023px]:p-6">

            {leftSections.map((section: any, sectionIndex: number) => (
              <div key={section.id} className={`NewSolutionSection ${sectionIndex > 0 ? 'mt-6' : ''}`}>
                {/* Section Header */}
                <p className="NewSolutionSectionHeader">
                  {section.label}
                </p>

                {/* Section Items */}
                <div className="NewSolutionSectionList">
                  {section.childItems?.nodes?.map((item: any) => (
                    <Link
                      key={item.id}
                      href={item.url || '#'}
                      onClick={onLinkClick}
                      className="NewSolutionItem NewSolutionItemWithDesc"
                    >
                      {/* Icon */}
                      <div className="NewSolutionItemIcon">
                        {item.menuACF?.icon?.node?.sourceUrl ? (
                          <img
                            src={item.menuACF.icon.node.sourceUrl}
                            alt={item.menuACF.icon.node.altText || item.label}
                            className="w-6 h-6 object-contain"
                          />
                        ) : (
                          <div className="w-6 h-6 bg-gray-100 rounded"></div>
                        )}
                      </div>

                      {/* Content */}
                      <div className="NewSolutionItemContent">
                        <p className="NewSolutionItemTitle">{item.label}</p>
                        {item.menuACF?.subTitle && (
                          <p className="NewSolutionItemDesc">{item.menuACF.subTitle}</p>
                        )}
                      </div>

                      {/* Arrow */}
                      <div className="NewSolutionItemArrow">
                        <svg width="29" height="24" viewBox="0 0 29 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <g clip-path="url(#clip0_1390_12139)">
                          <path d="M8.74756 11.6277H19.1959" stroke="black" stroke-width="1.16425" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M14.7183 16.1055L19.1961 11.6277" stroke="black" stroke-width="1.16425" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M14.7183 7.14978L19.1961 11.6277" stroke="black" stroke-width="1.16425" stroke-linecap="round" stroke-linejoin="round"/>
                          </g>
                          <defs>
                          <clipPath id="clip0_1390_12139">
                          <rect width="28.5852" height="23.6138" rx="3" fill="white"/>
                          </clipPath>
                          </defs>
                          </svg>

                      </div>
                    </Link>
                  ))}
                </div>
              </div>
            ))}

          </div>

          {/* Right Section - BY INDUSTRY */}
          <div className="NewSolutionRightSection col-span-1 min-[1023px]:col-span-4 p-4 min-[1023px]:p-6">

            {rightSection && (
              <div className="NewSolutionSection">
                {/* Section Header */}
                <p className="NewSolutionSectionHeader">
                  {rightSection.label}
                </p>

                {/* Section Items - Filter out explore-more-link */}
                <div className="NewSolutionSectionList">
                  {rightSection.childItems?.nodes
                    ?.filter((item: any) => !item.cssClasses?.includes('explore-more-link'))
                    ?.map((item: any) => (
                    <Link
                      key={item.id}
                      href={item.url || '#'}
                      onClick={onLinkClick}
                      className="NewSolutionItem NewSolutionItemSimple"
                    >
                      {/* Icon */}
                      <div className="NewSolutionItemIcon">
                        {item.menuACF?.icon?.node?.sourceUrl ? (
                          <img
                            src={item.menuACF.icon.node.sourceUrl}
                            alt={item.menuACF.icon.node.altText || item.label}
                            className="w-5 h-5 object-contain"
                          />
                        ) : (
                          <div className="w-5 h-5 bg-gray-100 rounded"></div>
                        )}
                      </div>

                      {/* Title */}
                      <p className="NewSolutionItemTitle">{item.label}</p>

                      {/* Arrow */}
                      <div className="NewSolutionItemArrow">
                        <svg width="29" height="24" viewBox="0 0 29 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <g clipPath="url(#clip0_1390_12139)">
                          <path d="M8.74756 11.6277H19.1959" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                          <path d="M14.7183 16.1055L19.1961 11.6277" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                          <path d="M14.7183 7.14978L19.1961 11.6277" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                          </g>
                          <defs>
                          <clipPath id="clip0_1390_12139">
                          <rect width="28.5852" height="23.6138" rx="3" fill="white"/>
                          </clipPath>
                          </defs>
                          </svg>

                      </div>
                    </Link>
                  ))}
                </div>

                {/* Explore More Link - Find item with explore-more-link class */}
                {rightSection.childItems?.nodes?.find((item: any) => item.cssClasses?.includes('explore-more-link')) && (
                  <div className="NewSolutionExploreMore">
                    <Link
                      href={rightSection.childItems.nodes.find((item: any) => item.cssClasses?.includes('explore-more-link'))?.url || '#'}
                      onClick={onLinkClick}
                      className="NewSolutionExploreMoreLink"
                    >
                      {rightSection.childItems.nodes.find((item: any) => item.cssClasses?.includes('explore-more-link'))?.label || 'Explore More'}
                    </Link>
                  </div>
                )}
              </div>
            )}

          </div>

          {/* Sidebar Section - Using Global Classes */}
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
