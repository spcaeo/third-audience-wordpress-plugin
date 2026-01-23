'use client';
import Link from "next/link"
import { useEffect, useState } from "react";

export const TwoColumnMenu = ({ menu, onLinkClick, currentPath }: { menu: any, onLinkClick: () => void, currentPath: string }) => {
    const [openinnerSubMenu, setOpeninnerSubMenu] = useState<number | null>(null);
    const [isLargeScreen, setIsLargeScreen] = useState<boolean | null>(null);
    const [isClient, setIsClient] = useState(false);

    useEffect(() => {
        setIsClient(true);
        if (typeof window !== 'undefined') {
            const handleResize = () => {
                setIsLargeScreen(window.innerWidth > 992);
            };
            handleResize();
            window.addEventListener('resize', handleResize);

            return () => {
                window.removeEventListener('resize', handleResize);
            };
        }
    }, []);

    const handleInnerMenuClick = (id: number) => {
        { !isLargeScreen && setOpeninnerSubMenu(openinnerSubMenu === id ? null : id) };
    };

    const isActive = (url: string) => {
        if (!isClient || url === '#') return false;
        try {
            const menuItemPath = new URL(url, window.location.origin).pathname;
            return menuItemPath === currentPath;
        } catch (error) {
            console.error("Error parsing URL:", error);
            return false;
        }
    };

    return (
        <div className="sub-menu">
            <ul className="min-[992px]:rounded-lg flex min-[992px]:min-w-[720px] min-[992px]:py-[15px] flex-col min-[992px]:flex-row">
                {menu.map((submenuItem: any) => (
                    <li className={`border-t lg:border-t-transparent w-full lg:w-1/2 py-[5px] ps-[15px] pe-[15px] min-[992px]:px-4 min-[992px]:py-0   ${!isLargeScreen && submenuItem?.childItems?.nodes.length > 0 && 'has-children relative'} ${openinnerSubMenu === submenuItem.id && 'active'} `} key={submenuItem.id}>
                        <Link href={submenuItem.url} title={submenuItem?.label} className={`lg:border-b lg:pb-2 peer leading-[35px] text-sm min-[992px]:text-base lg:font-medium relative block text-black hover:text[var(--primary)] decoration-transparent ${isActive(submenuItem.url) ? 'current-menu-item' : ''}`} onClick={() => handleInnerMenuClick(submenuItem?.id)}>{submenuItem?.label}</Link>

                        {submenuItem?.childItems?.nodes.length > 0 &&
                            <ul className={`mt-2.5 *:leading-[35px] *:pb-[5px] *:relative min-[992px]:block ${!isLargeScreen && openinnerSubMenu === submenuItem.id ? 'block' : 'hidden'}`} >
                                {submenuItem.childItems?.nodes.map((childItem: any) => (
                                    <li onClick={onLinkClick} key={childItem.id} className={`py-2 lg:py-1 px-4 lg:px-0 border-t lg:border-t-transparent ${isActive(childItem.url) ? 'current-menu-item' : ''}`}>
                                        <Link title={childItem.label} href={childItem.url} className={`${childItem.cssClasses.join(' ')} leading-[26px] lg:leading-[30px] text-sm min-[992px]:text-base relative block text-black hover:text[var(--primary)] decoration-transparent`}>
                                            {childItem.label}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        }
                    </li>
                ))}
            </ul>
        </div>
    )
}