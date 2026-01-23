import React from 'react';
import "./module.scss"
import { Metadata } from 'next';
import Script from 'next/script';
import { AppendUTMToAnchor, CalendlyEmbed } from '@/app/_components/General/Custom';
import LPFormModalClass from '@/app/_components/Form/LP/LPFormModalClass';
import DemoForm from '@/app/_components/Form/Demo/DemoForm';
import WorkflowTab from './WorkflowTab';
import ExampleWorkTab from './ExampleWorkTab';
import ROICalculator from './ROICalculator';
import LawnCareExitIntentPopup from './LawnCareExitIntentPopup';

export const metadata: Metadata = {
  title: 'Lawn Care Software | FieldCamp 2025',
  description: 'The best lawn care software for managing your lawn care business',
  robots: 'noindex, nofollow',
  alternates: {
    canonical: 'https://fieldcamp.ai/lawn-care-software/'
  }
};

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
  }
];

export default function LawnCareSoftwarePage() {

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
      <LawnCareExitIntentPopup />

      {/* Hero Section */}
      <section className='hero-section-ppc-ln py-[50px] md:py-[70px] lg:py-[90px]'>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-15px'>
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">

            {/* Left Content */}
            <div className="space-y-6">
              <h1 className="text-[32px] md:text-[42px] lg:text-[52px] leading-[1.15] font-bold">
                Cut 43% More Lawns <br />
                With 68% Less Windshield Time
              </h1>

              <p className="text-[18px] md:text-[20px] leading-relaxed max-w-[500px] text-gray-600">
                Join 400+ lawn care companies saving 12 hours/week on routing, scheduling, and invoicing
              </p>

              {/* Benefits List */}
              <div className="space-y-3 max-w-[500px]">
                <div className="flex items-center gap-3">
                  <svg className="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                  </svg>
                  <span className="text-[16px] md:text-[18px] font-medium">Quote any property in 45 seconds</span>
                </div>
                <div className="flex items-center gap-3">
                  <svg className="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                  </svg>
                  <span className="text-[16px] md:text-[18px] font-medium">Route 8 crews across 60 stops optimally</span>
                </div>
                <div className="flex items-center gap-3">
                  <svg className="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                  </svg>
                  <span className="text-[16px] md:text-[18px] font-medium">Invoice before leaving the driveway</span>
                </div>
                <div className="flex items-center gap-3">
                  <svg className="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                  </svg>
                  <span className="text-[16px] md:text-[18px] font-medium">Track fertilizer & equipment in real-time</span>
                </div>
              </div>

              {/* Buttons Side by Side */}
              <div className="flex flex-col sm:flex-row gap-4 items-center sm:items-start">
                <a href="https://calendly.com/jeel-fieldcamp/30min" style={{ height: "52px" }} className="calendly-open inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity shadow-lg">
                Book a Demo
                </a>
                <a href="tel:+18564602850" style={{ height: "52px" }} className="inline-flex items-center justify-center border-2 border-gray-300 hover:border-gray-400 px-6 py-3 rounded-xl font-medium transition-colors text-gray-700 hover:text-gray-900">
                  <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path fillRule="evenodd" d="M1.5 4.5a3 3 0 013-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 01-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 006.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 011.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 01-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5z" clipRule="evenodd" />
                  </svg>
                  +1 856-460-2850
                </a>
              </div>

            </div>

            <div className="relative hidden md:flex">
              <img
                src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lawnppc-banner-img-scaled.png"
                alt="vertex"
                className="w-auto object-contain"
              />
            </div>
            <div className="relative md:hidden">
              <img
                src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lawnppc-banner-img-scaled.png"
                alt="vertex"
                className="w-auto object-contain"
              />
            </div>
          </div>
        </div>
      </section>

      {/* Logo New PPC Section */}
      <section className='logo-new-ppc pb-160 py-[50px] bg-white'>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-[15px]'>
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-8 items-center logo-new-ppc-sec">

            {/* Title - First on mobile, left side on desktop */}
            <div className="order-1 lg:order-1 lg:col-span-5">
              <h2 className="text-[24px] md:text-[36px] lg:text-[32px] font-semibold md:font-bold leading-[1.3] md:leading-[1.2] mb-8 md:mb-0 text-center lg:text-left">
                Why lawn care companies <br className="" />
                choose FieldCamp
              </h2>
            </div>


            <div className="order-2 lg:order-2 lg:col-span-7">
              <div className="md:flex flex-row items-center justify-between gap-6 md:gap-6 lg:gap-8 logo-new-bx">
                <div className="flex items-center justify-center flex-1">
                  <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/greenedge-nw.png"
                    alt="greenedge"
                    className="h-[50px] md:h-[60px] w-auto object-contain"
                  />
                </div>

                <div className="flex items-center justify-center flex-1">
                  <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/lawnlift-sl-nw.png"
                    alt="lawnlift"
                    className="h-[50px] md:h-[60px] w-auto object-contain"
                  />
                </div>

                <div className="flex items-center justify-center flex-1">
                  <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/grass-ctraft-nw.png"
                    alt="grass-ctraft"
                    className="h-[50px] md:h-[60px] w-auto object-contain"
                  />
                </div>

                <div className="flex items-center justify-center flex-1 md:hidden">
                  <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/snap-task-logo-nw.png"
                    alt="grass-ctraft"
                    className="h-[50px] md:h-[60px] w-auto object-contain"
                  />
                </div>
              </div>


            </div>

          </div>
        </div>
      </section>

      {/* 3-Step Process Section */}
      <section className='three-step-process bg-white'>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-[15px]'>
          {/* Section Header */}
          <div className="text-center mb-16">
            <h2 className="text-[24px] md:text-[30px] lg:text-[32px] font-bold leading-[1.3] mb-6">
              Your Entire Business <br />
              Automated in 3 Steps
            </h2>
          </div>

          {/* 3 Steps Grid */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12 mobile-clmn">

            {/* Step 1 */}
            <div className="text-center client-bx">
              <div className="mb-6">
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/impprt-cl-lawn.svg"
                  alt="grass-ctraft"
                  className=""
                />
              </div>
              <h3 className="text-[20px] md:text-[24px] font-bold mb-4">
                Upload your customer list or connect QuickBooks
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-500">
                98 lawn care companies imported in under 5 minutes
              </p>
            </div>

            {/* Step 2 */}
            <div className="text-center client-bx">
              <div className="mb-6">
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/bussiness-step-2.svg"
                  alt="grass-ctraft"
                  className=""
                />
              </div>
              <h3 className="text-[20px] md:text-[24px] font-bold mb-4">
                Drag properties onto crew routes
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-500">
                Average user schedules 75 properties in first session
              </p>
            </div>

            {/* Step 3 */}
            <div className="text-center client-bx">
              <div className="mb-6">
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lawn-busioness-step-3.svg"
                  alt="grass-ctraft"
                  className=""
                />
              </div>
              <h3 className="text-[20px] md:text-[24px] font-bold mb-4">
                Auto-invoice when service completes
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-500">
                $12,380 average first month revenue tracked
              </p>
            </div>

          </div>

        </div>
      </section>

      {/* Workflow Tab Section (formerly Tabbed Interface Section) */}
      <WorkflowTab />

      {/* Features Section - Everything Connected */}
      <section className='features-connected-section'>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-[15px]'>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12 feature-section-bx">

            {/* Feature 1 - Everything Connected */}
            <div className="text-center space-y-4">
              <div className="inline-flex items-center justify-center mx-auto mb-4">
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/evryic-1.png"
                  alt="Everything Connected"
                  className="w-[80px] h-[80px] md:w-[80px] md:h-[80px] object-contain"
                />
              </div>
              <h3 className="text-[20px] md:text-[24px] font-bold">
                Everything connected
              </h3>
              <p className="text-[18px] md:text-[18px] leading-relaxed max-w-[320px] mx-auto">
                Gate codes sync to crew phones instantly. Service triggers invoices automatically. One profile: property size, cut height, obstacles.
              </p>
            </div>

            {/* Feature 2 - Built for Lawn Care Reality */}
            <div className="text-center space-y-4">
              <div className="inline-flex items-center justify-center mx-auto mb-4">
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/evryic-2.png"
                  alt="Built for Lawn Care"
                  className="w-[80px] h-[80px] md:w-[80px] md:h-[80px] object-contain"
                />
              </div>
              <h3 className="text-[20px] md:text-[24px] font-bold">
                Built for lawn care reality
              </h3>
              <p className="text-[18px] md:text-[18px] leading-relaxed max-w-[320px] mx-auto">
                Handles weekly, bi-weekly, skip weeks. Crews stay in neighborhoods—no zigzagging.
              </p>
            </div>

            {/* Feature 3 - Simple Enough to Trust */}
            <div className="text-center space-y-4">
              <div className="inline-flex items-center justify-center mx-auto mb-4">
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/evryic-3.png"
                  alt="Simple to Trust"
                  className="w-[80px] h-[80px] md:w-[80px] md:h-[80px] object-contain"
                />
              </div>
              <h3 className="text-[20px] md:text-[24px] font-bold">
                Simple enough for everyone              </h3>
              <p className="text-[18px] md:text-[18px] leading-relaxed max-w-[320px] mx-auto">
                Crews need no training, just addresses. Shuffle properties between routes instantly. Preview changes before confirming.
              </p>
            </div>

          </div>
        </div>
      </section>

      {/* AI Features Section */}
      <section className='ai-features-section pt-160 bg-white'>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-[15px]'>

          {/* Header */}
          <div className="mb-16">
            <h2 className="text-[26px] md:text-[30px] lg:text-[32px] font-bold leading-[1.3] mb-6">
              How are we different than other software?
            </h2>
            <p className="text-[18px] md:text-[18px] leading-relaxed max-w-[600px] mx-auto text-center text-gray-600">
              We focus exclusively on AI features that transform how your lawn care business operates. Here's what sets us apart:
            </p>
          </div>

          {/* AI Features Boxes */}
          <div className="space-y-12">

            <div className="desc-bx-recept bg-gray-50 border border-gray-200 rounded-2xl">
              <div className='recptleft-bx bx-flex'>
                <div className="flex items-center gap-4 recept-title">
                  <div className="w-10 h-10 bg-black text-white rounded-full flex items-center justify-center font-bold text-lg">
                    1
                  </div>
                  <span className="text-gray-800 text-sm font-medium uppercase tracking-wide">AI RECEPTIONIST</span>
                </div>
                <div className="recpt-body-desc">
                  <h3 className="text-[26px] md:text-[30px] font-bold leading-[1.2] text-gray-900 text-left">
                  Never miss another estimate request
                  </h3>
                  <ul className="space-y-3 text-[16px] leading-relaxed text-gray-700 mb-8">
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>Answers in 2 rings, 24/7/365</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>Books estimates directly in calendar</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"Can you cut my lawn tomorrow?"</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>Knows your service area & pricing</span>
                    </li>
                  </ul>
                </div>
                <div className='recpt-link-btn'>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className='btn-linkk-recpt calendly-open font-bold'>Learn more &nbsp;→</a>
                </div>
              </div>

              <div className='recpt-right-bx bx-flex'>
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/estimate-rew-lawn.png"
                  alt="Simple to Trust"
                  className="m-auto text-center"
                  style={{ backgroundColor: "#EDEDED" }}
                />
              </div>

            </div>

            <div className="desc-bx-recept bg-gray-50 border border-gray-200 rounded-2xl">
              <div className='recptleft-bx bx-flex'>
                <div className="flex items-center gap-4 recept-title">
                  <div className="w-10 h-10 bg-black text-white rounded-full flex items-center justify-center font-bold text-lg">
                    2
                  </div>
                  <span className="text-gray-800 text-sm font-medium uppercase tracking-wide">SMART BOOKING WIDGET</span>
                </div>
                <div className="recpt-body-desc">
                  <h3 className="text-[26px] md:text-[30px] font-bold leading-[1.2] text-gray-900 text-left">
                  Your website becomes a 24/7 sales machine
                  </h3>
                  <ul className="space-y-3 text-[16px] leading-relaxed text-gray-700 mb-8">
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>Live booking form on phone screen</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>Service selection (weekly/bi-weekly)</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"5 new customers while mowing"</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>Knows your service area & pricing</span>
                    </li>
                  </ul>
                </div>
                <div className='recpt-link-btn'>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className='btn-linkk-recpt calendly-open font-bold'>Learn more &nbsp;→</a>
                </div>
              </div>

              <div className='recpt-right-bx bx-flex'>
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/sales-machine-lawnppc-img.png"
                  alt="Simple to Trust"
                  className="m-auto text-center"
                  style={{ backgroundColor: "#EDEDED", padding: "25px" }}
                />
              </div>

            </div>

            <div className="desc-bx-recept bg-gray-50 border border-gray-200 rounded-2xl">
              <div className='recptleft-bx bx-flex'>
                <div className="flex items-center gap-4 recept-title">
                  <div className="w-10 h-10 bg-black text-white rounded-full flex items-center justify-center font-bold text-lg">
                    3
                  </div>
                  <span className="text-gray-800 text-sm font-medium uppercase tracking-wide">COMMAND CENTER AI</span>
                </div>
                <div className="recpt-body-desc">
                  <h3 className="text-[26px] md:text-[30px] font-bold leading-[1.2] text-gray-900 text-left">
                  Ask and do anything about your business
                  </h3>
                  <ul className="space-y-3 text-[16px] leading-relaxed text-gray-700 mb-8">
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"Which properties haven't been serviced in 30 days?"</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"Create invoice for Johnson property"</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"Schedule fertilizer treatment for zone 3"</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"Who's my fastest crew?"</span>
                    </li>
                  </ul>
                </div>
                <div className='recpt-link-btn'>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className='btn-linkk-recpt calendly-open font-bold'>Learn more &nbsp;→</a>
                </div>
              </div>

              <div className='recpt-right-bx bx-flex'>
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/ai-rececptionist-3.svg"
                  alt="Simple to Trust"
                  className="m-auto text-center"
                  style={{ padding: "25px" }}
                />
              </div>

            </div>

           
            {/* <div className="bg-gray-50 border border-gray-200 rounded-2xl">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start h-full mobile-clmn items-center">
                <div className="space-y-6 p-6">
                  <div className="flex items-center gap-4">
                    <div className="w-10 h-10 bg-black text-white rounded-full flex items-center justify-center font-bold text-lg">
                      1
                    </div>
                    <span className="text-gray-800 text-sm font-medium uppercase tracking-wide">AI RECEPTIONIST</span>
                  </div>
                  <h3 className="text-[26px] md:text-[30px] font-bold leading-[1.2] text-gray-900 text-left">
                    Never miss another estimate request
                  </h3>
                  <ul className="space-y-3 text-[16px] leading-relaxed text-gray-700 mb-8" style={{ marginLeft: "15px" }}>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>Answers in 2 rings, 24/7/365</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>Books estimates directly in calendar</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"Can you cut my lawn tomorrow?"</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>Knows your service area & pricing</span>
                    </li>
                  </ul>
                  <div className="flex items-start gap-3">
                    <div className="w-5 h-5 flex-shrink-0 mt-0.5"></div>
                    <a
                      href="https://calendly.com/jeel-fieldcamp/30min" style={{ height: "52px" }}
                      className="calendly-open inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity shadow-lg"
                    >
                      Get your receptionist
                    </a>
                  </div>
                </div>
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/estimate-rew-lawn.png"
                  alt="Simple to Trust"
                  className="w-full h-full"
                  style={{ backgroundColor: "#EDEDED" }}
                />
              </div>
            </div>

           
            <div className="bg-gray-50 border border-gray-200 rounded-2xl">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start h-full mobile-clmn items-center">
                <div className="space-y-6 p-6">
                  <div className="flex items-center gap-4">
                    <div className="w-10 h-10 bg-black text-white rounded-full flex items-center justify-center font-bold text-lg">
                      2
                    </div>
                    <span className="text-gray-800 text-sm font-medium uppercase tracking-wide">SMART BOOKING WIDGET</span>
                  </div>
                  <h3 className="text-[26px] md:text-[30px] font-bold leading-[1.2] text-gray-900 text-left">
                    Your website becomes a 24/7 sales machine
                  </h3>
                  <ul className="space-y-3 text-[16px] leading-relaxed text-gray-700 mb-8" style={{ marginLeft: "15px" }}>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>Live booking form on phone screen</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>Service selection (weekly/bi-weekly)</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"5 new customers while mowing"</span>
                    </li>
                  </ul>
                  <div className="flex items-start gap-3">
                    <div className="w-5 h-5 flex-shrink-0 mt-0.5"></div>
                    <a
                      href="https://calendly.com/jeel-fieldcamp/30min" style={{ height: "52px" }}
                      className="calendly-open inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity shadow-lg"
                    >
                      Add Widget to your website
                    </a>
                  </div>
                </div>
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/sales-machine-lawnppc-img.png"
                  alt="Simple to Trust"
                  className="m-auto text-center"
                  style={{ backgroundColor: "#EDEDED", padding: "25px" }}
                />
              </div>
            </div>

           
            <div className="bg-gray-50 border border-gray-200 rounded-2xl">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start h-full mobile-clmn items-center">
                <div className="space-y-6 p-6">
                  <div className="flex items-center gap-4">
                    <div className="w-10 h-10 bg-black text-white rounded-full flex items-center justify-center font-bold text-lg">
                      3
                    </div>
                    <span className="text-gray-800 text-sm font-medium uppercase tracking-wide">COMMAND CENTER AI</span>
                  </div>
                  <h3 className="text-[26px] md:text-[30px] font-bold leading-[1.2] text-gray-900 text-left">
                    Ask and do anything about your business
                  </h3>
                  <ul className="space-y-3 text-[16px] leading-relaxed text-gray-700 mb-8" style={{ marginLeft: "15px" }}>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"Which properties haven't been serviced in 30 days?"</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"Create invoice for Johnson property"</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"Schedule fertilizer treatment for zone 3"</span>
                    </li>
                    <li className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span>"Who's my fastest crew?"</span>
                    </li>
                  </ul>
                  <div className="flex items-start gap-3">
                    <div className="w-5 h-5 flex-shrink-0 mt-0.5"></div>
                    <a
                      href="https://calendly.com/jeel-fieldcamp/30min" style={{ height: "52px" }}
                      className="calendly-open inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity shadow-lg"
                    >
                      Talk to your business
                    </a>
                  </div>
                </div>
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/ai-rececptionist-3.svg"
                  alt="Simple to Trust"
                  className="w-full h-full"
                  style={{ backgroundColor: "#EDEDED", padding: "25px" }}
                />
              </div>
            </div> */}

          </div>
        </div>
      </section>

      {/* ROI Calculator Section */}
      <ROICalculator />

      {/* Example Work Tab Section */}
      <ExampleWorkTab />

      {/* CTA PPC New Section */}
      <section className='cta-ppc-new-section pt-160'>
        <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-[15px]'>
          <div className="relative bg-gray-100 rounded-3xl overflow-hidden">
            <div className="grid grid-cols-1 lg:grid-cols-2 items-center cta-new-ppc-bx">

              {/* Left Content */}
              <div className="space-y-6 order-1 lg:order-1">
                <h2 className="text-[28px] md:text-[30px] lg:text-[32px] font-bold leading-[1.4]">
                  Grow what matters.<br />
                  Automate what doesn't.
                </h2>
                <p className="text-[18px] md:text-[18px] leading-relaxed m-0">
                  Stop fighting your software. Start growing your business.
                </p>
                <div className="btn-cta-new-ppc">
                  <a href='https://calendly.com/jeel-fieldcamp/30min' className="calendly-open bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-xl font-medium hover:opacity-90 transition-opacity" style={{ height: "52px" }}>Book a Demo</a>
                </div>
              </div>

              {/* Right Image */}
              <div className="relative h-[200px] md:h-[200px] lg:h-[200px] order-2 lg:order-2 nw-ppx-ct">
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/cta-new-vector-ppc.svg"
                  alt="FieldCamp CTA Illustration"
                  className="cta-ppc-img-nw object-contain"
                />
              </div>

            </div>
          </div>
        </div>
      </section>

    </div>
  );
}