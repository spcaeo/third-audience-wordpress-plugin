import React from 'react';
import "./module.scss"
import { Metadata } from 'next';
import Accordion from '@/app/_components/Accordion';
import Script from 'next/script';
import { AppendUTMToAnchor, CalendlyEmbed } from '@/app/_components/General/Custom';
import LPForm from '@/app/_components/Form/LP/FormHTML';
import LPFormModalClass from '@/app/_components/Form/LP/LPFormModalClass';

export const metadata: Metadata = {
  title: 'AI Field Service Software | FieldCamp 2025',
  description: 'Streamline your field service operations with AI-powered software. Automate scheduling, dispatching, and job management. Try FieldCamp free—no card required.',
  robots: 'noindex, nofollow',
  alternates: {
    canonical: 'https://fieldcamp.ai/ai-field-service-software/'
  }
};

const faqItems = [
  {
    title: "How is this different from ServiceTitan or Housecall Pro?",
    content: [
      " We built FieldCamp as a complete operating system from day one—not cobbled together through acquisitions. AI-first architecture means it learns and improves, not just stores data."
    ]
  },
  {
    title: "What about our existing data?",
    content: [
      "Full migration included. Every customer, every job history, every custom field. Our AI maps it automatically."
    ]
  },
  {
    title: "Can this really replace multiple systems?",
    content: [
      "Martinez Mechanical replaced 5 systems. GreenFlow HVAC replaced 3. Average customer consolidates 3.7 tools into FieldCamp"
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
      <CalendlyEmbed />
      <AppendUTMToAnchor />
      <LPFormModalClass />
      
      
      
      <section className='project-management-comparison-section py-[70px] md:pb-[90px] lg:pb-[90px] md:pt-[70px] lg:pt-[70px]'>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-8 items-center banner-section">
            
            <div className="w-full d-none-mobile">
              <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/ai-software-ppc-main-img.png" alt="Project Management Interface - Annual Planning" className="w-full h-auto rounded-[20px] shadow-lg" />
            </div>

      
            <div className="space-y-6 text-left">
              <h1 className="text-[32px] md:text-[42px] lg:text-[52px] leading-[1.15] font-bold text-gray-900 text-left">
              AI-Powered Field Service Software That Simply Works<br />
              </h1>

              <div className="w-full d-none-desktop">
              <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/ai-software-ppc-main-img.png" alt="Project Management Interface - Annual Planning" className="w-full h-auto rounded-[20px] shadow-lg" />
            </div>
              
              <p className="text-lg text-gray-700 leading-relaxed text-left">
              One platform that connects everything: scheduling, dispatch,
invoicing, and team management—all powered by AI that learns how you work.
Built for modern field teams ready to scale.
              </p>
              
              <div className="flex flex-col sm:flex-row gap-4 justify-start">
              <a href="https://app.fieldcamp.ai/signup" className="utm-medium-signup inline-flex items-center justify-center gradient-button-ppc px-8 py-3 rounded-full font-medium hover:bg-gray-800 transition-colors">
              Get Started. It's Free!
                </a>
                {/* <button 
                  className="lp-form-trigger inline-flex items-center justify-center gradient-button-ppc px-8 py-3 rounded-full font-medium hover:bg-gray-800 transition-colors" 
                  data-medium="btn-try-for-free"
                >
                Get Started. It's Free!
                </button> */}
                <a href="#" className="lp-form-trigger inline-flex items-center justify-center border-2 border-gray-300 text-gray-700 px-8 py-3 rounded-full font-medium hover:border-gray-400 transition-colors">
                Request a Demo
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className='two-types-section'>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
          <div className="max-w-5xl mx-auto">

            <div className="text-center mb-6 lg:mb-16">
              <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[10px] md:mb-[12px]">Why companies like these choose FieldCamp?</h2>
              <p className="text-[#232529] text-[16px] md:text-[18px] font-normal mb-[25px] md:mb-[40px]">500+ companies automated this month • All trades • 24/7 support
              </p>
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-8 lg:gap-12 items-center justify-items-center">

              <div className="flex items-center justify-center w-full h-16 sm:h-20">
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/02/vertex-prosolutions.png"
                  alt="vertex"
                  className="w-auto object-contain"
                />

              </div>
              <div className="flex items-center justify-center w-full h-16 sm:h-20">
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/02/onpoint.png"
                  alt="onpoint"
                  className="w-auto object-contain"
                />

              </div>
              <div className="flex items-center justify-center w-full h-16 sm:h-20">
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/02/pinacle.png"
                  alt="pinacle"
                  className="w-auto object-contain"
                />

              </div>

              <div className="flex items-center justify-center w-full h-16 sm:h-20">
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/snap-task-logo.png"
                  alt="pinacle"
                  className="w-auto object-contain"
                />

              </div>

            </div>
          </div>
        </div>
      </section>

      <section className='py-[70px] md:py-[90px] lg:py-[90px]'>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
          <div className="max-w-7xl mx-auto">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
              <div className="flex flex-col">
                <div className="rounded-2xl mb-4 shadow-sm overflow-hidden">
                  <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/suggetion-schdule.png"
                    alt="Asana automation workflow"
                    className="w-full h-auto object-cover"
                  />
                </div>
                <div className='text-left'>
                  <h3 className="text-2xl font-bold text-gray-900 mb-4">AI That Learns Your Business</h3>
                  <p className="text-[16px] md:text-[18px] text-[#232529]">
                  AI wherever it counts: recommends ideal schedules, spots trends in your data, & streamlines daily tasks.
                  </p>
                </div>
              </div>

              <div className="flex flex-col">
                <div className="rounded-2xl mb-4 shadow-sm overflow-hidden">
                  <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/client-history-ai.png"
                    alt="Asana automation workflow"
                    className="w-full h-auto object-cover"
                  />
                </div>
                <div className='text-left'>
                  <h3 className="text-2xl font-bold text-gray-900 mb-4">Everything Connected</h3>
                  <p className="text-[16px] md:text-[18px] text-[#232529]">
                  Customer history, job details, inventory, invoices — all in one place. No more app switching archaeology.
                  </p>
                </div>
              </div>

              <div className="flex flex-col">
                <div className="rounded-2xl mb-4 shadow-sm overflow-hidden">
                  <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/trigger-ai.png"
                    alt="Asana automation workflow"
                    className="w-full h-auto object-cover"
                  />
                </div>
                <div className='text-left'>
                  <h3 className="text-2xl font-bold text-gray-900 mb-4">Ease of use</h3>
                  <p className="text-[16px] md:text-[18px] text-[#232529]">
                  From 2 techs to 200 skilled pros across teams and locations. Same platform. Absolutely no growing pains, just seamless scaling.
                  </p>
                </div>
              </div>

            </div>
          </div>
        </div>
      </section>

      {/* Calendar Section */}
      <section className='two-types-section '>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
          <div className="text-center">
            <div className="text-[#232529] text-[14px] md:text-[16px] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-full max-w-fit mx-auto mb-[15px]"> Platform Overview</div>
            <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[25px] md:mb-[40px]">
              Everything you need to run field ops, <br /> nothing you don't.
            </h2>
            <div className="flex-col-reverse md:flex-row flex justify-center gap-[20px] md:gap-[100px] lg:gap-[110px] items-center max-w-[1080px] mx-auto mb-[40px] md:mb-[25px]">
              <div className="text-left max-w-[450px]">
                <h3 className="text-[24px] font-bold text-[#232529] mb-[10px] md:mb-[20px]">What if we need to schedule 50 jobs by 7 AM?</h3>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  Other software makes you click through each one. FieldCamp's AI handles bulk scheduling in seconds—considering skills, location, and preferences. Teams report 75% less time on morning dispatch and 40% better route efficiency.
                </p>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  <a href="https://app.fieldcamp.ai/signup" className="utm-medium-signup gradient-button-ppc flex items-center gap-2 p-[5px_15px] rounded-full max-w-fit">See it work <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" role="graphics-symbol" aria-labelledby="arrow-right-icon-title-id" aria-hidden="false" className="css-pfo72i e1bpwjla1"><title id="arrow-right-icon-title-id">arrow-right icon</title><path fill="currentColor" d="M1.9877 7.23745H12.1877L8.4877 3.53745C8.1877 3.23745 8.1877 2.78745 8.4877 2.48745C8.7877 2.18745 9.2377 2.18745 9.5377 2.48745L14.5377 7.48745C14.8377 7.78745 14.8377 8.23745 14.5377 8.53745L9.5377 13.5375C9.3877 13.6875 9.1877 13.7375 8.9877 13.7375C8.7877 13.7375 8.5877 13.6875 8.4377 13.5375C8.13769 13.2375 8.13769 12.7875 8.4377 12.4875L12.1377 8.78745H1.9877C1.5877 8.78745 1.2377 8.43745 1.2377 8.03745C1.2377 7.63745 1.5877 7.23745 1.9877 7.23745Z"></path></svg></a>
                </p>
              </div>
              <div className="bg-white">
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/smart-schdule.png" alt="50jobs-ai-imh" />
              </div>
            </div>
            <div className="flex-col-reverse md:flex-row-reverse flex justify-center gap-[20px] md:gap-[100px] lg:gap-[110px] items-center max-w-[1080px] mx-auto mb-[40px] md:mb-[25px]">
              <div className="text-left max-w-[450px]">
                <h3 className="text-[24px] font-bold text-[#232529] mb-[10px] md:mb-[20px]">My team needs real-time visibility. Can FieldCamp deliver?</h3>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  With live GPS tracking, instant status updates, and one-click communication,
                  everyone stays connected. Know where every tech is, what they're doing, and
                  when they'll finish. Plus 99.9% uptime means it works when you need it.
                </p>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  <a href="https://app.fieldcamp.ai/signup" className="utm-medium-signup gradient-button-ppc flex items-center gap-2 p-[5px_15px] rounded-full max-w-fit">Try for free <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" role="graphics-symbol" aria-labelledby="arrow-right-icon-title-id" aria-hidden="false" className="css-pfo72i e1bpwjla1"><title id="arrow-right-icon-title-id">arrow-right icon</title><path fill="currentColor" d="M1.9877 7.23745H12.1877L8.4877 3.53745C8.1877 3.23745 8.1877 2.78745 8.4877 2.48745C8.7877 2.18745 9.2377 2.18745 9.5377 2.48745L14.5377 7.48745C14.8377 7.78745 14.8377 8.23745 14.5377 8.53745L9.5377 13.5375C9.3877 13.6875 9.1877 13.7375 8.9877 13.7375C8.7877 13.7375 8.5877 13.6875 8.4377 13.5375C8.13769 13.2375 8.13769 12.7875 8.4377 12.4875L12.1377 8.78745H1.9877C1.5877 8.78745 1.2377 8.43745 1.2377 8.03745C1.2377 7.63745 1.5877 7.23745 1.9877 7.23745Z"></path></svg></a>
                </p>
              </div>

              <div className="bg-white">
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/teams-need-ai.png" alt="deleveref-img" />
              </div>
            </div>
            <div className="flex-col-reverse md:flex-row flex justify-center gap-[20px] md:gap-[100px] lg:gap-[110px] items-center max-w-[1080px] mx-auto mb-[40px] md:mb-[25px]">
              <div className="text-left max-w-[450px]">
                <h3 className="text-[24px] font-bold text-[#232529] mb-[10px] md:mb-[20px]">What about all our customer history?</h3>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  Every job, note, and equipment detail follows the customer—not scattered across
                  systems. Techs see everything on-site, office tracks patterns, and nothing falls
                  through cracks. Teams report 45% fewer repeat visits.
                </p>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  <a href="https://app.fieldcamp.ai/signup" className="utm-medium-signup gradient-button-ppc flex items-center gap-2 p-[5px_15px] rounded-full max-w-fit">See Customer 360 <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" role="graphics-symbol" aria-labelledby="arrow-right-icon-title-id" aria-hidden="false" className="css-pfo72i e1bpwjla1"><title id="arrow-right-icon-title-id">arrow-right icon</title><path fill="currentColor" d="M1.9877 7.23745H12.1877L8.4877 3.53745C8.1877 3.23745 8.1877 2.78745 8.4877 2.48745C8.7877 2.18745 9.2377 2.18745 9.5377 2.48745L14.5377 7.48745C14.8377 7.78745 14.8377 8.23745 14.5377 8.53745L9.5377 13.5375C9.3877 13.6875 9.1877 13.7375 8.9877 13.7375C8.7877 13.7375 8.5877 13.6875 8.4377 13.5375C8.13769 13.2375 8.13769 12.7875 8.4377 12.4875L12.1377 8.78745H1.9877C1.5877 8.78745 1.2377 8.43745 1.2377 8.03745C1.2377 7.63745 1.5877 7.23745 1.9877 7.23745Z"></path></svg></a>
                </p>
              </div>

              <div className="bg-white">
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/customer-history-ai.png" alt="hostory-imhg" />
              </div>
            </div>


            <div className="flex-col-reverse md:flex-row-reverse flex justify-center gap-[20px] md:gap-[100px] lg:gap-[110px] items-center max-w-[1080px] mx-auto mb-[40px] md:mb-[25px]">
              <div className="text-left max-w-[450px]">
                <h3 className="text-[24px] font-bold text-[#232529] mb-[10px] md:mb-[20px]">We're drowning in paperwork. Does this actually help?</h3>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  Digital forms, instant invoicing, and mobile payments eliminate paper trails. What
                  took hours now takes minutes. Get paid 3x faster, close jobs on-site, and never
                  chase signatures again.
                </p>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  <a href="https://app.fieldcamp.ai/signup" className="utm-medium-signup gradient-button-ppc flex items-center gap-2 p-[5px_15px] rounded-full max-w-fit">Calculate time saved <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" role="graphics-symbol" aria-labelledby="arrow-right-icon-title-id" aria-hidden="false" className="css-pfo72i e1bpwjla1"><title id="arrow-right-icon-title-id">arrow-right icon</title><path fill="currentColor" d="M1.9877 7.23745H12.1877L8.4877 3.53745C8.1877 3.23745 8.1877 2.78745 8.4877 2.48745C8.7877 2.18745 9.2377 2.18745 9.5377 2.48745L14.5377 7.48745C14.8377 7.78745 14.8377 8.23745 14.5377 8.53745L9.5377 13.5375C9.3877 13.6875 9.1877 13.7375 8.9877 13.7375C8.7877 13.7375 8.5877 13.6875 8.4377 13.5375C8.13769 13.2375 8.13769 12.7875 8.4377 12.4875L12.1377 8.78745H1.9877C1.5877 8.78745 1.2377 8.43745 1.2377 8.03745C1.2377 7.63745 1.5877 7.23745 1.9877 7.23745Z"></path></svg></a>
                </p>
              </div>

              <div className="bg-white">
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/fieldpro-expert.png" alt="fieldpro-expert" />
              </div>
            </div>
            <div className="flex-col-reverse md:flex-row flex justify-center gap-[20px] md:gap-[100px] lg:gap-[110px] items-center max-w-[1080px] mx-auto mb-[40px] md:mb-[25px]">
              <div className="text-left max-w-[450px]">
                <h3 className="text-[24px] font-bold text-[#232529] mb-[10px] md:mb-[20px]">Can we really be up and running fast?</h3>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  Most teams go live in 48 hours. We import your data, your team watches one
                  video, and you're scheduling jobs. No consultants, no training weeks. Teams see
                  ROI in week one.
                </p>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  <a href="https://app.fieldcamp.ai/signup" className="utm-medium-signup gradient-button-ppc flex items-center gap-2 p-[5px_15px] rounded-full max-w-fit">Start today <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" role="graphics-symbol" aria-labelledby="arrow-right-icon-title-id" aria-hidden="false" className="css-pfo72i e1bpwjla1"><title id="arrow-right-icon-title-id">arrow-right icon</title><path fill="currentColor" d="M1.9877 7.23745H12.1877L8.4877 3.53745C8.1877 3.23745 8.1877 2.78745 8.4877 2.48745C8.7877 2.18745 9.2377 2.18745 9.5377 2.48745L14.5377 7.48745C14.8377 7.78745 14.8377 8.23745 14.5377 8.53745L9.5377 13.5375C9.3877 13.6875 9.1877 13.7375 8.9877 13.7375C8.7877 13.7375 8.5877 13.6875 8.4377 13.5375C8.13769 13.2375 8.13769 12.7875 8.4377 12.4875L12.1377 8.78745H1.9877C1.5877 8.78745 1.2377 8.43745 1.2377 8.03745C1.2377 7.63745 1.5877 7.23745 1.9877 7.23745Z"></path></svg></a>
                </p>
              </div>

              <div className="bg-white">
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/my-client-ai.png" alt="my-client-img" />
              </div>
            </div>

            <div className="flex-col-reverse md:flex-row-reverse flex justify-center gap-[20px] md:gap-[100px] lg:gap-[110px] items-center max-w-[1080px] mx-auto md:mb-[25px]">
              <div className="text-left max-w-[450px]">
                <h3 className="text-[24px] font-bold text-[#232529] mb-[10px] md:mb-[20px]">What if we outgrow it?
                </h3>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  From 2 techs to 200, same platform, same price per user. No modules to buy, no
                  features locked behind tiers. Just add users as you grow. Built to scale without
                  complexity.

                </p>
                <p className="text-[16px] text-[#232529] mb-[10px] md:mb-6">
                  <a href="https://app.fieldcamp.ai/signup" className="utm-medium-signup gradient-button-ppc flex items-center gap-2 p-[5px_15px] rounded-full max-w-fit">See pricing <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" role="graphics-symbol" aria-labelledby="arrow-right-icon-title-id" aria-hidden="false" className="css-pfo72i e1bpwjla1"><title id="arrow-right-icon-title-id">arrow-right icon</title><path fill="currentColor" d="M1.9877 7.23745H12.1877L8.4877 3.53745C8.1877 3.23745 8.1877 2.78745 8.4877 2.48745C8.7877 2.18745 9.2377 2.18745 9.5377 2.48745L14.5377 7.48745C14.8377 7.78745 14.8377 8.23745 14.5377 8.53745L9.5377 13.5375C9.3877 13.6875 9.1877 13.7375 8.9877 13.7375C8.7877 13.7375 8.5877 13.6875 8.4377 13.5375C8.13769 13.2375 8.13769 12.7875 8.4377 12.4875L12.1377 8.78745H1.9877C1.5877 8.78745 1.2377 8.43745 1.2377 8.03745C1.2377 7.63745 1.5877 7.23745 1.9877 7.23745Z"></path></svg></a>
                </p>
              </div>

              <div className="bg-white">
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/outgro-ai.png" alt="add-team-member-imh" />
              </div>
            </div>

          </div>
        </div>
      </section>

      <section className="relative py-[70px] md:py-[90px] lg:py-[90px]">
        <div className="max-w-4xl mx-auto text-center">
          <blockquote className="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 leading-tight mb-8">
            "We replaced ServiceTitan, QuickBooks, and three
            other tools with FieldCamp. Saved $1,100/month and
            actually increased functionality."
          </blockquote>
          <div className="text-gray-600">
            <p className="text-sm">- Martinez Mechanical, 45 techs</p>
          </div>
        </div>
       
      </section>

      {/* Calendar Section */}
      <section className='two-types-section'>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
          <div className="text-center">
            <div className="text-[#232529] text-[14px] md:text-[16px] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-full max-w-fit mx-auto mb-[15px]">The Comparison</div>
            <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[25px] md:mb-[40px]">Why FieldCamp Replaced <br />  Their Old Software</h2>
            <div className='max-w-[1080px] mx-auto'>
              <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/06/Why-FieldCamp-Replaced.png" alt="" />
            </div>
          </div>
        </div>
      </section>


      <section className='proof-it-works-section py-[70px] md:py-[90px] lg:py-[90px]'>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
          <div className="text-center">
            <div className="text-[#232529] text-[14px] md:text-[16px] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-full max-w-fit mx-auto mb-[15px]">Implementation</div>
            <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[10px] md:mb-[12px]">Migration Without the Migraine </h2>
            <p className="text-[#232529] text-[16px] md:text-[18px] font-normal mb-[25px] md:mb-[40px]">Switch Without Stress</p>
          </div>
          <div className='w-full max-w-[800px] mx-auto'>
            <div className="grid md:grid-cols-2 gap-6 mb-8">
              <div className="bg-[linear-gradient(20deg,_#fff_0%,_#fff_45%,_#E87878_100%)] border-[1px] border-[#C21818] rounded-lg p-[20px] text-center">
                <div className="mb-4 flex justify-center">
                <span className="step-emoji" style={{fontSize: '40px', width: '50px', height: '50px'}}>📥</span>
                  {/* <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/07/The-Looser.png" alt="The Looser" width={50} height={50} /> */}
                </div>
                <h3 className="text-[20px] md:text-[24px] font-semibold text-[#232529] mb-[10px] md:mb-[15px] max-w-[250px] mx-auto">Import Everything</h3>
                <p className="text-[16px] md:text-[18px] font-normal text-[#232529] max-w-[250px] mx-auto">Your data, customers, history—it all comes over</p>
              </div>

              <div className="bg-[linear-gradient(20deg,_#fff_0%,_#fff_45%,_#82E878_100%)] border-[1px] border-[#18C27E] rounded-lg p-[20px] md:p-[20px_20px_30px_20px] text-center">
                <div className="mb-4 flex justify-center">
                <span className="step-emoji" style={{fontSize: '35px', width: '50px', height: '50px'}}>🔧</span>
                  {/* <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/07/The-Winner.png" alt="The Winner" width={50} height={50} /> */}
                </div>
                <h3 className="text-[20px] md:text-[24px] font-semibold text-[#232529] mb-[10px] md:mb-[15px] max-w-[250px] mx-auto">Parallel Run</h3>
                <p className="text-[16px] md:text-[18px] font-normal text-[#232529] max-w-[250px] mx-auto">Test everything before switching</p>
              </div>
            </div>
            <div className="text-[16px] md:text-[18px] md:p-[30px_20px] p-[20px_15px] font-bold text-[#232529] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-[10px] bg-white"><span className='block w-full max-w-[600px] mx-auto'>The field service companies winning in 2025 make migration seamless. Period
              </span></div>
          </div>
        </div>
      </section>

      <LPForm />
      

      <section className="ppc-faq-section py-[70px] md:pt-[90px] lg:pt-[90px] md:pb-[20px] lg:pb-[20px]">
        <div className="max-w-full mx-auto px-[15px] lg:px-0 text-center">
          <div className="text-center">
            <div className="text-[#232529] text-[14px] md:text-[16px] border-[1px] border-[rgba(35,37,41,0.2)] p-[5px_15px] rounded-full max-w-fit mx-auto mb-[15px]">Got Questions</div>
            <h2 className="text-[30px] md:text-[42px] lg:text-[52px] leading-[1.15] font-semibold text-[#232529] mb-[25px] md:mb-[40px]">The Questions Every <br /> Smart Buyer Asks</h2>
          </div>
          <div className='max-w-4xl mx-auto'>
            <Accordion items={faqItems} />
          </div>
        </div>
      </section>

      <section className="py-[20px] md:py-[60px]  bg-[#F3F7FD] bottom-cta-mb">
          <div className="max-w-6xl mx-auto px-6">
            <p className='bottomcta'><button className="utm-medium-signup lp-form-trigger inline-flex items-center justify-center bg-black text-white px-8 py-3 rounded-full font-medium hover:bg-gray-800 transition-colors" data-medium="btn-try-for-free">Book a Demo</button></p>
          </div>
      </section>

    </div>
  );
}