import React from 'react';
import "./module.scss"
import { Metadata } from 'next';
import Accordion from '@/app/_components/Accordion';
import Script from 'next/script';
import { AppendUTMToAnchor, CalendlyEmbed } from '@/app/_components/General/Custom';


export const metadata: Metadata = {
  title: 'AI Field Service Scheduling Software | FieldCamp 2025',
  description: 'Let customers self-book jobs 24/7 with AI scheduling. Cut phone time, avoid conflicts, and boost revenue. Try FieldCamp free—no card required.',
  robots: 'noindex, nofollow',
    alternates: {
      canonical: 'https://fieldcamp.ai/scheduling/'
    }
};

const faqItems = [
  { 
    title: "I'm not tech-savvy. Is this complicated?", 
    content: [
      " If you can send a text, you can use FieldCamp. No training needed. Our AI understands plain English commands like 'schedule a 2-hour HVAC job next week'."
    ]
  },
  { 
    title: "What about my existing customers?", 
    content: [
      "Keep serving them exactly how you do now. But watch how quickly they love booking online once they try it. Import your customer list in one click."
    ]
  },
  { 
    title: "Can I customize when I'm available?", 
    content: [
      "Completely. Set your zones, hours, and availability. Block out lunch, time off, whatever you need. Customers only see when you're actually free."
    ]
  },
  { 
    title: "What if I'm already using scheduling software?", 
    content: [
      "We'll import everything and show you what you've been missing. Most users say they can't believe their old software didn't have online booking."
    ]
  },
  { 
    title: "Is the price really just $35?", 
    content: [
      "Yes. $35 per user per month. Everything included. No modules. No add-ons. No 'call for pricing' nonsense."
    ]
  }
];

const pageTitle = metadata.title?.toString() || 'FieldCamp';
const pageDescription = metadata.description || '';
const pageUrl = metadata.alternates?.canonical?.toString() || 'https://fieldcamp.ai/';

const schemaData = [
  {
    "@context": "https://schema.org/",
    "@type": "WebPage",
    "@id": `${pageUrl}#website`,
    "url": pageUrl,
    "name": pageTitle,
    "description": pageDescription,
    "about": {
      "@type": "Product",
      "name": pageTitle,
      "url": pageUrl,
      "description": pageDescription,
      "image": "https://fieldcamp.ai/_next/static/media/logo.6811b83e.svg"
    }
  },
  {
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "FieldCamp: AI Field Service Management Software",
    "description": "Streamline your business with FieldCamp's AI-driven field management software. Intuitive, multilingual, and built for field service professionals to enhance efficiency and reduce complexity.",
    "url": "https://fieldcamp.ai/",
    "image": "https://fieldcamp.ai/_next/static/media/logo.6811b83e.svg",
    "brand": {
      "@type": "Brand",
      "name": "FieldCamp"
    },
    "offers": {
      "@type": "Offer",
      "priceCurrency": "USD",
      "price": "35",
      "url": "https://fieldcamp.ai/pricing/",
      "availability": "https://schema.org/InStock"
    },
    "review": {
      "@type": "Review",
      "reviewRating": {
        "@type": "Rating",
        "ratingValue": "4.8",
        "bestRating": "5"
      },
      "author": {
        "@type": "Organization",
        "name": "Capterra"
      }
    },
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "4.8",
      "reviewCount": "150"
    }
  }
];

export default function FieldCampVsJobber() {
  return (
    
    <div className="new-ppc-template">
      <Script
        id="structured-data"
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(schemaData) }}
      />
      <CalendlyEmbed/>
      <AppendUTMToAnchor/>
      <section className='banner-section'>
        <div className="container max-w-[1245px] mx-auto px-[15px] lg:px-0 py-8 lg:py-16">
          <div className="text-center mb-[25px] md:mb-[40px]">
            <h1 className="text-[34px] md:text-[46px] lg:text-[64px] leading-[1.15] font-bold text-[#232529] mb-[20px]">
              Your Scheduling Software <br />
              <span className="text-[#DC2626]">Can't Do This</span>
            </h1>
            <p className="text-[#232529] text-[18px] md:text-[20px] leading-[24px] mb-[28px]">
              Let customers book jobs directly into your calendar.<br />
              With AI that finds the perfect time slot. All while you sleep.
            </p>
            <div className="flex justify-center gap-4">
              <a href="https://app.fieldcamp.ai/signup" className="utm-medium-signup font-inter bg-[#7239EA] border-[3px] border-[rgba(114, 57, 234, 0.2)] text-white px-5 sm:px-8 py-[9px] rounded-[7px] font-medium hover:bg-[#7239EA]/80 transition-colors" data-medium="btn-start-free-trial-1">
                Start Free Trial
              </a>
              <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open font-inter bg-[#F3F3F3] text-[#232529] px-5 sm:px-8 py-[9px] rounded-[7px] font-medium hover:bg-[#F3F3F3]/80 transition-colors">
                Book a Demo
              </a>
            </div>
          </div>
          <div className="w-full">
            <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/06/Your-Scheduling-Software-Cant-Do-This.png" alt="" />
          </div>
        </div>
        </section>
          {/* Two Types Section */}
        <section className='two-types-section mb-[40px] sm:mb-[50px] md:mb-[60px] lg:mb-[90px]'>
          <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
            <div className="text-center mb-[40px]">
            <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[25px] md:mb-[40px]">
              There Are Two Types of<br /> Field Service Businesses
            </h2>
            <div className="flex justify-center gap-[25px] md:gap-[40px] max-w-[1140px] flex-col md:flex-row mx-auto">
              {/* Still Manual */}
              <div className="bg-white rounded-[15px] p-[20px] md:p-[30px] border-[1px] border-[rgba(201,41,52,0.3)] w-full relative">
                <div className="flex items-center gap-2 mb-3 md:mb-6 justify-between">
                  <h3 className="text-[20px] md:text-[32px] font-medium text-[#232529]">Still Manual</h3>
                  <span className="right-icon">
                    <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/06/sad.svg" alt="Still Manual" width={40} height={40} />
                  </span>
                </div>
                <div className="space-y-3 text-left mb-3 md:mb-6">
                  <div className="flex items-start gap-2">
                    <div className="cancle-icon relative top-[0px]">
                      <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#232529"/></svg>
                    </div>
                    <span className="text-[16px] md:text-[18px] text-[#232529]">Drowning in phone calls asking "when can you come?"</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <div className="cancle-icon relative top-[0px]">
                      <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#232529"/></svg>
                    </div>
                    <span className="text-[16px] md:text-[18px] text-[#232529]">Customers calling you instead of rebooking</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <div className="cancle-icon relative top-[0px]">
                      <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#232529"/></svg>
                    </div>
                    <span className="text-[16px] md:text-[18px] text-[#232529]">Techs driving back and forth wasting fuel</span>
                  </div>
                </div>
                <div className="bg-[#FFE2E4] p-[15px] md:p-[20px] rounded-lg text-left">
                  <div className="text-[#C92934] text-[16px] md:text-[18px] font-semibold">Revenue</div>
                  <div className="text-[#C92934] text-[15px] md:text-[18px]">Losing $3-5k monthly to inefficiency</div>
                </div>
              </div>
  
              {/* Using Modern Software */}
              <div className="bg-white rounded-[15px] p-[20px] md:p-[30px] border-[1px] border-[#E4DE24] w-full relative">
                <div className="flex items-center gap-2 mb-3 md:mb-6 justify-between">
                  <h3 className="text-[20px] md:text-[32px] font-medium text-[#232529]">Using "Modern" Software</h3>
                  <span className="right-icon">
                    <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/06/confused.svg" alt="Still Manual" width={40} height={40} />
                  </span>
                </div>
                <div className="space-y-3 text-left mb-3 md:mb-6">
                  <div className="flex items-start gap-2">
                    <div className="cancle-icon relative top-[0px]">
                      <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#232529"/></svg>
                    </div>
                    <span className="text-[16px] md:text-[18px] text-[#232529]">But still taking booking calls manually</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <div className="cancle-icon relative top-[0px]">
                      <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#232529"/></svg>
                    </div>
                    <span className="text-[16px] md:text-[18px] text-[#232529]">Can't let customers self-schedule</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <div className="cancle-icon relative top-[0px]">
                      <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#232529"/></svg>
                    </div>
                    <span className="text-[16px] md:text-[18px] text-[#232529]">No AI to suggest optimal time slots</span>
                  </div>
                </div>
                <div className="bg-[#F5F3B4] p-[15px] md:p-[20px] rounded-lg text-left">
                  <div className="text-[#898506] text-[16px] md:text-[18px] font-semibold">Cost</div>
                  <div className="text-[#898506] text-[15px] md:text-[18px]">Paying $150+ per user for incomplete solutions</div>
                </div>
              </div>
            </div>
            <div className="mt-[25px] md:mt-[45px] bg-gray-900 text-white px-[25px] py-[10px] rounded-[10px] md:text-[18px] text-[16px] font-normal max-w-[400px] mx-auto">
              Both are leaving money on the table.
            </div>
          </div>
          </div>
        </section>
       
        {/* Calendar Section */}
        <section className='two-types-section mb-[40px] sm:mb-[50px] md:mb-[60px] lg:mb-[90px]'>
          <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
            <div className="text-center mb-16">
              <div className="text-[#232529] text-[14px] md:text-[16px] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-full max-w-fit mx-auto mb-[15px]">Welcome To 2025</div>
              <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[25px] md:mb-[40px]">
                Where Your Calendar <br /> Fills Itself
              </h2>
              <div className="flex-col-reverse md:flex-row flex justify-center gap-[20px] md:gap-[100px] lg:gap-[150px] items-center max-w-[1080px] mx-auto mb-[40px] md:mb-[25px]">
                <div className="text-left max-w-[450px]">
                  <h3 className="text-[24px] font-bold text-[#232529] mb-[10px] md:mb-[20px]">Online Booking That Actually Works</h3>
                  <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                    Your website becomes a 24/7 booking machine. Customers enter 
                    their address, see if your service their area, and then available 
                    time slots, and pay upfront. No calls.
                  </p>
                  <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                    No back and forth. Just booked, paid jobs appearing in your 
                    schedule.
                  </p>
                  <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                    Like having a 24/7 dispatcher that never sleeps.
                  </p>
                </div>
                
                <div className="bg-white">
                  <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/06/Online-Booking-That-Actually-Works.png" alt="" />
                </div>
              </div>
              <div className="flex-col-reverse md:flex-row-reverse flex justify-center gap-[20px] md:gap-[100px] lg:gap-[150px] items-center max-w-[1080px] mx-auto mb-[40px] md:mb-[25px]">
                <div className="text-left max-w-[450px]">
                  <h3 className="text-[24px] font-bold text-[#232529] mb-[10px] md:mb-[20px]">AI Scheduling Intelligence</h3>
                  <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  Customer calls asking "when can you come?" You click one button, select job duration, and instantly see optimal slots for the next 7-15 days. The AI considers tech skills, current locations, and existing routes.
                  </p>
                  <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                    What used to take 10 minutes now takes 10 seconds.
                  </p>
                </div>
                
                <div className="bg-white">
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/06/AI-Scheduling-Intelligence.png" alt="" />
                </div>
              </div>
              <div className="flex-col-reverse md:flex-row flex justify-center gap-[20px] md:gap-[100px] lg:gap-[150px] items-center max-w-[1080px] mx-auto mb-[40px] md:mb-[25px]">
                <div className="text-left max-w-[450px]">
                  <h3 className="text-[24px] font-bold text-[#232529] mb-[10px] md:mb-[20px]">True Route Optimization</h3>
                  <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  Not just "draw a line between jobs." Actual AI that reorganizes your entire day with one click. Plan your techs' routes, then optimize. Watch drive time drop by 25% instantly.
                  </p>
                  <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  Your competitors are still zigzagging.Your competitors are still zigzagging.
                  </p>
                </div> 
                
                <div className="bg-white">
                  <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/06/True-Route-Optimization.png" alt="" />
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* Calendar Section */}
        <section className='two-types-section'>
          <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
            <div className="text-center mb-16">
              <div className="text-[#232529] text-[14px] md:text-[16px] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-full max-w-fit mx-auto mb-[15px]">The Comparison</div>
              <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[25px] md:mb-[40px]">Why FieldCamp Replaced <br />  Their Old Software</h2>
              <div className='max-w-[1080px] mx-auto'>
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/06/Why-FieldCamp-Replaced.png" alt="" />
              </div>
            </div>
          </div>
        </section>

    <section className='pricing-section mb-[40px] sm:mb-[50px] md:mb-[60px] lg:mb-[90px]'>
          <div className="container mx-auto px-4 py-[40px] md:py-[65px]">
            <p className="text-[#fff] text-[14px] md:text-[16px] border-[1px] border-[#393B3F] p-[5px_15px] rounded-full max-w-fit mx-auto mb-[15px]">Show Me The Money</p>
            <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-white mb-[25px] md:mb-[40px]">Do the Math</h2>
            <div className="grid lg:grid-cols-3 gap-6 max-w-6xl mx-auto mb-6 md:mb-12">
            <div className="card-losing bg-[rgba(21,21,23,0.15)] rounded-[20px] p-[15px] md:p-[20px] border-[1px] border-[#727272]">
                <div className="status-losing bg-[#393B3F] mb-[20px] md:mb-[30px] md:text-[16px] text-[14px] text-[#fff] p-[5px_10px] max-w-fit rounded-[10px]">
                    Losing
                </div>
                
                <h3 className="text-xl font-semibold text-white md:mb-6 mb-4 leading-tight text-left">Without Proper Scheduling</h3>
                
                <div className="space-y-4 mb-5 md:mb-8 max-w-[280px] text-left">
                    <div className="flex items-start gap-2">
                      <div className="cancle-icon relative top-[0px]">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#fff"/></svg>
                      </div>
                      <span className="text-[15px] md:text-[16px] text-[#fff]">Miss 2-3 jobs per week from conflicts</span>
                    </div> 
                    <div className="flex items-start gap-2">
                      <div className="cancle-icon relative top-[0px]">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#fff"/></svg>
                      </div>
                      <span className="text-[15px] md:text-[16px] text-[#fff]">Waste 2 hours daily on phone scheduling</span>
                    </div>
                    <div className="flex items-start gap-2">
                      <div className="cancle-icon relative top-[0px]">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#fff"/></svg>
                      </div>
                      <span className="text-[15px] md:text-[16px] text-[#fff]">Lose emergency calls to competitors</span>
                    </div>
                </div>
                
                <div className="monthly-box-losing rounded-lg p-[12px_15px] text-center border-[1px] border-[#727272] bg-[rgba(0,0,0,0.42)]">
                    <p className="font-semibold text-[15px] sm:text-base text-white">Monthly Loss</p>
                    <p className="text-[15px] sm:text-base font-semibold text-white">$3,000-5,000</p>
                </div>
            </div>

            <div className="card-losing bg-[rgba(21,21,23,0.15)] rounded-[20px] p-[15px] md:p-[20px] border-[1px] border-[#727272]">
                <div className="status-losing bg-[#393B3F] mb-[20px] md:mb-[30px] md:text-[16px] text-[14px] text-[#fff] p-[5px_10px] max-w-fit rounded-[10px]">
                Still Losing
                </div>
                
                <h3 className="text-xl font-semibold text-white md:mb-6 mb-4 leading-tight text-left">With "Traditional" Software</h3>
                 
                <div className="space-y-4 mb-5 md:mb-8 text-left max-w-[280px]">
                    <div className="flex items-start gap-2">
                      <div className="cancle-icon relative top-[0px]">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#fff"/></svg>
                      </div>
                      <span className="text-[15px] md:text-[16px] text-[#fff]">Still miss 1 job per week (no self-booking)</span>
                    </div> 
                    <div className="flex items-start gap-2">
                      <div className="cancle-icon relative top-[0px]">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#fff"/></svg>
                      </div>
                      <span className="text-[15px] md:text-[16px] text-[#fff]">Still waste 1 hour daily on scheduling</span>
                    </div>
                    <div className="flex items-start gap-2">
                      <div className="cancle-icon relative top-[0px]">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.1693 10.4556L13.699 12.9249L16.1693 15.3943C16.239 15.464 16.2943 15.5467 16.332 15.6378C16.3697 15.7288 16.3891 15.8264 16.3891 15.9249C16.3891 16.0235 16.3697 16.1211 16.332 16.2121C16.2943 16.3031 16.239 16.3859 16.1693 16.4556C16.0996 16.5252 16.0169 16.5805 15.9258 16.6182C15.8348 16.6559 15.7372 16.6753 15.6387 16.6753C15.5401 16.6753 15.4425 16.6559 15.3515 16.6182C15.2605 16.5805 15.1777 16.5252 15.1081 16.4556L12.6387 13.9852L10.1693 16.4556C10.0996 16.5252 10.0169 16.5805 9.92585 16.6182C9.8348 16.6559 9.73722 16.6753 9.63868 16.6753C9.54013 16.6753 9.44255 16.6559 9.3515 16.6182C9.26046 16.5805 9.17773 16.5252 9.10805 16.4556C9.03837 16.3859 8.98309 16.3031 8.94538 16.2121C8.90767 16.1211 8.88826 16.0235 8.88826 15.9249C8.88826 15.8264 8.90767 15.7288 8.94538 15.6378C8.98309 15.5467 9.03837 15.464 9.10805 15.3943L11.5784 12.9249L9.10805 10.4556C8.96732 10.3148 8.88826 10.1239 8.88826 9.92493C8.88826 9.7259 8.96732 9.53503 9.10805 9.3943C9.24878 9.25357 9.43965 9.17451 9.63868 9.17451C9.8377 9.17451 10.0286 9.25357 10.1693 9.3943L12.6387 11.8646L15.1081 9.3943C15.1777 9.32462 15.2605 9.26934 15.3515 9.23163C15.4425 9.19392 15.5401 9.17451 15.6387 9.17451C15.7372 9.17451 15.8348 9.19392 15.9258 9.23163C16.0169 9.26934 16.0996 9.32462 16.1693 9.3943C16.239 9.46398 16.2943 9.54671 16.332 9.63775C16.3697 9.7288 16.3891 9.82638 16.3891 9.92493C16.3891 10.0235 16.3697 10.1211 16.332 10.2121C16.2943 10.3031 16.239 10.3859 16.1693 10.4556ZM22.3887 12.9249C22.3887 14.8533 21.8168 16.7384 20.7455 18.3417C19.6742 19.9451 18.1514 21.1948 16.3698 21.9328C14.5883 22.6707 12.6279 22.8638 10.7365 22.4876C8.84523 22.1114 7.10795 21.1828 5.74439 19.8192C4.38082 18.4557 3.45223 16.7184 3.07602 14.8271C2.69981 12.9357 2.8929 10.9753 3.63085 9.19376C4.36881 7.41218 5.61849 5.88944 7.22187 4.8181C8.82525 3.74675 10.7103 3.17493 12.6387 3.17493C15.2237 3.17766 17.7021 4.20576 19.53 6.03365C21.3578 7.86154 22.3859 10.3399 22.3887 12.9249ZM20.8887 12.9249C20.8887 11.2932 20.4048 9.69818 19.4983 8.34147C18.5918 6.98477 17.3033 5.92734 15.7958 5.30292C14.2883 4.6785 12.6295 4.51512 11.0292 4.83345C9.42884 5.15178 7.95883 5.93751 6.80505 7.0913C5.65126 8.24508 4.86553 9.71509 4.5472 11.3154C4.22887 12.9158 4.39225 14.5746 5.01667 16.0821C5.64109 17.5896 6.69852 18.878 8.05522 19.7846C9.41193 20.6911 11.007 21.1749 12.6387 21.1749C14.826 21.1724 16.9229 20.3025 18.4696 18.7558C20.0162 17.2092 20.8862 15.1122 20.8887 12.9249Z" fill="#fff"/></svg>
                      </div>
                      <span className="text-[15px] md:text-[16px] text-[#fff]">Pay $150-300 per <br />user</span>
                    </div>
                </div>
                
                <div className="monthly-box-losing rounded-lg p-[12px_15px] text-center border-[1px] border-[#727272] bg-[rgba(0,0,0,0.42)]">
                    <p className="font-semibold text-[15px] sm:text-base text-white">Monthly Loss</p>
                    <p className="text-[15px] sm:text-base font-semibold text-white">$1,500 + software costs</p>
                </div>
            </div>

            <div className="card-wining">
                <div className='content'>
                <div className="status-wining bg-[#42CF00] mb-[30px] text-[#000] p-[5px_10px] max-w-fit rounded-[10px]">
                Winning
                </div>
                <h3 className="text-xl font-semibold text-white md:mb-6 mb-4 leading-tight text-left">With FieldCamp</h3>
                <div className="space-y-4 mb-5 md:mb-8 text-left max-w-[280px]">
                    <div className="flex items-start gap-2">
                      <div className="cancle-icon relative top-[2px]">
                      <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.92286 0.674805C7.99449 0.674805 6.10943 1.24663 4.50605 2.31798C2.90267 3.38932 1.65298 4.91206 0.91503 6.69364C0.177076 8.47522 -0.0160064 10.4356 0.360199 12.3269C0.736405 14.2183 1.665 15.9555 3.02856 17.3191C4.39213 18.6827 6.12941 19.6113 8.02073 19.9875C9.91204 20.3637 11.8724 20.1706 13.654 19.4326C15.4356 18.6947 16.9583 17.445 18.0297 15.8416C19.101 14.2382 19.6729 12.3532 19.6729 10.4248C19.6701 7.83978 18.642 5.36142 16.8141 3.53353C14.9862 1.70564 12.5079 0.677535 9.92286 0.674805ZM14.2035 8.70543L8.95348 13.9554C8.88383 14.0252 8.80111 14.0805 8.71006 14.1182C8.61901 14.156 8.52142 14.1754 8.42286 14.1754C8.32429 14.1754 8.2267 14.156 8.13565 14.1182C8.0446 14.0805 7.96189 14.0252 7.89223 13.9554L5.64223 11.7054C5.5015 11.5647 5.42244 11.3738 5.42244 11.1748C5.42244 10.9758 5.5015 10.7849 5.64223 10.6442C5.78296 10.5034 5.97383 10.4244 6.17286 10.4244C6.37188 10.4244 6.56275 10.5034 6.70348 10.6442L8.42286 12.3645L13.1422 7.64418C13.2119 7.5745 13.2946 7.51922 13.3857 7.48151C13.4767 7.4438 13.5743 7.42439 13.6729 7.42439C13.7714 7.42439 13.869 7.4438 13.96 7.48151C14.0511 7.51922 14.1338 7.5745 14.2035 7.64418C14.2732 7.71386 14.3284 7.79659 14.3661 7.88763C14.4039 7.97868 14.4233 8.07626 14.4233 8.1748C14.4233 8.27335 14.4039 8.37093 14.3661 8.46198C14.3284 8.55302 14.2732 8.63575 14.2035 8.70543Z" fill="#42CF00"/></svg>  
                      </div>
                      <span className="text-[15px] md:text-[16px] text-[#fff]">Zero missed jobs (AI prevents conflicts)</span>
                    </div> 
                    <div className="flex items-start gap-2">
                    <div className="cancle-icon relative top-[2px]">
                      <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.92286 0.674805C7.99449 0.674805 6.10943 1.24663 4.50605 2.31798C2.90267 3.38932 1.65298 4.91206 0.91503 6.69364C0.177076 8.47522 -0.0160064 10.4356 0.360199 12.3269C0.736405 14.2183 1.665 15.9555 3.02856 17.3191C4.39213 18.6827 6.12941 19.6113 8.02073 19.9875C9.91204 20.3637 11.8724 20.1706 13.654 19.4326C15.4356 18.6947 16.9583 17.445 18.0297 15.8416C19.101 14.2382 19.6729 12.3532 19.6729 10.4248C19.6701 7.83978 18.642 5.36142 16.8141 3.53353C14.9862 1.70564 12.5079 0.677535 9.92286 0.674805ZM14.2035 8.70543L8.95348 13.9554C8.88383 14.0252 8.80111 14.0805 8.71006 14.1182C8.61901 14.156 8.52142 14.1754 8.42286 14.1754C8.32429 14.1754 8.2267 14.156 8.13565 14.1182C8.0446 14.0805 7.96189 14.0252 7.89223 13.9554L5.64223 11.7054C5.5015 11.5647 5.42244 11.3738 5.42244 11.1748C5.42244 10.9758 5.5015 10.7849 5.64223 10.6442C5.78296 10.5034 5.97383 10.4244 6.17286 10.4244C6.37188 10.4244 6.56275 10.5034 6.70348 10.6442L8.42286 12.3645L13.1422 7.64418C13.2119 7.5745 13.2946 7.51922 13.3857 7.48151C13.4767 7.4438 13.5743 7.42439 13.6729 7.42439C13.7714 7.42439 13.869 7.4438 13.96 7.48151C14.0511 7.51922 14.1338 7.5745 14.2035 7.64418C14.2732 7.71386 14.3284 7.79659 14.3661 7.88763C14.4039 7.97868 14.4233 8.07626 14.4233 8.1748C14.4233 8.27335 14.4039 8.37093 14.3661 8.46198C14.3284 8.55302 14.2732 8.63575 14.2035 8.70543Z" fill="#42CF00"/></svg>  
                      </div>
                      <span className="text-[15px] md:text-[16px] text-[#fff]">Customers self-book 40% of <br />jobs</span>
                    </div>
                    <div className="flex items-start gap-2">
                    <div className="cancle-icon relative top-[2px]">
                      <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.92286 0.674805C7.99449 0.674805 6.10943 1.24663 4.50605 2.31798C2.90267 3.38932 1.65298 4.91206 0.91503 6.69364C0.177076 8.47522 -0.0160064 10.4356 0.360199 12.3269C0.736405 14.2183 1.665 15.9555 3.02856 17.3191C4.39213 18.6827 6.12941 19.6113 8.02073 19.9875C9.91204 20.3637 11.8724 20.1706 13.654 19.4326C15.4356 18.6947 16.9583 17.445 18.0297 15.8416C19.101 14.2382 19.6729 12.3532 19.6729 10.4248C19.6701 7.83978 18.642 5.36142 16.8141 3.53353C14.9862 1.70564 12.5079 0.677535 9.92286 0.674805ZM14.2035 8.70543L8.95348 13.9554C8.88383 14.0252 8.80111 14.0805 8.71006 14.1182C8.61901 14.156 8.52142 14.1754 8.42286 14.1754C8.32429 14.1754 8.2267 14.156 8.13565 14.1182C8.0446 14.0805 7.96189 14.0252 7.89223 13.9554L5.64223 11.7054C5.5015 11.5647 5.42244 11.3738 5.42244 11.1748C5.42244 10.9758 5.5015 10.7849 5.64223 10.6442C5.78296 10.5034 5.97383 10.4244 6.17286 10.4244C6.37188 10.4244 6.56275 10.5034 6.70348 10.6442L8.42286 12.3645L13.1422 7.64418C13.2119 7.5745 13.2946 7.51922 13.3857 7.48151C13.4767 7.4438 13.5743 7.42439 13.6729 7.42439C13.7714 7.42439 13.869 7.4438 13.96 7.48151C14.0511 7.51922 14.1338 7.5745 14.2035 7.64418C14.2732 7.71386 14.3284 7.79659 14.3661 7.88763C14.4039 7.97868 14.4233 8.07626 14.4233 8.1748C14.4233 8.27335 14.4039 8.37093 14.3661 8.46198C14.3284 8.55302 14.2732 8.63575 14.2035 8.70543Z" fill="#42CF00"/></svg>  
                      </div>
                      <span className="text-[15px] md:text-[16px] text-[#fff]">Add 2 extra jobs daily from saved time</span> 
                    </div>
                </div>
                <div className="card-wining rounded-lg text-center bg-[rgba(0,0,0,0.42)]">
                    <div className="content !p-[12px_15px]">
                    <p className="font-semibold text-[15px] sm:text-base text-white">Monthly Gain</p>
                    <p className="text-[15px] sm:text-base font-semibold text-white">$4,000+ in new revenue</p>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div className="text-center">
            <a href='https://app.fieldcamp.ai/signup' target='_blank' data-medium="btn-roi-pays-for-itself-in-3-days-1" className="utm-medium-signup roi-button px-[10px] py-[14px] rounded-[7px] text-white font-semibold text-sm transition-all duration-300 bg-[#7239EA] border-[3px] border-[rgba(114,57,234,0.2)] shadow-[0_4px_10px_rgba(0,0,0,0.15)]">
                ROI: Pays for itself in 3 days
            </a>
        </div>
      </div>
    </section>
    <section className='proof-it-works-section  mb-[40px] sm:mb-[50px] md:mb-[60px] lg:mb-[90px]'>
          <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
            <div className="text-center">
              <div className="text-[#232529] text-[14px] md:text-[16px] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-full max-w-fit mx-auto mb-[15px]">Real Results</div>
              <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[25px] md:mb-[40px]">Proof It Works</h2>
            </div>
            <div className="w-full bg-white rounded-[10px] p-[20px] md:p-[45px] border border-[rgba(102,112,133,0.40)] shadow-sm bg-[linear-gradient(20deg,_#fff_0%,_#fff_25%,_#fff_70%,_#B4E2FF_110%)]">
                <div className='mb-[25px] text-left border-b border-[rgba(102,112,133,0.40)] pb-[25px]'>
                  <h3 className='text-[22px] md:text-[28px] font-semibold mb-[8px] leading-[1]'>We fired our dispatcher. In a good way.</h3>
                  <p className='text-[16px]'>Johnson HVAC switched from Housecall Pro six months ago. Results:</p>
                </div>

                <div className="grid md:grid-cols-2 gap-4 md:gap-8">
                    <div className="space-y-4">
                        <div className="flex md:items-center space-x-[10px] md:space-x-[15px]">
                            <div><svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g clip-path="url(#clip0_6449_21731)">
                              <mask id="mask0_6449_21731" style={{maskType: 'luminance'}} maskUnits="userSpaceOnUse" x="0" y="0" width="25" height="25">
                              <path d="M24.5251 0.693848H0.525146V24.6938H24.5251V0.693848Z" fill="white"/>
                              </mask>
                              <g mask="url(#mask0_6449_21731)">
                              <path d="M12.5251 22.6938C18.0251 22.6938 22.5251 18.1938 22.5251 12.6938C22.5251 7.19385 18.0251 2.69385 12.5251 2.69385C7.02515 2.69385 2.52515 7.19385 2.52515 12.6938C2.52515 18.1938 7.02515 22.6938 12.5251 22.6938Z" stroke="#18C27E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              <path d="M8.27515 12.6937L11.1051 15.5237L16.7751 9.86377" stroke="#18C27E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              </g>
                              </g>
                              <defs>
                              <clipPath id="clip0_6449_21731">
                              <rect width="24" height="24" fill="white" transform="translate(0.525146 0.693848)"/>
                              </clipPath>
                              </defs>
                              </svg></div>
                            <span className="text-[16px] text-[#232529] font-medium text-left">40% of jobs now book themselves online</span>
                        </div>
                        <div className="flex md:items-center space-x-[10px] md:space-x-[15px]">
                            <div><svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g clip-path="url(#clip0_6449_21731)">
                              <mask id="mask0_6449_21731" style={{maskType: 'luminance'}} maskUnits="userSpaceOnUse" x="0" y="0" width="25" height="25">
                              <path d="M24.5251 0.693848H0.525146V24.6938H24.5251V0.693848Z" fill="white"/>
                              </mask>
                              <g mask="url(#mask0_6449_21731)">
                              <path d="M12.5251 22.6938C18.0251 22.6938 22.5251 18.1938 22.5251 12.6938C22.5251 7.19385 18.0251 2.69385 12.5251 2.69385C7.02515 2.69385 2.52515 7.19385 2.52515 12.6938C2.52515 18.1938 7.02515 22.6938 12.5251 22.6938Z" stroke="#18C27E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              <path d="M8.27515 12.6937L11.1051 15.5237L16.7751 9.86377" stroke="#18C27E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              </g>
                              </g>
                              <defs>
                              <clipPath id="clip0_6449_21731">
                              <rect width="24" height="24" fill="white" transform="translate(0.525146 0.693848)"/>
                              </clipPath>
                              </defs>
                              </svg></div>
                            <span className="text-[16px] text-[#232529] font-medium text-left">Zero double-bookings (down from 3-4 weekly)</span>
                        </div>
                        <div className="flex md:items-center space-x-[10px] md:space-x-[15px]">
                            <div><svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g clip-path="url(#clip0_6449_21731)">
                              <mask id="mask0_6449_21731" style={{maskType: 'luminance'}} maskUnits="userSpaceOnUse" x="0" y="0" width="25" height="25">
                              <path d="M24.5251 0.693848H0.525146V24.6938H24.5251V0.693848Z" fill="white"/>
                               </mask>
                              <g mask="url(#mask0_6449_21731)">
                              <path d="M12.5251 22.6938C18.0251 22.6938 22.5251 18.1938 22.5251 12.6938C22.5251 7.19385 18.0251 2.69385 12.5251 2.69385C7.02515 2.69385 2.52515 7.19385 2.52515 12.6938C2.52515 18.1938 7.02515 22.6938 12.5251 22.6938Z" stroke="#18C27E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              <path d="M8.27515 12.6937L11.1051 15.5237L16.7751 9.86377" stroke="#18C27E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              </g>
                              </g>
                              <defs>
                              <clipPath id="clip0_6449_21731">
                              <rect width="24" height="24" fill="white" transform="translate(0.525146 0.693848)"/>
                              </clipPath>
                              </defs>
                              </svg></div>
                            <span className="text-[16px] text-[#232529] font-medium text-left">Added 2 extra jobs per day with same crew</span>
                        </div>
                    </div>
                    <div className="space-y-4">
                        <div className="flex md:items-center space-x-[10px] md:space-x-[15px]">
                            <div><svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_6449_21731)">
                                <mask id="mask0_6449_21731" style={{maskType: 'luminance'}} maskUnits="userSpaceOnUse" x="0" y="0" width="25" height="25">
                                <path d="M24.5251 0.693848H0.525146V24.6938H24.5251V0.693848Z" fill="white"/>
                                </mask>
                                <g mask="url(#mask0_6449_21731)">
                                <path d="M12.5251 22.6938C18.0251 22.6938 22.5251 18.1938 22.5251 12.6938C22.5251 7.19385 18.0251 2.69385 12.5251 2.69385C7.02515 2.69385 2.52515 7.19385 2.52515 12.6938C2.52515 18.1938 7.02515 22.6938 12.5251 22.6938Z" stroke="#18C27E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8.27515 12.6937L11.1051 15.5237L16.7751 9.86377" stroke="#18C27E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </g>
                                </g>
                                <defs>
                                <clipPath id="clip0_6449_21731">
                                <rect width="24" height="24" fill="white" transform="translate(0.525146 0.693848)"/>
                                </clipPath>
                                </defs>
                                </svg></div>
                            <span className="text-[16px] text-[#232529] font-medium text-left">Customers pay upfront through the widget</span>
                        </div>
                        <div className="flex md:items-center space-x-[10px] md:space-x-[15px]">
                            <div><svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                  <g clip-path="url(#clip0_6449_21731)">
                                  <mask id="mask0_6449_21731" style={{maskType: 'luminance'}} maskUnits="userSpaceOnUse" x="0" y="0" width="25" height="25">
                                  <path d="M24.5251 0.693848H0.525146V24.6938H24.5251V0.693848Z" fill="white"/>
                                  </mask>
                                  <g mask="url(#mask0_6449_21731)">
                                  <path d="M12.5251 22.6938C18.0251 22.6938 22.5251 18.1938 22.5251 12.6938C22.5251 7.19385 18.0251 2.69385 12.5251 2.69385C7.02515 2.69385 2.52515 7.19385 2.52515 12.6938C2.52515 18.1938 7.02515 22.6938 12.5251 22.6938Z" stroke="#18C27E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                  <path d="M8.27515 12.6937L11.1051 15.5237L16.7751 9.86377" stroke="#18C27E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                  </g>
                                  </g>
                                  <defs>
                                  <clipPath id="clip0_6449_21731">
                                  <rect width="24" height="24" fill="white" transform="translate(0.525146 0.693848)"/>
                                  </clipPath>
                                  </defs>
                                  </svg></div>
                            <span className="text-[16px] text-[#232529] font-medium text-left">Dispatcher now focuses on growth, not scheduling</span>
                        </div>
                    </div>
                </div>
                
                <div className="mt-6 bg-white rounded-[13px] p-4 flex md:items-center space-x-3 border-[1px] border-[#667085]">
                <svg width="67" height="67" viewBox="0 0 67 67" fill="none" xmlns="http://www.w3.org/2000/svg" className="min-w-[40px] min-h-[40px] md:min-w-[67px] md:min-h-[67px]">
                    <g clip-path="url(#clip0_6449_21779)">
                    <mask id="mask0_6449_21779" style={{maskType:"luminance"}} maskUnits="userSpaceOnUse" x="0" y="0" width="67" height="67">
                    <path d="M0.0759277 0.405273H66.0759V66.4053H0.0759277V0.405273Z" fill="white"/>
                    </mask>
                    <g mask="url(#mask0_6449_21779)">
                    <path d="M22.3238 34.4241H9.42629C9.64629 47.2666 12.1763 49.3841 20.0688 54.0591C20.9763 54.6091 21.2788 55.7641 20.7288 56.6991C20.1788 57.6066 19.0238 57.9091 18.0888 57.3591C8.79379 51.8591 5.54879 48.5041 5.54879 32.4991V17.6767C5.54879 12.9742 9.37129 9.1792 14.0463 9.1792H22.2963C27.1363 9.1792 30.7938 12.8367 30.7938 17.6767V25.9267C30.8213 30.7666 27.1638 34.4241 22.3238 34.4241Z" fill="#2E373D" fill-opacity="0.24"/>
                    <path d="M52.0784 34.4241H39.1809C39.4009 47.2666 41.9309 49.3841 49.8234 54.0591C50.7309 54.6091 51.0334 55.7641 50.4834 56.6991C49.9334 57.6066 48.7784 57.9091 47.8434 57.3591C38.5484 51.8591 35.3034 48.5041 35.3034 32.4991V17.6767C35.3034 12.9742 39.1259 9.1792 43.8009 9.1792H52.0509C56.9184 9.1792 60.5759 12.8367 60.5759 17.6767V25.9267C60.5759 30.7666 56.9184 34.4241 52.0784 34.4241Z" fill="#2E373D" fill-opacity="0.24"/>
                    </g>
                    </g>
                    <defs>
                    <clipPath id="clip0_6449_21779">
                    <rect width="66" height="66" fill="white" transform="matrix(-1 0 0 1 66.0759 0.405273)"/>
                    </clipPath>
                    </defs>
                    </svg>
                    <div>
                        <p className="text-[16px] md:text-[18px] text-[#232529] font-normal text-left mb-[5px]">
                            "Monday mornings used to be chaos. Now I wake up to a full, optimized schedule. It's like magic."
                        </p>
                        <p className="text-[16px] md:text-[18px] text-[#232529] font-medium text-left italic">- Mike Johnson, Owner</p>
                    </div>
                </div>
            </div>
          </div>
    </section>

    <section className='proof-it-works-section  bg-[#F3F7FD] py-[40px] md:py-[55px]'>
          <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
            <div className="text-center">
              <div className="text-[#232529] text-[14px] md:text-[16px] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-full max-w-fit mx-auto mb-[15px]">Show Me The Money</div>
              <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[10px] md:mb-[12px]">Your Competitors Are <br />Already Switching</h2>
              <p className="text-[#232529] text-[16px] md:text-[18px] font-normal mb-[25px] md:mb-[40px]">Every Monday, you'll either wake up to</p>
            </div>
            <div className='w-full max-w-[800px] mx-auto'>
            <div className="grid md:grid-cols-2 gap-6 mb-8">
                <div className="bg-[linear-gradient(20deg,_#fff_0%,_#fff_45%,_#E87878_100%)] border-[1px] border-[#C21818] rounded-lg p-[20px] text-center">
                    <div className="mb-4 flex justify-center">
                      <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/07/The-Looser.png" alt="The Looser" width={50} height={50} />
                    </div>
                    <h3 className="text-[20px] md:text-[24px] font-semibold text-[#232529] mb-[10px] md:mb-[15px] max-w-[250px] mx-auto">The Looser</h3>
                    <p className="text-[16px] md:text-[18px] font-normal text-[#232529] max-w-[250px] mx-auto">An empty calendar and a full voicemail box</p>
                </div>
                
                <div className="bg-[linear-gradient(20deg,_#fff_0%,_#fff_45%,_#82E878_100%)] border-[1px] border-[#18C27E] rounded-lg p-[20px] md:p-[20px_20px_30px_20px] text-center">
                    <div className="mb-4 flex justify-center">
                    <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/07/The-Winner.png" alt="The Winner" width={50} height={50}/>
                    </div>
                    <h3 className="text-[20px] md:text-[24px] font-semibold text-[#232529] mb-[10px] md:mb-[15px] max-w-[250px] mx-auto">The Winner</h3>
                    <p className="text-[16px] md:text-[18px] font-normal text-[#232529] max-w-[250px] mx-auto">Or a full calendar with jobs already booked and paid</p>
                </div>
            </div>
            <div className="text-[16px] md:text-[18px] md:p-[30px_20px] p-[20px_15px] font-bold text-[#232529] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-[10px] bg-white"><span className='block w-full max-w-[600px] mx-auto'>The field service companies winning in 2025 let their
            customers book online. Period.</span></div>
            </div>
          </div>
    </section>
    <section className="py-[40px] md:py-[55px] bg-white">
        <div className="max-w-6xl mx-auto px-6">
            <div className="cta-inner-ppc rounded-[15px] p-[40px_20px] md:p-[50px_30px] lg:p-[70px_50px] flex items-center justify-between text-white flex-wrap md:flex-nowrap gap-[20px]">
                <div>
                    <h3 className="text-[24px] lg:text-[32px] leading-[1.25] font-semibold mb-0 pb-[15px] text-left">Already using software?</h3>
                    <p className="text-[16px] lg:text-[18px] font-normal text-left bg-[rgba(255,255,255,0.1)] p-[10px] rounded-[10px]">Try it free. Import your data. See the difference in 24 hours.</p>
                </div>
                <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open text-[16px] md:text-[18px] border-[4px] border-[rgba(255,255,255,0.2)] bg-[radial-gradient(circle_at_center,_#320222_0%,_#000000_100%)] text-white p-[12px_25px] rounded-lg  ransition-colors mr-[0px] lg:mr-[50px]">
                    Book a Call with Us
                </a>
            </div>
        </div>
    </section>

    <section className="pb-16 ppc-faq-section">
        <div className="max-w-full mx-auto px-[15px] lg:px-0 text-center">
            <div className="text-center">
                <div className="text-[#232529] text-[14px] md:text-[16px] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-full max-w-fit mx-auto mb-[15px]">Got Questions</div>
                <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[25px] md:mb-[40px]">Quick Answers to <br />Your Questions</h2>
            </div>
            <div className='max-w-4xl mx-auto'>
              <Accordion items={faqItems}/>
            </div>
        </div>
    </section>
   
    <section className="footer-section-ppc relative md:py-16 pb-[40px] bg-[radial-gradient(circle,_rgba(239,68,68,0.2)_0%,_rgba(168,85,247,0.2)_60%,_transparent_100%)]">
        <div className="max-w-full mx-auto px-[15px] lg:px-0 text-center">
            <div className="text-center">
                <div className="text-[#232529] text-[14px] md:text-[16px] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-full max-w-fit mx-auto mb-[15px]">Final Warning</div>
                <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[25px] md:mb-[40px]">Stop Scheduling Like <br /> It's 1999</h2>
            </div>
            
            <div className="bg-white rounded-[10px] md:p-[40px] p-[20px] border-[1px] border-[#CBCAE4] w-full max-w-[1024px] mx-auto">
                <p className="text-[16px] md:text-[18px] md:leading-[1.12] leading-[1.25] font-medium text-[#232529] md:mb-[35px] mb-[25px] max-w-[800px] mx-auto">
                    In the time it took to read this page, three of your competitors' customers booked jobs online while yours called and got voicemail.
                </p>
                
                <div className="flex flex-col font-normal sm:flex-row gap-4 justify-center max-w-[800px] mx-auto md:mb-[50px] mb-[30px]">
                    <a href="https://app.fieldcamp.ai/signup" target="_blank" data-medium="btn-start-free-trial-2" className="utm-medium-signup bg-[#7239EA] hover:bg-[#7239EA] text-white font-semibold p-[10px_20px] leading-[16px] rounded-lg transition-colors">
                        Start Your Free Trial
                    </a>
                    <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open border border-[#9FACC2] text-[#9FACC2] hover:bg-[#7239EA] hover:text-white p-[10px_20px] leading-[16px] rounded-lg transition-colors">
                        Watch 2 Minute Demo
                    </a>
                </div>
                
                <p className="text-[15px] md:text-[16px] font-normal text-[#9FACC2] mt-6 max-w-[800px] mx-auto">
                    P.S. Every day you wait, you're losing bookings to competitors who let customers schedule online. Monday morning you'll either have a full calendar or an empty one. Your choice.
                </p>
            </div>
        </div>
    </section>



    </div>
  );
}