'use client';

import React, { useEffect } from 'react';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * ScrollImageSwitcher - Handles scroll-based image switching with pinned images
 * For the "Your operations, under control" section on customization page
 *
 * WordPress Gutenberg HTML Structure:
 *
 * <div class="wp-block-columns image-faq-column why-field-column">
 *   <div class="wp-block-column">
 *     <div class="image-faq-ul">
 *       <div class="image-faq-li why-field-li-hover-bx">
 *         <figure class="image-faq-url"><img/></figure>  <!-- Hidden image for this item -->
 *         <h4>Title</h4>
 *         <p>Description</p>
 *       </div>
 *       ... more items
 *     </div>
 *   </div>
 *   <div class="wp-block-column faq-figur-col">
 *     <figure class="image-faq-url"><img/></figure>  <!-- Display image -->
 *   </div>
 * </div>
 */

const ScrollImageSwitcher: React.FC = () => {
  useEffect(() => {
    // Only run on desktop (>768px)
    const isDesktop = window.innerWidth >= 768;
    if (!isDesktop) return;

    // Find all "why-field-column" sections (there are multiple on the page)
    const sections = document.querySelectorAll('.why-field-column');

    if (sections.length === 0) return;

    const triggers: ScrollTrigger[] = [];

    sections.forEach((section) => {
      // Find the image column (faq-figur-col) within this section
      const imageColumn = section.querySelector('.faq-figur-col');
      // Find the display image
      const displayImage = imageColumn?.querySelector('figure.image-faq-url img') as HTMLImageElement;
      // Find all content items within this section
      const contentItems = section.querySelectorAll('.image-faq-li');

      if (!imageColumn || !displayImage || contentItems.length === 0) return;

      // Store pin trigger reference for this section
      let sectionPinTrigger: ScrollTrigger | null = null;

      // Function to update the display image
      const updateImage = (index: number) => {
        const activeItem = contentItems[index];
        if (!activeItem) return;

        // Get the image from within the content item
        const itemImage = activeItem.querySelector('figure img') as HTMLImageElement;
        if (!itemImage) return;

        // Update active states for content items
        contentItems.forEach((item, i) => {
          if (i === index) {
            item.classList.add('scroll-active');
          } else {
            item.classList.remove('scroll-active');
          }
        });

        // Animate the image change with smooth fade, scale and slide effect
        gsap.to(displayImage, {
          opacity: 0,
          y: -20,
          scale: 0.98,
          duration: 0.3,
          ease: 'power2.in',
          onComplete: () => {
            displayImage.src = itemImage.src;
            if (itemImage.srcset) {
              displayImage.srcset = itemImage.srcset;
            }
            gsap.fromTo(displayImage,
              {
                opacity: 0,
                y: 30,
                scale: 0.98
              },
              {
                opacity: 1,
                y: 0,
                scale: 1,
                duration: 0.5,
                ease: 'power3.out'
              }
            );
          }
        });
      };

      // Get the content column height for proper pin duration
      const contentColumn = section.querySelector('.image-faq-ul') as HTMLElement;
      const contentHeight = contentColumn ? contentColumn.offsetHeight : 500;

      // Multiply content height to slow down scroll (2.5x = slower scroll)
      const scrollMultiplier = 2.5;
      const adjustedHeight = contentHeight * scrollMultiplier;

      // Pin the image column while scrolling through content
      sectionPinTrigger = ScrollTrigger.create({
        trigger: section,
        start: 'top 100px',
        end: () => `+=${adjustedHeight}`,
        pin: imageColumn,
        pinSpacing: false,
      });
      triggers.push(sectionPinTrigger);

      // Create scroll triggers for each content item
      const lastIndex = contentItems.length - 1;
      let pinDisabled = false;

      contentItems.forEach((item, index) => {
        const trigger = ScrollTrigger.create({
          trigger: item,
          start: 'top center',
          end: 'bottom center',
          onEnter: () => {
            updateImage(index);
          },
          onEnterBack: () => {
            updateImage(index);
            // When scrolling back into section, re-enable the pin
            if (pinDisabled && sectionPinTrigger) {
              pinDisabled = false;
              sectionPinTrigger.enable();
              ScrollTrigger.refresh();
            }
          },
          // Only for last item - disable pin when scrolled past
          onLeave: index === lastIndex ? () => {
            if (sectionPinTrigger && !pinDisabled) {
              pinDisabled = true;
              sectionPinTrigger.disable();
              gsap.set(imageColumn, { clearProps: 'all' });
            }
          } : undefined,
        });
        triggers.push(trigger);
      });

      // Initialize first item as active
      updateImage(0);
    });

    // Handle window resize
    const handleResize = () => {
      const isNowDesktop = window.innerWidth >= 768;
      if (!isNowDesktop) {
        // Kill all triggers on mobile
        triggers.forEach(t => t.kill());
        // Remove pinned styles
        sections.forEach(section => {
          const imageColumn = section.querySelector('.faq-figur-col');
          if (imageColumn) {
            gsap.set(imageColumn, { clearProps: 'all' });
          }
          // Remove active classes
          section.querySelectorAll('.image-faq-li').forEach(item => {
            item.classList.remove('scroll-active');
          });
        });
      }
    };

    window.addEventListener('resize', handleResize);

    // Cleanup
    return () => {
      window.removeEventListener('resize', handleResize);
      triggers.forEach(t => t.kill());
    };
  }, []);

  return null;
};

export default ScrollImageSwitcher;
