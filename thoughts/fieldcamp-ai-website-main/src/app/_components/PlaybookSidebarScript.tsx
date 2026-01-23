'use client';

import { useEffect } from 'react';

export default function PlaybookSidebarScript() {
  useEffect(() => {
    const initSidebarNavigation = () => {
      const nav = document.querySelector('.sidebar-navigation');
      if (!nav) return;

      // Click toggle handler
      const handleToggle = (e: MouseEvent) => {
        const button = (e.target as HTMLElement).closest('.nav-toggle');
        if (!button) return;

        const category = button.closest('.nav-category');
        if (!category) return;

        const isOpen = category.classList.contains('open');

        category.classList.toggle('open', !isOpen);
        button.classList.toggle('active', !isOpen);
        button.setAttribute('aria-expanded', String(!isOpen));
      };

      // Add event listener
      nav.addEventListener('click', handleToggle as EventListener);

      // Open first category by default
      const firstCategory = nav.querySelector('.nav-category');
      if (firstCategory) {
        firstCategory.classList.add('open');
        const firstBtn = firstCategory.querySelector('.nav-toggle');
        if (firstBtn) {
          firstBtn.classList.add('active');
          firstBtn.setAttribute('aria-expanded', 'true');
        }
      }

      // Cleanup function
      return () => {
        nav.removeEventListener('click', handleToggle as EventListener);
      };
    };

    // Run the initialization
    const cleanup = initSidebarNavigation();

    // Also try to run it after a small delay in case DOM is still loading
    const timeoutId = setTimeout(() => {
      initSidebarNavigation();
    }, 100);

    // Cleanup on unmount
    return () => {
      if (cleanup) cleanup();
      clearTimeout(timeoutId);
    };
  }, []);

  return null; // This component doesn't render anything
}