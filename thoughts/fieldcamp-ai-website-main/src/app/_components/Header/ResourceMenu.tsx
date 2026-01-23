"use client";
import React from "react";
import Link from "next/link";
import Image from "next/image";
import { ColumnStructure } from "./ColumnStructure";

export const ResourceMenu = ({ 
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
  
  // Filter top-level menu items (these will be our three columns)
  const columns = menu.filter((item: any) => item.parentId === null || !item.parentId);

  return (
    <div className="pt-0 xl:pt-3 first-child-menu ResourceMenuWrapper">
      <style jsx>{`
        .sf-pro-font {
          font-family: SFPRO, sans-serif;
        }
        .ResourceSubMenu {
          width: 100%;
        }
        @media (min-width: 1023px) {
          .ResourceSubMenu {
            width: min(calc(100vw - 40px), 1200px);
          }
        }
        @media (min-width: 1320px) {
          .ResourceSubMenu {
            width: min(calc(100vw - 60px), 1200px);
          }
        }
        @media (min-width: 1440px) {
          .ResourceSubMenu {
            width: min(calc(100vw - 80px), 1200px);
          }
        }
        .ResourceStreamlineButton:hover {
          background: linear-gradient(215deg, #22D3EE 0%, #0891B2 100%) !important;
          color: white;
          transition: all 0.3s ease-in-out;
        }
      `}</style>
      
      <div className={`ResourceSubMenu sub-menu relative min-[1023px]:fixed min-[1023px]:left-1/2 min-[1023px]:-translate-x-1/2 shadow-[0px_20px_25px_-5px_rgba(0,0,0,0.1),0px_10px_10px_-5px_rgba(0,0,0,0.04)] rounded-none min-[1023px]:rounded-2xl border border-gray-200 bg-white transition-all duration-300 ease-in-out ${isOpen ? 'block opacity-100 translate-y-0' : 'hidden opacity-0 translate-y-2'}`}>
        <div className="ResourceMenuGrid grid grid-cols-1 min-[1023px]:grid-cols-12 gap-0 min-[1023px]:gap-4">
          
          {/* Render three columns dynamically */}
          {columns.slice(0, 3).map((column: any, columnIndex: number) => (
            <div key={column.id} className="p-4 min-[1023px]:p-[15px] col-span-1 min-[1023px]:col-span-3 border-b min-[1023px]:border-b-0 border-gray-200">
              {/* Column Heading */}
              <p className="MenuBoxHeader sf-pro-font text-[14px] font-semibold text-[#000000] mb-4 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>
                {column.label}
              </p>
              
              {/* Column Menu Items */}
              <div className="space-y-2">
                {(column.childItems?.nodes || []).map((item: any) => (
                  <Link
                    key={item.id}
                    href={item.url || '#'}
                    onClick={onLinkClick}
                    className="flex items-center text-[14px] text-[#232529] hover:text-gray-900"
                  >
                    {/* Dynamic Icon - Use uploaded icon if available, otherwise show arrow icon */}
                    {item.menuACF?.icon?.node?.sourceUrl ? (
                      <Image 
                        src={item.menuACF.icon.node.sourceUrl} 
                        alt={item.menuACF.icon.node.altText || item.label} 
                        width={16} 
                        height={16} 
                        className="MenuBoxIcon mr-3 mt-0.5"
                      />
                    ) : (
                      <svg className="MenuBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                      </svg>
                    )}
                    {item.label}
                  </Link>
                ))}
              </div>
            </div>
          ))}

          {/* Right Column - Promotional/CTA Section */}
          <div className="ResourceStreamlineColumn min-[1023px]:rounded-2xl hidden min-[1023px]:block p-4 min-[1023px]:p-[15px] col-span-1 min-[1023px]:col-span-3 border-l border-dotted border-gray-300" style={{ background: 'linear-gradient(40deg, #ffffff 60%, #22D3EE 176%)' }}>
            <div className="ResourceStreamlineBox relative rounded-xl flex flex-col justify-between h-full">
              <div className="ResourceStreamlineTop">
                <p className="ResourceStreamlineHeader sf-pro-font text-[14px] font-semibold text-[#000000] mb-4 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>
                  Streamline Your Field Operations
                </p>
                <div className="ResourceStreamlineContent mb-4">
                  <div className="ResourceStreamlineImage mb-4 flex justify-center">
                    <img 
                      src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/resource-menu-sub-img.png" 
                      alt="Streamline Your Field Operations" 
                      className="w-full h-auto rounded-xl"
                    />
                  </div>
                  <p className="ResourceStreamlineText sf-pro-font text-[14px] text-black italic text-left" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '20px' }}>
                    Boost technician productivity with real-time job scheduling & tracking.
                  </p>
                </div>
              </div>
              <a href="https://calendly.com/jeel-fieldcamp/30min" style={{ backgroundColor: '#EAEDFB', fontFamily: 'SFPRO, sans-serif' }} className="calendly-open ResourceStreamlineButton sf-pro-font w-full py-2.5 px-4 rounded-lg transition-all duration-200 text-[14px] font-medium text-gray-700 text-center">
                Book a Demo
                </a>
              {/* <button className="ResourceStreamlineButton sf-pro-font w-full py-2.5 px-4 rounded-lg transition-all duration-200 text-[14px] font-medium text-gray-700" style={{ backgroundColor: '#EAEDFB', fontFamily: 'SFPRO, sans-serif' }}>
                Book a demo
              </button> */}
            </div>
          </div>
        </div>

        {/* Bottom Links */}
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