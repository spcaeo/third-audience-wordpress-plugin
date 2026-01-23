"use client";
import React, { useCallback, useEffect, useRef } from 'react';
import { usePathname } from 'next/navigation';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { useRouter } from 'next/navigation';
gsap.registerPlugin(ScrollTrigger);

declare const Calendly: any;

interface TableToggleProps {
  buttonClassName?: string;
  buttonContent?: string;
}

// Global flag to prevent multiple event listeners
let detailsListenerAdded = false;

const ManageDetailsJS: React.FC = () => {
    useEffect(() => {
        // Only add listener once globally
        if (detailsListenerAdded) return;
        detailsListenerAdded = true;

        const handleDetailsClick = (e: MouseEvent) => {
            const target = e.target as HTMLElement;
            const clickedDetails = target.closest('details') as HTMLDetailsElement | null;
            const clickedSummary = target.closest('summary');

            if (!clickedDetails || !clickedSummary) return;

            // Prevent default behavior for all clicks on summary or its children
            e.preventDefault();

            // Toggle the open state
            const isOpen = clickedDetails.hasAttribute('open');

            // Close all other <details> siblings within the same wrapper
            const wrapper = clickedDetails.closest('.common-faq-wrapper');
            if (wrapper) {
                const allDetails = wrapper.querySelectorAll('details');
                allDetails.forEach((detail) => {
                    if (detail !== clickedDetails) {
                        detail.removeAttribute('open');
                    }
                });
            }

            // Toggle the clicked <details>
            if (isOpen) {
                clickedDetails.removeAttribute('open');
            } else {
                clickedDetails.setAttribute('open', '');
            }
        };

        // Use event delegation on the document
        document.addEventListener('click', handleDetailsClick);

        // Clean up on unmount
        return () => {
            document.removeEventListener('click', handleDetailsClick);
            detailsListenerAdded = false;
        };
    }, []);

    return null;
};

export const TextAnimation: React.FC<{ children: React.ReactNode, key: string }> = ({ children, key }) => {
  const textRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const splitText = textRef.current?.innerText.split(" ").map((word) => `${word} `);
    if (textRef.current && splitText) {
      textRef.current.innerHTML = "";
      splitText.forEach((word) => {
        const span = document.createElement("span");
        span.innerHTML = word;
        span.style.color = "#757575";
        textRef.current?.appendChild(span);
      });

      gsap.to(textRef.current.children, {
        color: "#1a1a1a",
        duration: 0.5,
        stagger: 0.1,
        ease: "none",
        scrollTrigger: {
          trigger: textRef.current,
          start: "top center",
          end: "bottom center",
          scrub: true,
        },
      });
    }
  }, []);

  return (
    <section key={key} className="px-4 bg-white animation-text">
      <div 
        ref={textRef}
        className="max-w-[1240px] mx-auto text-xl md:text-2xl lg:text-4xl font-medium md:leading-[1.25] lg:leading-relaxed animation-text"
      >
        {children}
      </div>
    </section>
  );
};

export const TabImage: React.FC = () => {
  useEffect(() => {
    let currentContent: Element | null = null;

    const handleImageHover = () => {
      const listItems = document.querySelectorAll('.image-faq-li');

      listItems.forEach((item) => {
        const parentUl = item.closest('.image-faq-ul');
        const useClick = parentUl?.classList.contains('js-on-click-image-change');
        const eventType = useClick ? 'click' : 'mouseenter';

        item.addEventListener(eventType, () => {
          const parentGroup = item.parentElement?.parentElement;
          const imageUrlFigure = parentGroup?.nextElementSibling?.querySelector('figure.image-faq-url');
          if (!imageUrlFigure) return;
          const image = item.querySelector('figure img');
          const svgContent = item.querySelector('figure svg');
          currentContent = imageUrlFigure.querySelector('img, svg');

          if (!currentContent) return;

          if (!image && !svgContent) return;

          gsap.to(currentContent, {
            opacity: 0,
            duration: 0.3,
            onComplete: () => {
              if (image) {
                if (currentContent instanceof SVGElement) {
                  const newImage = document.createElement('img');
                  const parent = currentContent.parentElement;
                  if (!parent) return;

                  parent.replaceChild(newImage, currentContent);
                  currentContent = newImage;
                }

                if (!currentContent) {
                  return;
                }

                const imageUrl = image.getAttribute('src');
                const imageSrcSet = image.getAttribute('srcset');

                if (imageUrl) {
                  if (!currentContent) return;

                  (currentContent as HTMLImageElement).src = imageUrl;
                  if (imageSrcSet) {
                    (currentContent as HTMLImageElement).srcset = imageSrcSet;
                  }

                  const loadImage = () => {
                    return new Promise<void>((resolve) => {
                      const img = currentContent as HTMLImageElement;
                      if (!img) return resolve();
                      if (img.complete) {
                        resolve();
                      } else {
                        img.onload = () => resolve();
                        img.onerror = () => resolve();
                      }
                    });
                  };

                  loadImage().then(() => {
                    gsap.to(currentContent, {
                      opacity: 1,
                      duration: 0.3
                    });
                  });
                }
              } else if (svgContent) {
                if (currentContent instanceof HTMLImageElement) {
                  const newSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg') as SVGSVGElement;
                  const parent = currentContent.parentElement;
                  if (!parent) return;

                  parent.replaceChild(newSvg, currentContent);
                  currentContent = newSvg;
                }

                if (!currentContent) {
                  return;
                }

                if (currentContent instanceof SVGSVGElement) {
                  const svgCode = svgContent.outerHTML;
                  currentContent.innerHTML = svgCode;

                  gsap.to(currentContent, {
                    opacity: 1,
                    duration: 0.3
                  });
                }
              }
            }
          });
        });
      });
    };

    handleImageHover();

    return () => {
      const listItems = document.querySelectorAll('.image-faq-li');
      listItems.forEach((item) => {
        item.removeEventListener('mouseenter', () => {});
      });
    };
  }, []);

  return null;
};

// Custom hook for tracking navigation paths
const usePathTracking = () => {
  const pathname = usePathname();

  useEffect(() => {
    const storage = globalThis?.sessionStorage;
    if (!storage) return;
    
    // Get the current full URL including search params
    const currentUrl = window.location.href;
    
    // Check if this is the first time the hook is running on this page
    const isInitialLoad = storage.getItem('currentPath') !== globalThis.location.pathname;
    
    // On initial page load (not client-side navigation)
    if (isInitialLoad) {
      const externalReferrer = document.referrer;
      // Only store external referrer if it's from a different domain
      if (externalReferrer && !externalReferrer.includes(window.location.hostname)) {
        storage.setItem('externalReferrer', externalReferrer);
      } else if (externalReferrer.includes(window.location.hostname)) {
        // Clear external referrer if it's from our own domain
        storage.removeItem('externalReferrer');
      }
    }
    
    // Get the previous URL, preferring internal navigation over external referrer
    const prevFullUrl = storage.getItem('currentFullUrl') || 
                      (isInitialLoad ? storage.getItem('externalReferrer') : '') || 
                      '';
                      
    // Store the previous full URL and current path/URL
    storage.setItem('prevPath', prevFullUrl); // Now storing full URL instead of just path
    storage.setItem('currentPath', globalThis.location.pathname);
    storage.setItem('currentFullUrl', currentUrl);
  }, [pathname]);
};

export const AppendUTMToAnchor: React.FC = () => {
  const pathname = usePathname();
  usePathTracking(); // Use the custom hook


  useEffect(() => {
    const storage = globalThis?.sessionStorage;
    console.log(storage);
    const anchors = document.querySelectorAll('a.utm-medium-signup');
    const currentDate = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format
    // Get previousPageUrl directly from the storage object if it exists
    
    const referrer = storage?.getItem("prevPath") || ''; // Use previousPageUrl if available, fallback to prevPath
    const landingPageUrl = window.location.href;
    const hostName =  referrer ? new URL(referrer).hostname : '';

    // Determine source based on referrer and URL
    let uSource = 'referral'; // Default to referral
    if ((landingPageUrl.includes("ppc") && hostName.includes('google')) || landingPageUrl.includes("ppc") || landingPageUrl.includes("gclid")) {
      uSource = 'ppc';
    } else if (hostName === '') {
      uSource = 'direct';
    } else if (["google", "bing", "yahoo", "duckduckgo", "ecosia", "baidu", "aol", "qwant", "yandex", "ask", "searchencrypt"].some(engine => hostName.includes(engine))) {
      uSource = 'organic';
    } else if (["facebook", "twitter", "linkedin", "youtube", "pinterest", "reddit", "quora", "instagram", "flickr", "snapchat", "vimeo", "digg"].some(platform => hostName.includes(platform))) {
      uSource = 'social';
    }

    // Store landing page URL and referring domain if the source is organic
    if (uSource === 'organic' || uSource === 'social' || uSource === 'ppc') {
      localStorage.setItem('landingPageUrl', landingPageUrl);
      localStorage.setItem('referringDomain', hostName);
    }

    // Retrieve stored values
    const storedLandingPageUrl = localStorage.getItem('landingPageUrl');
    const storedReferringDomain = localStorage.getItem('referringDomain');

    anchors.forEach((anchor) => {
      const url = new URL(anchor.getAttribute('href') || '', window.location.origin);
      const dataMedium = anchor.getAttribute('data-medium') || ''; // Fetch data-medium attribute

      url.searchParams.set('u_clicked_on', currentDate); // Append the current date as a query parameter with key u_clicked_on
      url.searchParams.set('u_source', uSource); // Append the source
      url.searchParams.set('u_medium', dataMedium); // Append the data-medium value
      
      if (referrer) {
        url.searchParams.set('u_referrer_url', referrer); // Append the referrer URL if it exists
      }
      if (storedLandingPageUrl) {
        url.searchParams.set('u_initial_referrer', storedLandingPageUrl); // Append the initial landing page URL
      }
      if (storedReferringDomain) {
        url.searchParams.set('u_referring_domain', storedReferringDomain); // Append the referring domain
      }
      anchor.setAttribute('href', url.toString());
    });
  }, [pathname]);

  return null;
};

export const CalendlyEmbed: React.FC = () => {
  const controlTOCclick = useCallback((event: MouseEvent) => {
    // Check if the clicked element or any of its parents have the 'calendly-open' class
    const calendlyLink = (event.target as HTMLElement).closest('.calendly-open');
    if (!calendlyLink) return; // Exit if not a calendly link
    event.preventDefault();
    let dynamicUrl = (calendlyLink as HTMLAnchorElement).href;
    // Rest of your URL and UTM parameter logic...
    const url = new URL(dynamicUrl);
    const isOrganic = document.referrer && 
                     new URL(document.referrer).hostname !== window.location.hostname;
    url.searchParams.append('utm_source', isOrganic ? 'organic' : 'direct');
    url.searchParams.append('utm_medium', window.location.href);
    const storedLandingPageUrl = localStorage.getItem('landingPageUrl');
    if (storedLandingPageUrl) {
      url.searchParams.append('utm_content', storedLandingPageUrl);
    }
    dynamicUrl = url.toString();
 
    // Rest of your Calendly initialization...
    if (!document.querySelector('link[href="https://calendly.com/assets/external/widget.css"]')) {
      const calendlyStylesheet = document.createElement('link');
      calendlyStylesheet.rel = 'stylesheet';
      calendlyStylesheet.href = 'https://calendly.com/assets/external/widget.css';
      document.head.appendChild(calendlyStylesheet);
    }
 
    if (!document.querySelector('script[src="https://assets.calendly.com/assets/external/widget.js"]')) {
      const script = document.createElement('script');
      script.src = 'https://assets.calendly.com/assets/external/widget.js';
      script.onload = () => {
        if (typeof Calendly !== 'undefined') {
          Calendly.showPopupWidget(dynamicUrl);
        }
      };
      document.head.appendChild(script);
    } else if (typeof Calendly !== 'undefined') {
      Calendly.showPopupWidget(dynamicUrl);
    }
  }, []);
 
  useEffect(() => {
    const handleClick = (e: MouseEvent) => {
      // Only handle left mouse button clicks
      if (e.button !== 0) return;
      controlTOCclick(e);
    };
 
    document.addEventListener('click', handleClick, true); // Use capture phase
    return () => {
      document.removeEventListener('click', handleClick, true);
    };
  }, [controlTOCclick]);
 
  return null;
};

export const SmoothScrollJS: React.FC = () => {
  useEffect(() => {
      const handleAnchorClick = (e: Event) => {
          // Type assertion to MouseEvent
          const anchor = e.currentTarget as HTMLAnchorElement;

          if (anchor && anchor.getAttribute('href')?.startsWith('#')) {
              e.preventDefault();
              const parent = anchor.parentElement;
              const siblings = Array.from(parent?.parentElement?.querySelectorAll(':scope > .active') || []);
              siblings.forEach(sibling => sibling.classList.remove('active'));

              anchor.parentElement?.classList.add('active');

              const targetId = anchor.getAttribute('href')!.substring(1);
              const targetElement = document.getElementById(targetId);

              if (targetElement) {
                  const header = document.querySelector('header');
                  const headerOffset = header ? header.offsetHeight : 0;

                  const toc = document.querySelector('.navigation-toc') as HTMLElement;
                  const tocOffset = toc ? toc.offsetHeight + 70 : 0;

                  const totalOffset = headerOffset + tocOffset;
                  const elementPosition = targetElement.getBoundingClientRect().top;
                  const offsetPosition = elementPosition + window.pageYOffset - totalOffset;

                  window.scrollTo({
                      top: offsetPosition,
                      behavior: 'smooth',
                  });
              }
          }
      };

      // Handle scroll to update active TOC item
      const handleScroll = (): void => {
          const tocItems = Array.from(document.querySelectorAll('.navigation-toc li'));
          const viewportMiddle = window.innerHeight / 2;
          let closestItem = { distance: Infinity, element: null as HTMLElement | null };
          
          // First pass: find the closest section to the viewport middle
          tocItems.forEach(item => {
              const anchor = item.querySelector('a');
              if (anchor) {
                  const targetId = anchor.getAttribute('href')!.substring(1);
                  const targetElement = document.getElementById(targetId);
                  if (targetElement) {
                      const rect = targetElement.getBoundingClientRect();
                          const elementMiddle = rect.top + (rect.height / 2);
                          const distance = Math.abs(viewportMiddle - elementMiddle);
                          
                      if (distance < closestItem.distance) {
                              closestItem = { distance, element: item as HTMLElement };
                          }
                      }
                  }
          });
          
          // Second pass: update active states
          tocItems.forEach(item => {
              if (item === closestItem.element) {
                  item.classList.add('active');
              } else {
                  item.classList.remove('active');
              }
          });
      };

      const anchors = document.querySelectorAll('a[href^="#"]');
      anchors.forEach(anchor => anchor.addEventListener('click', handleAnchorClick));
      window.addEventListener('scroll', handleScroll);

      // Clean up the event listeners on component unmount
      return () => {
          anchors.forEach(anchor => anchor.removeEventListener('click', handleAnchorClick));
          window.removeEventListener('scroll', handleScroll);
      };
  }, []);

  return null; // Return nothing
};

const TableToggle: React.FC<TableToggleProps> = ({ buttonClassName = '', buttonContent = 'View All' }) => {
  const [isExpanded, setIsExpanded] = React.useState(false);

  React.useEffect(() => {
      console.log('table loaded')
      const viewButton = document.querySelector('.view-full-table');

      // Add click handler for your existing button
      if (viewButton) {
          viewButton.addEventListener('click', () => {
              setIsExpanded(!isExpanded);
              viewButton.remove();
          });
      }

      // Clean up event listener
      return () => {
          if (viewButton) {
              viewButton.removeEventListener('click', () => {
                  setIsExpanded(!isExpanded);
              });
          }
      };
  }, [isExpanded]);

  React.useEffect(() => {
      const tableRows = document.querySelectorAll('.hide-show-table tr') as NodeListOf<HTMLTableRowElement>;
      tableRows.forEach((row, index) => {
          // Show first 3 rows, hide the rest
          if (index >= 4) {
              row.style.display = isExpanded ? 'table-row' : 'none';
          } else {
              row.style.display = 'table-row';
          }
      });
  }, [isExpanded]);

  return null; // Return nothing since we're using an existing button
};

// View More Toggle functionality
function ViewMoreToggle() {
  const handleClick = React.useCallback((e: Event) => {
      e.preventDefault();
      e.stopPropagation();
      
      const button = e.target as HTMLElement;
      const viewToggleDiv = button.closest('.view-toggle');
      
      if (!viewToggleDiv) return;
      
      const toggleContent = viewToggleDiv.querySelector('.toggle-content');
      const viewMoreText = viewToggleDiv.querySelector('.view-more-text');
      const viewLessText = viewToggleDiv.querySelector('.view-less-text');
      
      if (toggleContent && viewMoreText) {
          const isHidden = toggleContent.classList.contains('hidden');
          
          requestAnimationFrame(() => {
              if (isHidden) {
                  toggleContent.classList.remove('hidden');
                  viewLessText?.classList.remove('hidden');
                  viewMoreText.classList.add('hidden');
              } else {
                  toggleContent.classList.add('hidden');
                  viewLessText?.classList.add('hidden');
                  viewMoreText.classList.remove('hidden');
              }
          });
      }
  }, []);

  React.useEffect(() => {
      // Add event listeners for both view-more and view-less buttons
      const buttons = document.querySelectorAll('.view-more-text, .view-less-text');
      buttons.forEach(button => {
          button.addEventListener('click', handleClick);
      });
  
      return () => {
          buttons.forEach(button => {
              button.removeEventListener('click', handleClick);
          });
      };
  }, [handleClick]);

  return null;
}

export { TableToggle, ViewMoreToggle };

export default ManageDetailsJS;
