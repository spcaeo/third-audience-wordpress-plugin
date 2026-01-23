"use client";
import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";
import { ColumnStructure } from "./ColumnStructure";

export const TabsMenuNew = ({
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

  const isActive = (url: string) => {
    if (url === "#") return false;
    if (typeof window === "undefined") return false;
    try {
      const menuItemPath = new URL(url, window.location.origin).pathname;
      return menuItemPath === currentPath;
    } catch (error) {
      console.error("Error parsing URL:", error);
      return false;
    }
  };

  // Filter menu items into tabs (first level items)
  const tabs = menu.filter((item: any) => item.parentId === null || !item.parentId);
  
  // Get the active tab's children
  const activeTab = tabs[activeIndex];
  const activeTabChildren = activeTab?.childItems?.nodes || [];
  
  // Separate capability menu items (only from backend)
  const capabilityMenuItems = activeTabChildren;
  
  // Static AI-Powered Features (hardcoded for now)
  const staticAiFeatures = [
    {
      id: 'ai-scheduling',
      label: 'AI Scheduling',
      url: '/features/ai-scheduling/',
      icon: null
    },
    {
      id: 'ai-workflow',
      label: 'AI Workflow Builder',
      url: '/features/ai-workflow-builder/',
      icon: null
    },
    {
      id: 'ai-booking-bot',
      label: 'Conversational Booking Bot',
      url: '/features/conversational-booking-bot/',
      icon: null
    },
    {
      id: 'ai-command-center',
      label: 'AI Command Center (Handy AI)',
      url: '/features/ai-command-center/',
      icon: null
    }
  ];
  
  // Static AI Features title
  const aiFeatureTitle = "AI-Powered Features";

  return (
    <div className="pt-0 xl:pt-3 first-child-menu TabsMenuNewWrapper">
      <style jsx>{`
        .TabsMenuNewSubmenu {
          width: 100%;
        }
        @media (min-width: 1023px) {
          .TabsMenuNewSubmenu {
            width: min(calc(100vw - 40px), 1200px);
          }
        }
        @media (min-width: 1320px) {
          .TabsMenuNewSubmenu {
            width: min(calc(100vw - 60px), 1200px);
          }
        }
        @media (min-width: 1440px) {
          .TabsMenuNewSubmenu {
            width: min(calc(100vw - 80px), 1200px);
          }
        }
        .AiFeaturesButton:hover {
          background: linear-gradient(215deg, #BD5DD0 0%, #9333EA 100%) !important;
          color: white;
          transition: all 0.3s ease-in-out;
        }
      `}</style>
      
      <div className={`TabsMenuNewSubmenu sub-menu relative min-[1023px]:fixed min-[1023px]:left-1/2 min-[1023px]:-translate-x-1/2 shadow-[0px_20px_25px_-5px_rgba(0,0,0,0.1),0px_10px_10px_-5px_rgba(0,0,0,0.04)] rounded-none min-[1023px]:rounded-2xl border border-gray-200 bg-white transition-all duration-300 ease-in-out ${isOpen ? 'block opacity-100 translate-y-0' : 'hidden opacity-0 translate-y-2'}`}>
        <div className="grid grid-cols-1 min-[1023px]:grid-cols-12 gap-0 min-[1023px]:gap-4">
          
          {/* First Column - Tabs */}
          <div className="p-4 min-[1023px]:p-[15px] col-span-1 min-[1023px]:col-span-3 border-b min-[1023px]:border-b-0 border-gray-200">
            <ColumnStructure
              items={tabs}
              onLinkClick={onLinkClick}
              currentPath={currentPath}
              columnType="tabs"
              activeIndex={activeIndex}
              onTabClick={handleTabClick}
            />
          </div>

          {/* Second Column - Dynamic Content based on active tab */}
          <div className="p-4 min-[1023px]:p-[15px] col-span-1 min-[1023px]:col-span-6 border-b min-[1023px]:border-b-0 border-gray-200">
            {capabilityMenuItems.length > 0 && (() => {
              // Determine column type based on active tab name
              const activeTabName = activeTab?.label?.toLowerCase() || '';
              let columnType: 'capabilities' | 'menu-items' = 'capabilities';
              
              // If tab is workflow or integrations, show menu items directly without headings
              if (activeTabName.includes('workflow') || activeTabName.includes('integration')) {
                columnType = 'menu-items';
              }
              
              return (
                <ColumnStructure
                  items={capabilityMenuItems}
                  onLinkClick={onLinkClick}
                  currentPath={currentPath}
                  columnType={columnType}
                />
              );
            })()}
          </div>

          {/* Third Column - AI-Powered Features (Static for now) */}
          <div className="col-span-1 min-[1023px]:col-span-3 border-l border-dotted border-gray-300">
            <ColumnStructure
              items={staticAiFeatures}
              onLinkClick={onLinkClick}
              currentPath={currentPath}
              columnType="ai-features"
              aiFeatureTitle={aiFeatureTitle}
            />
          </div>
        </div>
        
        {/* Bottom Links Container - Static, only shows if menu has backend items */}
        {menu && menu.length > 0 && (
          <div className="jsx-537a3625e90ebbc9 BottomLinksContainer hidden min-[1023px]:flex p-4 border-t border-gray-200 justify-end space-x-8">
            <div className="flex justify-between items-center">
              <div className="flex space-x-6">
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
        )}
      </div>
    </div>
  );
};