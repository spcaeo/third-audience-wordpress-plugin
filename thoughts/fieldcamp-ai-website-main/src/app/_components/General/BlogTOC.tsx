"use client";
import Link from "next/link";
import React, { useEffect, useState } from "react";
import ReactDOMServer from 'react-dom/server';

function BlogTOC(content: any) {
  const [scrolled, setScrolled] = useState(false);
  const [activeH2Id, setActiveH2Id] = useState<string | null>(null);
  const [activeH3Id, setActiveH3Id] = useState<string | null>(null);
  const [isOpen, setIsOpen] = useState(false);
  const [lastScrollTop, setLastScrollTop] = useState(0);

  useEffect(() => {
    if (typeof window === "undefined") return; // Ensure code runs only on client side

    const handleResize = () => {
      const ezTocList = document.querySelector(".blog_index");
      if (!ezTocList) return;
      const scrHeight = ezTocList.scrollHeight;
      const screenHeight = window.innerHeight;

      const srHeight = screenHeight - 370;

      const tocWrapper = document.querySelector(
        ".toc_wrapper ul"
      ) as HTMLElement;
      if (!tocWrapper) return;
      tocWrapper.style.maxHeight = srHeight + "px";

      const scrollHeight = tocWrapper.scrollHeight;

      const scroll = tocWrapper.scrollTop;
      const totalHeightScroll = scrHeight - scrollHeight;

      const sideMenuBottomImg = document.querySelector(
        "#side-menubottom img"
      ) as HTMLImageElement;
      const sideMenuBottom = document.querySelector(
        "#side-menubottom"
      ) as HTMLImageElement;
      if (scroll >= totalHeightScroll && sideMenuBottomImg && sideMenuBottom) {
        sideMenuBottomImg.style.filter = "grayscale(100%) brightness(190%)";
        sideMenuBottom.style.cursor = "default";
      }

      const sideMenuTop = document.querySelector("#side-menutop");
      if (sideMenuTop) {
        sideMenuTop.addEventListener("click", () => {
          tocWrapper.scrollBy({
            top: -160,
            behavior: "smooth",
          });
        });
      }

      if (sideMenuBottom) {
        sideMenuBottom.addEventListener("click", () => {
          tocWrapper.scrollBy({
            top: 160,
            behavior: "smooth",
          });
        });
      }
    };

    const handleScroll = () => {
      const scrollPosition = window.scrollY;
      const contentWrapper = document.querySelector('.blog_content_wrapper') as HTMLElement;
      const contentOffsetTop = contentWrapper?.offsetTop || 0;
      const contentOffsetBottom = contentOffsetTop + (contentWrapper?.scrollHeight || 0); // Calculate bottom
      const blogIndexCover = document.querySelector('.blog_index_cover') as HTMLElement;
      const blogIndexCoverHeight = blogIndexCover ? blogIndexCover.offsetHeight : 0; 
      const adjustedContentOffsetBottom = contentOffsetBottom - blogIndexCoverHeight - 10;


      // Detect scroll direction
      if (scrollPosition < lastScrollTop) {
        setIsOpen(false); // Close TOC on scroll up
      }
      setLastScrollTop(scrollPosition);

      if(scrollPosition > contentOffsetTop && scrollPosition < adjustedContentOffsetBottom){
        setScrolled(true);
      }else{
        setScrolled(false);
      }

      // Logic for h2 elements
      const h2Anchors = document.querySelectorAll(".blog_content_wrapper h2.toc_heading[id]");
      let newActiveH2Id: string | null = null;

      for (let i = 0; i < h2Anchors.length; i++) {
        const anchor = h2Anchors[i] as HTMLElement;
        const anchorExists = document.querySelector(
          '.blog_index li a[href="#' + anchor.id + '"]'
        );

        if (anchorExists !== null) {
          const element = document.getElementById(anchor.id);
          if (element !== null) {
            if (element.getBoundingClientRect().top < 200) {
              newActiveH2Id = anchor.id;
            } else {
              break;
            }
          }
        }
      }

      if (newActiveH2Id !== activeH2Id) {
        setActiveH2Id(newActiveH2Id);
        // Scroll TOC to active item
        const activeTOCItem = document.querySelector(`.blog_index li a[href="#${newActiveH2Id}"]`);
        if (activeTOCItem) {
          const blogIndex = document.querySelector(".blog_index") as HTMLElement;
          if (blogIndex) {
            const activeItemOffsetTop = (activeTOCItem as HTMLElement).offsetTop;
            blogIndex.scrollTop = activeItemOffsetTop - blogIndex.offsetTop;
          }
        }
      }

      // Logic for h3 elements
      const h3Anchors = document.querySelectorAll(".blog_content_wrapper h3.toc_heading[id]");
      let newActiveH3Id: string | null = null;

      for (let i = 0; i < h3Anchors.length; i++) {
        const anchor = h3Anchors[i] as HTMLElement;
        const anchorExists = document.querySelector(
          '.blog_index li a[href="#' + anchor.id + '"]'
        );

        if (anchorExists !== null) {
          const element = document.getElementById(anchor.id);
          if (element !== null) {
            if (element.getBoundingClientRect().top < 200) {
              newActiveH3Id = anchor.id;
            } else {
              break;
            }
          }
        }
      }

      if (newActiveH3Id !== activeH3Id) {
        setActiveH3Id(newActiveH3Id);
      }
    };

    window.addEventListener("scroll", handleScroll);
    handleResize();

    window.addEventListener("resize", handleResize);

    return () => {
      window.removeEventListener("scroll", handleScroll);
      window.removeEventListener("resize", handleResize);
    };
  }, [activeH2Id, activeH3Id, lastScrollTop]);

  useEffect(() => {
    if (typeof window === "undefined") return; // Ensure code runs only on client side

    document.querySelectorAll('.blog_index a[href^="#"]').forEach((anchor) => {
      anchor.addEventListener("click", (e) => {
        e.preventDefault();
        const href = anchor.getAttribute("href")?.replace("#", ""); // Optional chaining
        const element = document.getElementById(
          href || ""
        ) as HTMLElement | null; // Provide a default value
        if (element) {
          const headerOffset = 120;
          const elementPosition = element.getBoundingClientRect().top;
          const offsetPosition =
            elementPosition + window.pageYOffset - headerOffset;
          window.scrollTo({
            top: offsetPosition,
            behavior: "smooth",
          });
        }
      });
    });
  }, []);

  const handleClick =
    (id: string) =>
    (event: React.MouseEvent<HTMLAnchorElement, MouseEvent>) => {
      event.preventDefault();
      const headerOffset = 100; // Adjust this value to match your header height
      const element = document.getElementById(id);
      const elementPosition = element?.getBoundingClientRect().top || 0;
      const offsetPosition = elementPosition + window.scrollY - headerOffset;

      window.scrollTo({
        top: offsetPosition,
        behavior: "smooth",
      });

      setIsOpen(false); // Close TOC on mobile after clicking a link
    };

  const toggleTOC = () => {
    setIsOpen(!isOpen);
  };

  const stripHTML = (html: string | React.ReactNode) => {
    const htmlString = typeof html === 'string' 
      ? html 
      : React.isValidElement(html) 
        ? ReactDOMServer.renderToStaticMarkup(html) 
        : '';
    return htmlString.replace(/<\/?[^>]+(>|$)/g, "");
  };

  const renderTOCItems = (items: { id: string; title: string; children?: { id: string; title: string }[] }[]) => {
    const existingIds = new Set(); // Track existing IDs to prevent duplicates

    return items.map((item) => {
      if (existingIds.has(item.id)) return null; // Skip if already exists
      existingIds.add(item.id); // Add to existing IDs

      return (
        <li key={item.id} className={item.id === activeH2Id ? "active" : ""}>
          <Link href={`#${item.id}`} className="no-underline" onClick={handleClick(item.id)} title={stripHTML(item.title)}>
            {stripHTML(item.title)}
          </Link>
          {item.children && item.children.length > 0 && (
            <ul>
              {item.children.map((child) => {
                if (existingIds.has(child.id)) return null; // Skip if already exists
                existingIds.add(child.id); // Add to existing IDs

                return (
                  <li key={child.id} className={child.id === activeH3Id ? "active" : ""}>
                    <Link href={`#${child.id}`} className="no-underline" onClick={handleClick(child.id)} title={stripHTML(String(child.title))}>
                      {stripHTML(String(child.title))}
                    </Link>
                  </li>
                );
              })}
            </ul>
          )}
        </li>
      );
    });
  };

  return (
    <div className={`blog_index_cover toc_wrapper sticky top-[90px] h-auto  ${scrolled ? "" : ""} ${isOpen ? "open" : ""}`}
      id="toc_container"
    >
        <p className={`blog_index_toggle_btn `} onClick={toggleTOC}>
        Table of Contents
        </p>
        <ul className="blog_index">
          {renderTOCItems(content.content)}
        </ul>
    </div>
  );
}

export default BlogTOC;