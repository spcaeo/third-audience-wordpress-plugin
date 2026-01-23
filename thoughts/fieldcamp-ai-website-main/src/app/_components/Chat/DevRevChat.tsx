"use client";
import { useEffect } from "react";

// Extend the global Window interface to include plugSDK
declare global {
  interface Window {
    plugSDK: {
      init: (config: { app_id: string }) => void;
    };
  }
}

const DevRevChat = () => {
  useEffect(() => {
    // Wait for the page to fully load before initializing DevRev chat
    const loadDevRevChat = () => {
      // Add a delay of 2 seconds after page load
      setTimeout(() => {
        const script = document.createElement("script");
        script.src = "https://plug-platform.devrev.ai/static/plug.js";
        script.type = "text/javascript";
        script.async = true;

        // Handle script load and error events
        script.onload = () => {
          try {
            if (window.plugSDK) {
              window.plugSDK.init({
                app_id:
                  "DvRvStPZG9uOmNvcmU6ZHZydi11cy0xOmRldm8vMUVKeWFDTkdHRzpwbHVnX3NldHRpbmcvMV9ffHxfXzIwMjQtMTAtMjMgMDg6Mjg6MTIuMDM5MTExNDM3ICswMDAwIFVUQw==xlxendsDvRv",
              });
            } else {
              console.error("plugSDK is not available on the window object.");
            }
          } catch (error) {
            console.error("Error initializing plugSDK:", error);
          }
        };

        script.onerror = () => {
          console.error("Failed to load DevRev chat script");
        };

        document.body.appendChild(script);
      }, 10000); // 2 second delay
    };

    // Check if the document is already loaded
    if (document.readyState === 'complete') {
      loadDevRevChat();
    } else {
      // If not loaded, wait for the load event
      window.addEventListener('load', loadDevRevChat);
      // Cleanup
      return () => window.removeEventListener('load', loadDevRevChat);
    }
  }, []);

  return null;
};

export default DevRevChat;
