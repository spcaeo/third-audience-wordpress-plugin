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

const IntercomChat = () => {
  useEffect(() => {
    let loaded = false;

    const loadIntercomChat = () => {
      if (loaded) return;
      loaded = true;

      // Create and append Intercom settings script
      const settingsScript = document.createElement('script');
      settingsScript.innerHTML = `
        window.intercomSettings = {
          api_base: "https://api-iam.intercom.io",
          app_id: "fykf68k6",
        };
      `;
      document.head.appendChild(settingsScript);

      // Create and append Intercom widget script
      const widgetScript = document.createElement('script');
      widgetScript.innerHTML = `
        (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/fykf68k6';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
      `;
      document.body.appendChild(widgetScript);
    };

    // Load on user interaction OR after 8 seconds (whichever comes first)
    const events = ['scroll', 'mousemove', 'touchstart', 'click'];
    events.forEach(event => {
      window.addEventListener(event, loadIntercomChat, { once: true, passive: true });
    });

    const timeout = setTimeout(loadIntercomChat, 8000);

    return () => {
      events.forEach(event => window.removeEventListener(event, loadIntercomChat));
      clearTimeout(timeout);
    };
  }, []);

  return null;
};

export default IntercomChat;
