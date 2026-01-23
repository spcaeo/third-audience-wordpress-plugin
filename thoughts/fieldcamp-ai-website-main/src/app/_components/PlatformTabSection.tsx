'use client';

import React, { useEffect, useRef } from 'react';

const PlatformTabSection: React.FC = () => {
  const isInitialized = useRef(false);

  useEffect(() => {
    // Helper function to extract tab identifier from button class
    const getTabIdentifier = (element: Element): string | null => {
      const classes = Array.from(element.classList);
      for (const className of classes) {
        if (className.endsWith('-tab-btn') && className !== 'btn-platform-tab') {
          // Extract identifier: "hvac-tab-btn" -> "hvac"
          return className.replace('-tab-btn', '');
        }
      }
      return null;
    };

    // Function to update active states
    const setActiveTab = (identifier: string) => {
      const platformTabButtons = document.querySelectorAll('.btn-platform-tab');
      const platformTabContents = document.querySelectorAll('.platform-tab-desc');

      // Update buttons - add active to matching, remove from others
      platformTabButtons.forEach(button => {
        const btnIdentifier = getTabIdentifier(button);
        if (btnIdentifier === identifier) {
          button.classList.add('active');
        } else {
          button.classList.remove('active');
        }
      });

      // Update content panels - add active to matching, remove from others
      platformTabContents.forEach(content => {
        const classes = Array.from(content.classList);
        const isMatch = classes.some(className => className === `${identifier}-tab-desc`);
        if (isMatch) {
          content.classList.add('active');
        } else {
          content.classList.remove('active');
        }
      });
    };

    // Handle tab button click
    const handlePlatformTabClick = (e: Event) => {
      e.preventDefault();

      // Find the closest .btn-platform-tab element (handles clicks on inner elements)
      const target = e.target as Element;
      const button = target.closest('.btn-platform-tab');

      if (!button) return;

      const identifier = getTabIdentifier(button);
      if (identifier) {
        setActiveTab(identifier);
      }
    };

    // Initialize - set first tab as active if none are active
    const initializeTabs = () => {
      const platformTabButtons = document.querySelectorAll('.btn-platform-tab');
      const platformTabContents = document.querySelectorAll('.platform-tab-desc');

      if (platformTabButtons.length === 0) return;

      // Check if any content is already active
      const hasActiveContent = Array.from(platformTabContents).some(
        content => content.classList.contains('active')
      );

      if (!hasActiveContent) {
        // Set first tab as active
        const firstButton = platformTabButtons[0];
        const firstIdentifier = getTabIdentifier(firstButton);
        if (firstIdentifier) {
          setActiveTab(firstIdentifier);
        }
      } else {
        // Sync button active state with content
        for (const content of platformTabContents) {
          if (content.classList.contains('active')) {
            const classes = Array.from(content.classList);
            for (const className of classes) {
              if (className.endsWith('-tab-desc') && className !== 'platform-tab-desc') {
                const identifier = className.replace('-tab-desc', '');
                // Set the corresponding button as active
                platformTabButtons.forEach(button => {
                  const btnIdentifier = getTabIdentifier(button);
                  if (btnIdentifier === identifier) {
                    button.classList.add('active');
                  }
                });
                break;
              }
            }
            break;
          }
        }
      }
    };

    // Only initialize once
    if (!isInitialized.current) {
      initializeTabs();
      isInitialized.current = true;
    }

    // Use event delegation on document for better click handling
    document.addEventListener('click', handlePlatformTabClick);

    // Cleanup
    return () => {
      document.removeEventListener('click', handlePlatformTabClick);
    };
  }, []);

  return null;
};

export default PlatformTabSection;
