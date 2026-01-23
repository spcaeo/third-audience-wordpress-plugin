"use client";
import React, { useState, useEffect } from "react";
import Link from "next/link";
import Image from "next/image";

export const SolutionMenuLayout = ({
  menu,
  onLinkClick,
  currentPath,
  isOpen,
}: {
  menu: any;
  onLinkClick: () => void;
  currentPath: string;
  isOpen: boolean;
}) => {
  const [activeIndex, setActiveIndex] = useState<number>(0);
  const [isLargeScreen, setIsLargeScreen] = useState<boolean | null>(null);
  

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

  const handleTabClick = (index: number) => {
    setActiveIndex(index);
  };

  // Filter menu items into tabs (first level items)
  const tabs = menu.filter((item: any) => item.parentId === null || !item.parentId);
  
  // Get the active tab's children
  const activeTab = tabs[activeIndex];
  const activeTabChildren = activeTab?.childItems?.nodes || [];
  
  // Separate content items from AI features
  const contentItems = activeTabChildren.filter(
    (item: any) => !item.cssClasses?.includes('ai-powered-feature')
  );
  
  // Get AI features (items with ai-powered-feature class)
  const aiFeatures = activeTabChildren.filter(
    (item: any) => item.cssClasses?.includes('ai-powered-feature')
  );

  return (
    <div className="pt-0 xl:pt-3 first-child-menu SolutionMenuWrapper">
      <style jsx>{`
        .sf-pro-font {
          font-family: SFPRO, sans-serif;
        }
        .SolutionSubMenu {
          width: 100%;
        }
          .SolutionUseCaseIcon img {
    margin-top: 5px;
}
        .main-menu a {
    border-bottom: 1px;
    border-color: rgb(229 231 235 / var(--tw-border-opacity));
}
        @media (min-width: 1023px) {
          .SolutionSubMenu {
            width: min(calc(100vw - 40px), 1200px);
          }
        }
        @media (min-width: 1320px) {
          .SolutionSubMenu {
            width: min(calc(100vw - 60px), 1200px);
          }
        }
        @media (min-width: 1440px) {
          .SolutionSubMenu {
            width: min(calc(100vw - 80px), 1200px);
          }
        }
        
        // .SolutionMenuBox {
        //   border-bottom: 1px solid #e5e7eb !important;
        // }
        .SolutionLink {
          border-bottom: 1px solid #e5e7eb !important;
        }
        .SolutionAiFeaturesButton:hover {
          background: linear-gradient(215deg, #BD5DD0 0%, #9333EA 100%) !important;
          color: white;
          transition: all 0.3s ease-in-out;
        }
      `}</style>
      
      <div className={`SolutionSubMenu sub-menu relative min-[1023px]:fixed min-[1023px]:left-1/2 min-[1023px]:-translate-x-1/2 shadow-[0px_20px_25px_-5px_rgba(0,0,0,0.1),0px_10px_10px_-5px_rgba(0,0,0,0.04)] rounded-none min-[1023px]:rounded-2xl border border-gray-200 bg-white transition-all duration-300 ease-in-out ${isOpen ? 'block opacity-100 translate-y-0' : 'hidden opacity-0 translate-y-2'}`}>
        <div className="SolutionMenuGrid grid grid-cols-1 min-[1023px]:grid-cols-12 gap-0 min-[1023px]:gap-4">
          
          {/* Left Column - Tabs */}
          <div className="SolutionTabBoxColumn p-4 min-[1023px]:p-[15px] col-span-1 min-[1023px]:col-span-3 border-b min-[1023px]:border-b-0 border-gray-200">
            <div className="SolutionTabBoxContainer flex flex-col space-y-2 mb-0">
              {tabs.map((tab: any, index: number) => (
                <button
                  key={tab.id}
                  onClick={() => handleTabClick(index)}
                  className={`SolutionTabBox flex items-center justify-between text-[14px] font-semibold sf-pro-font w-full px-3 py-2 rounded-lg transition-all duration-200 ${
                    activeIndex === index
                      ? 'bg-gray-100 text-[#232529]'
                      : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                  }`}
                >
                  <span className="flex items-center">
                    {/* Display icon if available */}
                    {tab.menuACF?.icon?.node?.sourceUrl && (
                      <img 
                        src={tab.menuACF.icon.node.sourceUrl} 
                        alt={tab.menuACF.icon.node.altText || tab.label}
                        className="SolutionTabIcon mr-2 h-5 w-5"
                      />
                    )}
                    {tab.label}
                  </span>
                  <svg className="ml-auto h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
              ))}
            </div>
          </div>

          {/* Middle Column - Dynamic Content with Different Layouts */}
          <div className="SolutionMenuBoxColumn col-span-1 min-[1023px]:col-span-6">
            {(() => {
              const tabLabel = activeTab?.label?.toLowerCase() || '';
              
              // Layout 1: By Team Size - 3 column cards with centered content
              if (tabLabel.includes('team') || tabLabel.includes('size')) {
                return (
                  <div className="SolutionMenuBoxContainer md:px-0 px-4 pt-4 pb-4 grid grid-cols-1 sm:grid-cols-3 min-[1023px]:grid-cols-3 gap-x-4 min-[1023px]:gap-x-4 gap-y-4 min-[1023px]:gap-y-6">
                    {contentItems.map((item: any) => {
                      const isCategory = item.childItems?.nodes?.length > 0;
                      const categoryItems = isCategory ? item.childItems.nodes : [item];
                      
                      return categoryItems.map((subItem: any) => {
                        // Get menuDesc field value
                        let menuDescValue = '';
                        if (subItem.menuACF) {
                          // Try direct field names first
                          menuDescValue = subItem.menuACF.menuDesc || 
                                         subItem.menuACF.menuDesc || 
                                         subItem.menuACF['menu-description'] || 
                                         subItem.menuACF.menu_description || 
                                         '';
                          
                          // If not found, search dynamically
                          if (!menuDescValue) {
                            const acfFields = Object.keys(subItem.menuACF);
                            const menuDescField = acfFields.find(key => 
                              key.toLowerCase().includes('menuDesc') || 
                              key.toLowerCase().includes('description')
                            );
                            
                            if (menuDescField) {
                              menuDescValue = subItem.menuACF[menuDescField];
                            }
                          }
                        }
                        
                        return (
                          <Link
                            key={subItem.id}
                            href={subItem.url || '#'}
                            onClick={onLinkClick}
                            className="SolutionMenuBox text-center p-3 border border-gray-200 border-b border-gray-200 rounded-xl hover:no-underline block"
                          >
                          <div className="SolutionBoxIcon pb-3 flex justify-center">
                            {subItem.menuACF?.icon?.node?.sourceUrl ? (
                              <img 
                                src={subItem.menuACF.icon.node.sourceUrl} 
                                alt={subItem.menuACF.icon.node.altText || subItem.label}
                                className="h-8 w-8"
                              />
                            ) : (
                              <svg className="h-8 w-8" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="16" cy="16" r="15" stroke="#232529" strokeWidth="1" />
                                <path d="M16 8v8m0 0v8m0-8h8m-8 0H8" stroke="#232529" strokeWidth="1" strokeLinecap="round" />
                              </svg>
                            )}
                          </div>
                          <p className="SolutionBoxHeader sf-pro-font text-[16px] font-medium text-gray-900 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>
                            {subItem.label}
                          </p>
                          {subItem.menuACF?.subTitle && (
                            <p className="SolutionBoxSubtext sf-pro-font text-[14px] text-black-500 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>
                              {subItem.menuACF.subTitle}
                            </p>
                          )}
                          {/* Always show paragraph if menuDescValue exists */}
                          {menuDescValue && (
                            <p className="SolutionBoxDescription sf-pro-font text-[14px] pt-2 pb-0 text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '22px' }}>
                              {menuDescValue}
                            </p>
                          )}
                          {/* Fallback to other descriptions if no menuDescValue */}
                          {!menuDescValue && (subItem.description || subItem.menuACF?.description) && (
                            <p className="SolutionBoxDescription sf-pro-font text-[14px] pt-2 pb-0 text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '22px', paddingBottom: '0' }}>
                              {subItem.menuACF?.description || subItem.description}
                            </p>
                          )}
                        </Link>
                        );
                      });
                    })}
                  </div>
                );
              }
              
              // Layout 2: By Use Case - 2x3 grid with icons and descriptions
              else if (tabLabel.includes('use') || tabLabel.includes('case')) {
                return (
                  <div className="SolutionMenuBoxContainer md:px-0 px-4 pt-4 pb-4 grid grid-cols-1 sm:grid-cols-2 min-[1023px]:grid-cols-2 gap-x-6 min-[1023px]:gap-x-4 gap-y-4 min-[1023px]:gap-y-4">
                    {contentItems.map((item: any) => {
                      const isCategory = item.childItems?.nodes?.length > 0;
                      const categoryItems = isCategory ? item.childItems.nodes : [item];
                      
                      return categoryItems.map((subItem: any) => {
                        // Get menuDesc field value
                        let menuDescValue = '';
                        if (subItem.menuACF) {
                          // Try direct field names first
                          menuDescValue = subItem.menuACF.menuDesc || 
                                         subItem.menuACF.menuDesc || 
                                         subItem.menuACF['menu-description'] || 
                                         subItem.menuACF.menu_description || 
                                         '';
                          
                          // If not found, search dynamically
                          if (!menuDescValue) {
                            const acfFields = Object.keys(subItem.menuACF);
                            const menuDescField = acfFields.find(key => 
                              key.toLowerCase().includes('menuDesc') || 
                              key.toLowerCase().includes('description')
                            );
                            
                            if (menuDescField) {
                              menuDescValue = subItem.menuACF[menuDescField];
                            }
                          }
                        }
                        
                        return (
                          <Link
                            key={subItem.id}
                            href={subItem.url || '#'}
                            onClick={onLinkClick}
                            className="SolutionLink flex gap-2 align-center border border-gray-200 border-b border-gray-200 rounded-xl p-2 block text-[14px] font-medium text-[#232529] hover:text-[#7824B1] mb-1"
                          >
                          <div className="SolutionUseCaseIcon flex-shrink-0">
                            {subItem.menuACF?.icon?.node?.sourceUrl ? (
                              <img 
                                src={subItem.menuACF.icon.node.sourceUrl} 
                                alt={subItem.menuACF.icon.node.altText || subItem.label}
                                className="h-4 w-4"
                              />
                            ) : (
                              <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="3" width="18" height="18" rx="2" stroke="#232529" strokeWidth="1.5"/>
                              </svg>
                            )}
                          </div>
                          <div className="SolutionUseCaseContent">
                            <p className="SolutionUseCaseTitle sf-pro-font text-[15px] font-medium text-gray-900 mb-1 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>
                              {subItem.label}
                            </p>
                            {/* Always show paragraph if menuDescValue exists */}
                            {menuDescValue && (
                              <p className="text-[14px] text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '18px', paddingBottom: '0' }}>
                                {menuDescValue}
                              </p>
                            )}
                            {/* Fallback to other descriptions if no menuDescValue */}
                            {!menuDescValue && (subItem.menuACF?.description || subItem.menuACF?.subTitle || subItem.description) && (
                              <p className="SolutionUseCaseDesc sf-pro-font text-[13px] text-gray-600 leading-[18px]" style={{ fontFamily: 'SFPRO, sans-serif' }}>
                                {subItem.menuACF?.description || subItem.menuACF?.subTitle || subItem.description}
                              </p>
                            )}
                          </div>
                        </Link>
                        );
                      });
                    })}
                  </div>
                );
              }
              
              // Layout 3: By Industry - Simple list layout
              else if (tabLabel.includes('industry')) {
                return (
                  <div className="SolutionMenuBoxContainer SolutionMenuBoxIndustry md:px-0 px-4 pt-4 pb-4 grid grid-cols-1 sm:grid-cols-2 min-[1023px]:grid-cols-2 gap-x-4 min-[1023px]:gap-x-8 gap-y-4 min-[1023px]:gap-y-6">
                    {/* Left Column */}
                    <div className="SolutionMenuBox">
                      <ul className="SolutionBoxList space-y-2">
                        {contentItems.slice(0, Math.ceil(contentItems.length / 2)).map((item: any) => {
                          const isCategory = item.childItems?.nodes?.length > 0;
                          const categoryItems = isCategory ? item.childItems.nodes : [item];
                          
                          return categoryItems.map((subItem: any) => (
                            <li key={subItem.id} className="SolutionBoxItem flex items-center">
                              {/* Show icon if available, otherwise show default arrow */}
                              {subItem.menuACF?.icon?.node?.sourceUrl ? (
                                <img 
                                  src={subItem.menuACF.icon.node.sourceUrl} 
                                  alt={subItem.menuACF.icon.node.altText || subItem.label}
                                  className="SolutionBoxIcon mr-3 h-4 w-4"
                                />
                              ) : (
                                <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                              )}
                              <Link 
                                href={subItem.url || '#'} 
                                onClick={onLinkClick} 
                                className="SolutionLink text-[14px] hover:text-[#7824B1] font-medium text-[#232529] hover:text-[#232529]" 
                                style={{ fontFamily: 'SFPRO, sans-serif' }}
                              >
                                {subItem.label}
                              </Link>
                            </li>
                          ));
                        })}
                      </ul>
                    </div>

                    {/* Right Column */}
                    <div className="SolutionMenuBox">
                      <ul className="SolutionBoxList space-y-2">
                        {contentItems.slice(Math.ceil(contentItems.length / 2)).map((item: any) => {
                          const isCategory = item.childItems?.nodes?.length > 0;
                          const categoryItems = isCategory ? item.childItems.nodes : [item];
                          
                          return categoryItems.map((subItem: any) => (
                            <li key={subItem.id} className="SolutionBoxItem flex items-center">
                              {/* Show icon if available, otherwise show default arrow */}
                              {subItem.menuACF?.icon?.node?.sourceUrl ? (
                                <img 
                                  src={subItem.menuACF.icon.node.sourceUrl} 
                                  alt={subItem.menuACF.icon.node.altText || subItem.label}
                                  className="SolutionBoxIcon mr-3 h-4 w-4 mt-0.5"
                                />
                              ) : (
                                <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                              )}
                              <Link 
                                href={subItem.url || '#'} 
                                onClick={onLinkClick} 
                                className="SolutionLink text-[14px] hover:text-[#7824B1] font-medium text-[#232529]" 
                                style={{ fontFamily: 'SFPRO, sans-serif' }}
                              >
                                {subItem.label}
                              </Link>
                            </li>
                          ));
                        })}
                      </ul>
                    </div>
                  </div>
                );
              }
              
              // Default layout (fallback)
              else {
                return (
                  <div className="SolutionMenuBoxContainer pt-4 pb-4 grid grid-cols-1 sm:grid-cols-3 min-[1023px]:grid-cols-3 gap-x-4 min-[1023px]:gap-x-4 gap-y-4 min-[1023px]:gap-y-6">
                    {contentItems.map((item: any) => (
                      <Link
                        key={item.id}
                        href={item.url || '#'}
                        onClick={onLinkClick}
                        className="SolutionMenuBox text-center p-3 border border-gray-200 rounded-xl hover:no-underline block"
                      >
                        <p className="sf-pro-font text-[16px] font-medium text-gray-900 pb-0">
                          {item.label}
                        </p>
                      </Link>
                    ))}
                  </div>
                );
              }
            })()}
          </div>

          {/* Right Column - AI Features */}
          <div className="SolutionAiFeaturesColumn min-[1023px]:rounded-2xl  hidden min-[1023px]:block p-4 min-[1023px]:p-[15px] col-span-1 min-[1023px]:col-span-3 border-l border-dotted border-gray-300" style={{ background: 'linear-gradient(43deg, #ffffff 70%, #BD5DD0 138%)' }}>
            <div className="SolutionAiFeaturesBox relative rounded-xl p-6 border-l-2 border-l-dotted border-l-purple-200 border-t border-r border-b border-purple-100 flex flex-col justify-between h-full" style={{ background: 'linear-gradient(215deg, #ffffff 80%, #BD5DD0 149%)' }}>
              <div className="SolutionAiFeaturesTop">
                <p className="SolutionAiFeaturesHeader sf-pro-font text-[14px] font-semibold text-[#000000] mb-4 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>Customer Stories</p>
                <div className="SolutionStoryContent mb-4">
                  <p className="SolutionStoryText sf-pro-font text-[14px] text-gray-700 italic mb-4" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '20px' }}>
                    FieldCamp transformed how we schedule jobs—now everything runs smoother and faster!
                  </p>
                  
                    <div className="SolutionStoryAuthor items-center">
                      <div className="SolutionAuthorAvatar w-12 h-12 rounded-full bg-gray-300 items-center justify-center overflow-hidden">
                        <img 
                          src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/testimonial-elips.png" 
                          alt="James W." 
                          className="w-full h-full object-cover"
                        />
                      </div>
                      <div className="SolutionAuthorInfo">
                        <p className="SolutionAuthorName sf-pro-font text-[14px] pb-0 font-medium text-gray-900 mb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>James W.,</p>
                        <p className="SolutionAuthorTitle sf-pro-font leading-[1.2] text-[13px] text-gray-600 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>Operations Manager at HomeFix Pros</p>
                      </div>
                    </div>
                  
                </div>
              </div>

              {/* <button className="SolutionAiFeaturesButton sf-pro-font w-full py-2.5 px-4 rounded-lg transition-all duration-200 text-[14px] font-medium text-gray-700" style={{ backgroundColor: '#EAEDFB', fontFamily: 'SFPRO, sans-serif' }}>
                See More
              </button> */}
            </div>
          </div>

        </div>
        <div className="jsx-537a3625e90ebbc9 BottomLinksContainer hidden min-[1023px]:flex p-4 border-t border-gray-200 justify-end space-x-8">
            <div className="flex justify-between items-center">
              <div className="flex space-x-6">
                {/* <a href="#" onClick={onLinkClick} className="text-sm text-gray-600 hover:text-gray-900 flex items-center">
                    <img 
                        src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/contact-sales-ic.svg" 
                        alt="Contact Sales" 
                        className="mr-2"
                      />
                  Contact sales
                </a> */}
                <a href="https://www.youtube.com/watch?v=qDIE6DaIAWU" target="_blank" rel="noopener noreferrer" onClick={onLinkClick} className="text-sm text-gray-600 hover:text-gray-900 flex items-center">
                      <img
                        src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/watch-demo-ic.svg"
                        alt="Watch Demo"
                        className="mr-2"
                      />
                  Watch demo
                </a>
                <Link href="/mobile-app-download/" onClick={onLinkClick} className="text-sm text-gray-600 hover:text-gray-900 flex items-center">
                       <img
                        src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/donwload-ic.svg"
                        alt="Download Apps"
                        className="mr-2"
                      />
                  Download apps
                </Link>
              </div>
            </div>
          </div>
      </div>
    </div>
  );
};