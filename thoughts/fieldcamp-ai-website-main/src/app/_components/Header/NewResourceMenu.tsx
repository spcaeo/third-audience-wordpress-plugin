"use client";
import React from "react";
import Link from "next/link";

export const NewResourceMenu = ({
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

  // Filter top-level menu items
  const menuItems = menu.filter((item: any) => item.parentId === null || !item.parentId);

  // Split items into 3 columns
  const itemsPerColumn = Math.ceil(menuItems.length / 3);
  const column1 = menuItems.slice(0, itemsPerColumn);
  const column2 = menuItems.slice(itemsPerColumn, itemsPerColumn * 2);
  const column3 = menuItems.slice(itemsPerColumn * 2);

  return (
    <div className="pt-0 xl:pt-3 first-child-menu NewResourceMenuWrapper">
      {/* Desktop View */}
      <div className={`NewResourceSubMenu sub-menu relative min-[1023px]:fixed min-[1023px]:left-1/2 min-[1023px]:-translate-x-1/2 shadow-[0px_20px_25px_-5px_rgba(0,0,0,0.1),0px_10px_10px_-5px_rgba(0,0,0,0.04)] rounded-none min-[1023px]:rounded-2xl border border-gray-200 bg-white transition-all duration-300 ease-in-out ${isOpen ? 'block opacity-100 translate-y-0' : 'hidden opacity-0 translate-y-2'}`}>

        <div className="NewResourceMenuMain grid grid-cols-1 min-[1023px]:grid-cols-3 p-4 min-[1023px]:p-6 gap-0 min-[1023px]:gap-6">

          {/* Column 1 */}
          <div className="NewResourceColumn">
            <div className="NewSolutionSectionList">
              {column1.map((item: any) => (
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
                      <g clipPath="url(#clip0_resource_1)">
                        <path d="M8.74756 11.6277H19.1959" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                        <path d="M14.7183 16.1055L19.1961 11.6277" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                        <path d="M14.7183 7.14978L19.1961 11.6277" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                      </g>
                      <defs>
                        <clipPath id="clip0_resource_1">
                          <rect width="28.5852" height="23.6138" rx="3" fill="white"/>
                        </clipPath>
                      </defs>
                    </svg>
                  </div>
                </Link>
              ))}
            </div>
          </div>

          {/* Column 2 */}
          <div className="NewResourceColumn">
            <div className="NewSolutionSectionList">
              {column2.map((item: any) => (
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
                      <g clipPath="url(#clip0_resource_2)">
                        <path d="M8.74756 11.6277H19.1959" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                        <path d="M14.7183 16.1055L19.1961 11.6277" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                        <path d="M14.7183 7.14978L19.1961 11.6277" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                      </g>
                      <defs>
                        <clipPath id="clip0_resource_2">
                          <rect width="28.5852" height="23.6138" rx="3" fill="white"/>
                        </clipPath>
                      </defs>
                    </svg>
                  </div>
                </Link>
              ))}
            </div>
          </div>

          {/* Column 3 */}
          <div className="NewResourceColumn">
            <div className="NewSolutionSectionList">
              {column3.map((item: any) => (
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
                      <g clipPath="url(#clip0_resource_3)">
                        <path d="M8.74756 11.6277H19.1959" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                        <path d="M14.7183 16.1055L19.1961 11.6277" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                        <path d="M14.7183 7.14978L19.1961 11.6277" stroke="black" strokeWidth="1.16425" strokeLinecap="round" strokeLinejoin="round"/>
                      </g>
                      <defs>
                        <clipPath id="clip0_resource_3">
                          <rect width="28.5852" height="23.6138" rx="3" fill="white"/>
                        </clipPath>
                      </defs>
                    </svg>
                  </div>
                </Link>
              ))}
            </div>
          </div>

        </div>

      </div>
    </div>
  );
};
