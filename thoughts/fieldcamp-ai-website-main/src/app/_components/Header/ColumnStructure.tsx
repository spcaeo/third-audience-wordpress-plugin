"use client";
import Image from "next/image";
import Link from "next/link";

interface ColumnStructureProps {
  items: any[];
  onLinkClick: () => void;
  currentPath: string;
  columnType: 'tabs' | 'capabilities' | 'ai-features' | 'menu-items';
  activeIndex?: number;
  onTabClick?: (index: number) => void;
  aiFeatureTitle?: string;
}

export const ColumnStructure = ({
  items,
  onLinkClick,
  currentPath,
  columnType,
  activeIndex,
  onTabClick,
  aiFeatureTitle = "AI-Powered Features",
}: ColumnStructureProps) => {
  const isActive = (url: string) => {
    if (url === "#" || !url) return false;
    if (typeof window === "undefined") return false;
    try {
      const menuItemPath = new URL(url, window.location.origin).pathname;
      return menuItemPath === currentPath;
    } catch (error) {
      console.error("Error parsing URL:", error);
      return false;
    }
  };

  if (columnType === 'tabs') {
    return (
      <div className="TabBoxContainer flex flex-col space-y-2 mb-0">
        {items.map((tab: any, index: number) => (
          <button
            key={tab.id}
            onClick={() => onTabClick?.(index)}
            className={`TabBox flex items-center justify-between text-[14px] font-semibold sf-pro-font w-full px-3 py-2 rounded-lg transition-all duration-200 ${
              activeIndex === index
                ? 'bg-gray-100 text-[#232529]'
                : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
            }`}
          >
            <span className="flex items-center">
              {tab.menuACF?.icon?.node?.sourceUrl && (
                <Image 
                  src={tab.menuACF.icon.node.sourceUrl} 
                  alt={tab.menuACF.icon.node.altText || tab.label} 
                  width={20} 
                  height={20} 
                  className="mr-2"
                />
              )}
              {tab.label}
            </span>
            <svg className="ml-auto h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
        ))}
      </div>
    );
  }

  if (columnType === 'capabilities') {
    return (
      <div className="MenuBoxContainer MenuBoxCapabilities grid grid-cols-1 sm:grid-cols-2 min-[1023px]:grid-cols-2 gap-x-4 min-[1023px]:gap-x-12 gap-y-4 min-[1023px]:gap-y-6">
        {items.map((category: any) => (
          <div key={category.id} className="MenuBox">
            {/* Category Heading */}
            <p className="MenuBoxHeader sf-pro-font text-[14px] font-semibold text-[#000000] mb-4 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>
              {category.label}
            </p>
            
            {/* Category Links */}
            {category.childItems?.nodes?.length > 0 && (
              <ul className="MenuBoxList space-y-2">
                {category.childItems.nodes.map((link: any) => (
                  <li key={link.id} className="MenuBoxItem flex items-center">
                    {/* Dynamic Icon - Use uploaded icon if available, otherwise show static icon */}
                    {link.menuACF?.icon?.node?.sourceUrl ? (
                      <Image 
                        src={link.menuACF.icon.node.sourceUrl} 
                        alt={link.menuACF.icon.node.altText || link.label} 
                        width={16} 
                        height={16} 
                        className="MenuBoxIcon mr-2"
                      />
                    ) : (
                      <svg className="MenuBoxIcon mr-2 h-4 w-4 text-[#232529]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                      </svg>
                    )}
                    <Link
                      href={link.url || "#"}
                      onClick={onLinkClick}
                      className="MenuLink text-[14px] text-[#232529] hover:text-[#7824B1]"
                      style={{ fontFamily: 'SFPRO, sans-serif' }}
                    >
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            )}
          </div>
        ))}
      </div>
    );
  }

  if (columnType === 'ai-features') {
    return (
      <div className="AiFeaturesColumn justify-between h-full min-[1023px]:rounded-2xl  hidden min-[1023px]:block p-4 min-[1023px]:p-[15px] col-span-1 min-[1023px]:col-span-3" style={{ background: 'linear-gradient(43deg, #ffffff 70%, #BD5DD0 138%)' }}>
            <div className="AiFeaturesBox relative rounded-xl p-3 border-l-2 border-l-dotted border-l-purple-200 border-t border-r border-b border-purple-100 flex flex-col justify-between h-full" style={{ background: 'linear-gradient(215deg, #ffffff 80%, #BD5DD0 149%)' }}>
              <div className="AiFeaturesTop">
                <p className="AiFeaturesHeader sf-pro-font text-[14px] font-semibold text-[#000000] mb-4 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>AI-Powered Features</p>
                <ul className="AiFeaturesList space-y-2">
                <li className="AiFeaturesItem flex items-center">
                    <div className="AiFeaturesIcon w-5 h-5 rounded bg-purple-100 flex items-center justify-center mr-3">
                      <svg className="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                      </svg>
                    </div>
                    <Link href="/ai-receptionist/" onClick={onLinkClick} className="AiFeaturesText sf-pro-font text-[13px] text-gray-800 hover:text-[#7824B1]">AI Receptionist</Link>
                  </li>
                  <li className="AiFeaturesItem flex items-center">
                    <div className="AiFeaturesIcon w-5 h-5 rounded bg-purple-100 flex items-center justify-center mr-3">
                      <svg className="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                      </svg>
                    </div>
                    <Link href="/features/ai-workflow-builder/" onClick={onLinkClick} className="AiFeaturesText sf-pro-font text-[13px] text-gray-800 hover:text-[#7824B1]">AI Workflow Builder</Link>
                  </li>
                  <li className="AiFeaturesItem flex items-center">
                    <div className="AiFeaturesIcon w-5 h-5 rounded bg-purple-100 flex items-center justify-center mr-3">
                      <svg className="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                      </svg>
                    </div>
                    <Link href="/features/ai-command-center/" onClick={onLinkClick} className="AiFeaturesText sf-pro-font text-[13px] text-gray-800 hover:text-[#7824B1]">AI Command Center (Handy AI)</Link>
                  </li>
                  <li className="AiFeaturesItem flex items-center">
                    <div className="AiFeaturesIcon w-5 h-5 rounded bg-purple-100 flex items-center justify-center mr-3">
                      <svg className="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                    </div>
                    <Link href="/features/ai-dispatch-scheduling/" onClick={onLinkClick} className="AiFeaturesText sf-pro-font text-[13px] text-gray-800 hover:text-[#7824B1]">AI Scheduling</Link>
                  </li>
                </ul>
              </div>

              <Link href="/features/ai-dispatch-scheduling/" onClick={onLinkClick} className="AiFeaturesButton sf-pro-font mt-6 w-full py-2.5 px-4 rounded-lg transition-all duration-200 text-[14px] font-medium text-gray-700 block text-center" style={{ backgroundColor: '#f3d0eb52' }}>
                Explore Now
              </Link>
            </div>
          </div>
    );
  }

  if (columnType === 'menu-items') {
    // Split items into two columns for better layout
    const midpoint = Math.ceil(items.length / 2);
    const leftColumnItems = items.slice(0, midpoint);
    const rightColumnItems = items.slice(midpoint);

    return (
      <div className="MenuBoxContainer grid grid-cols-1 sm:grid-cols-2 min-[1023px]:grid-cols-2 gap-x-4 min-[1023px]:gap-x-12 gap-y-4 min-[1023px]:gap-y-6">
        {/* Left Column */}
        <div className="MenuBox">
          <ul className="MenuBoxList space-y-2">
            {leftColumnItems.map((link: any) => (
              <li key={link.id} className="MenuBoxItem flex items-center">
                {/* Dynamic Icon - Use uploaded icon if available, otherwise show static icon */}
                {link.menuACF?.icon?.node?.sourceUrl ? (
                  <Image 
                    src={link.menuACF.icon.node.sourceUrl} 
                    alt={link.menuACF.icon.node.altText || link.label} 
                    width={16} 
                    height={16} 
                    className="MenuBoxIcon mr-2"
                  />
                ) : (
                  <svg className="MenuBoxIcon mr-2 h-4 w-4 text-[#232529]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                )}
                <Link
                  href={link.url || "#"}
                  onClick={onLinkClick}
                  className="MenuLink text-[14px] text-[#232529] hover:text-[#7824B1]"
                  style={{ fontFamily: 'SFPRO, sans-serif' }}
                >
                  {link.label}
                </Link>
              </li>
            ))}
          </ul>
        </div>

        {/* Right Column */}
        <div className="MenuBox">
          <ul className="MenuBoxList space-y-2">
            {rightColumnItems.map((link: any) => (
              <li key={link.id} className="MenuBoxItem flex items-center">
                {/* Dynamic Icon - Use uploaded icon if available, otherwise show static icon */}
                {link.menuACF?.icon?.node?.sourceUrl ? (
                  <Image 
                    src={link.menuACF.icon.node.sourceUrl} 
                    alt={link.menuACF.icon.node.altText || link.label} 
                    width={16} 
                    height={16} 
                    className="MenuBoxIcon mr-2"
                  />
                ) : (
                  <svg className="MenuBoxIcon mr-2 h-4 w-4 text-[#232529]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                )}
                <Link
                  href={link.url || "#"}
                  onClick={onLinkClick}
                  className="MenuLink text-[14px] text-[#232529] hover:text-[#7824B1]"
                  style={{ fontFamily: 'SFPRO, sans-serif' }}
                >
                  {link.label}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      </div>
    );
  }

  return null;
};