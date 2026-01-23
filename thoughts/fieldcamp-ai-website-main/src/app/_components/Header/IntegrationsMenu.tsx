"use client";
import React, { useEffect, useState } from "react";
import Link from "next/link";

interface IntegrationsMenuProps {
  onLinkClick: () => void;
  menu?: any;
  currentPath?: string;
}

export const IntegrationsMenu = ({ onLinkClick, menu, currentPath }: IntegrationsMenuProps) => {
  const [isClient, setIsClient] = useState(false);

  useEffect(() => {
    setIsClient(true);
  }, []);

  const isActive = (url: string) => {
    if (!isClient || url === "#" || !currentPath) return false;
    try {
      const menuItemPath = new URL(url, window.location.origin).pathname;
      return menuItemPath === currentPath;
    } catch (error) {
      console.error("Error parsing URL:", error);
      return false;
    }
  };

  // If menu data is provided, use dynamic rendering
  if (menu && menu.length > 0) {
    // Split items into two columns automatically
    const midpoint = Math.ceil(menu.length / 2);
    const column1Items = menu.slice(0, midpoint);
    const column2Items = menu.slice(midpoint);

    return (
      <div className="MenuBoxContainer MenuBoxIntegrations grid grid-cols-1 min-[1023px]:grid-cols-2 gap-x-4 min-[1023px]:gap-x-12 gap-y-4 min-[1023px]:gap-y-6">
        {/* Column 1 */}
        <div className="MenuBox MenuBoxIntegrationsCol1">
          <ul className="MenuBoxList space-y-2">
            {column1Items.map((item: any) => (
              <li key={item.id} className="MenuBoxItem IntegrationItem flex items-center">
                {item.menuACF?.icon?.node?.sourceUrl ? (
                  <img 
                    src={item.menuACF.icon.node.sourceUrl} 
                    alt={item.menuACF.icon.node.altText || item.label}
                    className="w-6 h-6 rounded-full mr-3"
                  />
                ) : (
                  <div className="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                    <svg className="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                  </div>
                )}
                <Link 
                  href={item.url} 
                  onClick={onLinkClick} 
                  className={`MenuLink text-[13px] text-[#232529] hover:text-[#7824B1] ${isActive(item.url) ? 'current-menu-item' : ''}`}
                  style={{ fontFamily: 'SFPRO, sans-serif' }}
                >
                  {item.label}
                  {item.menuACF?.subTitle && (
                    <span className="block text-xs text-[#667085] pt-1">
                      {item.menuACF.subTitle}
                    </span>
                  )}
                </Link>
              </li>
            ))}
          </ul>
        </div>

        {/* Column 2 */}
        {column2Items.length > 0 && (
          <div className="MenuBox MenuBoxIntegrationsCol2">
            <ul className="MenuBoxList space-y-2">
              {column2Items.map((item: any) => (
                <li key={item.id} className="MenuBoxItem IntegrationItem flex items-center">
                  {item.menuACF?.icon?.node?.sourceUrl ? (
                    <img 
                      src={item.menuACF.icon.node.sourceUrl} 
                      alt={item.menuACF.icon.node.altText || item.label}
                      className="w-6 h-6 rounded-full mr-3"
                    />
                  ) : (
                    <div className="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                      <svg className="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                      </svg>
                    </div>
                  )}
                  <Link 
                    href={item.url} 
                    onClick={onLinkClick} 
                    className={`MenuLink text-[13px] text-[#232529] hover:text-[#7824B1] ${isActive(item.url) ? 'current-menu-item' : ''}`}
                    style={{ fontFamily: 'SFPRO, sans-serif' }}
                  >
                    {item.label}
                    {item.menuACF?.subTitle && (
                      <span className="block text-xs text-[#667085] pt-1">
                        {item.menuACF.subTitle}
                      </span>
                    )}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        )}
      </div>
    );
  }

  // If no menu data is provided, don't render anything
  return null;
};