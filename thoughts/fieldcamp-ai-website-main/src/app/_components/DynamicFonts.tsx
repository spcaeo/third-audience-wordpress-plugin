'use client';

import { useEffect } from 'react';

export default function DynamicFonts() {
  useEffect(() => {
    // Get the current base path from the page URL or fallback
    const basePath = process.env.NEXT_PUBLIC_BASE_PATH || 
                     (typeof window !== 'undefined' && window.location.pathname.match(/^\/[^\/]+/) ? window.location.pathname.match(/^\/[^\/]+/)?.[0] : '') || 
                     '';
    
    // Remove any existing Raleway font styles
    const existingFontStyles = document.querySelectorAll('#dynamic-raleway-fonts');
    existingFontStyles.forEach(style => style.remove());

    // Create and inject new font styles with correct base path
    const style = document.createElement('style');
    style.id = 'dynamic-raleway-fonts';
    style.textContent = `
      @font-face {
        font-family: 'Raleway';
        src: url('${basePath}/fonts/Raleway-Regular.ttf') format('truetype');
        font-weight: 400;
        font-display: swap;
      }
      @font-face {
        font-family: 'Raleway';
        src: url('${basePath}/fonts/Raleway-Medium.ttf') format('truetype');
        font-weight: 500;
        font-display: swap;
      }
      @font-face {
        font-family: 'Raleway';
        src: url('${basePath}/fonts/Raleway-SemiBold.ttf') format('truetype');
        font-weight: 600;
        font-display: swap;
      }
      @font-face {
        font-family: 'Raleway';
        src: url('${basePath}/fonts/Raleway-Bold.ttf') format('truetype');
        font-weight: 700;
        font-display: swap;
      }
    `;
    document.head.appendChild(style);
  }, []);

  return null; // This component doesn't render anything visible
}