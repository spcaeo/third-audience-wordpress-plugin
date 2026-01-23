import Script from "next/script"; // Import the Script component
import DevRevChat from "./_components/Chat/DevRevChat";
import React from "react";
import IntercomChat from "./_components/Chat/intercome";
import dynamic from 'next/dynamic';
import Twocolumnsliderreview from "@/app/_components/Twocolumnsliderreview";
import Thumbnailslider from "@/app/_components/Thumbnailslider";

const ServiceModal = dynamic(() => import("./_components/services-mng-modals/modal"), { 
  ssr: false 
});

const GTM_CODE = process.env.GTM_CODE;

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <head>
        {/* Font preloading for better performance */}
        <link
          rel="preload"
          href="/fonts/SF-Pro-Text-Regular.otf"
          as="font"
          type="font/otf"
          crossOrigin="anonymous"
          fetchPriority="high"
        />
        <link
          rel="preload"
          href="/fonts/Inter-Regular.ttf"
          as="font"
          type="font/truetype"
          crossOrigin="anonymous"
          fetchPriority="high"
        />
        {/* Preconnect to external domains for faster loading */}
        <link rel="preconnect" href="https://cms.fieldcamp.ai" />
        <link rel="preconnect" href="https://widget.intercom.io" />
        <link rel="preconnect" href="https://www.googletagmanager.com" />
        <link rel="preconnect" href="https://connect.facebook.net" />
        {GTM_CODE && (
          <Script
            id="GTM"
            strategy="lazyOnload"
            dangerouslySetInnerHTML={{
              __html: `(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','${GTM_CODE}');`,
            }}
          ></Script>
        )}
        <Script id="facebook-pixel" strategy="lazyOnload">
          {`
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '705554132389145');
            fbq('track', 'PageView');
          `}
        </Script>
        <noscript>
          <img height="1" width="1" style={{display: 'none'}} 
            src="https://www.facebook.com/tr?id=705554132389145&ev=PageView&noscript=1"
            alt="facebook-pixel"
          />
        </noscript>
      </head>
      <body
        className={`antialiased`}
      >
        {GTM_CODE && (
          <noscript
            dangerouslySetInnerHTML={{
              __html: `<iframe src="https://www.googletagmanager.com/ns.html?id=${GTM_CODE}"
          height="0" width="0" style="display:none;visibility:hidden"></iframe>`,
            }}
          ></noscript>
        )}

        <main id="main-content">
          {children}
        </main>

        {/* <DevRevChat /> */}
        <IntercomChat />
        <ServiceModal />
         <Twocolumnsliderreview/>
         <Thumbnailslider/>
      </body>
    </html>
  );
}