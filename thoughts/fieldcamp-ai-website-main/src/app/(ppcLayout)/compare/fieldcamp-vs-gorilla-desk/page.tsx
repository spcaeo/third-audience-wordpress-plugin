import React from 'react';
import "./module.scss"
import { Metadata } from 'next';
import Accordion from '@/app/_components/Accordion';
import Script from 'next/script';
import { AppendUTMToAnchor, CalendlyEmbed } from '@/app/_components/General/Custom';
import DemoForm from '@/app/_components/Form/Demo/DemoForm';
import LPForm from '@/app/_components/Form/LP/FormHTML';
import LPFormModalClass from '@/app/_components/Form/LP/LPFormModalClass';


export const metadata: Metadata = {
    title: 'FieldCamp vs GorillaDesk: Modern AI Tools vs Pest Control Software',
    description: 'GorillaDesk was built for pest control, not your trade. FieldCamp: AI scheduling, transparent pricing, stable platform for HVAC, plumbing, electrical. $39-59/user.',
  robots: 'index, follow',
  alternates: {
    canonical: 'https://fieldcamp.ai/compare/fieldcamp-vs-gorilla-desk'
  }
};
const faqItems = [
    { 
      title: "Why is FieldCamp better for trades other than pest control?", 
      content: [
        "FieldCamp was designed from day one for HVAC, plumbing, electrical, landscaping, and all field service trades. Our features, terminology, and workflows match how these businesses actually operate. No adapting pest control features to fit your needs."
      ]
    },
    { 
      title: "How does FieldCamp's pricing compare?", 
      content: [
        "FieldCamp: $39-59 per user, all features included, clear and predictable. GorillaDesk: Confusing tier jumps from $49 to $299 with unclear user limits and feature restrictions. For a 10-person team, you'll save significantly while getting more capabilities."
      ]
    },
    { 
      title: "Can I switch from GorillaDesk easily?", 
      content: [
        "We handle the entire migration—customer data, job history, and service schedules transfer in about 24 hours. You can run both systems in parallel while your team gets comfortable. Most companies are fully switched within a week."
      ]
    },
    { 
      title: "Does FieldCamp have a stable mobile app?", 
      content: [
        "Yes, our mobile app is built on modern architecture that prioritizes stability and speed. No crashes, no lost data, no mid-job restarts. Your techs can focus on work, not troubleshooting apps."
      ]
    },
    { 
      title: "What about route optimization?", 
      content: [
        "FieldCamp includes real route optimization that considers traffic, job duration, tech skills, and parts on truck. It's not just drawing lines between dots—it's intelligence that reduces drive time by 35% on average."
      ]
    },
    { 
      title: "Why doesn't GorillaDesk have AI features?", 
      content: [
        "Adding AI to legacy architecture is extremely difficult. GorillaDesk would need to rebuild their entire platform. FieldCamp was built with AI as a core component, which is why our scheduling and analytics work so seamlessly."
      ]
    },
    { 
      title: "What if I'm already paying for GorillaDesk annually?", 
      content: [
        "Many companies switch anyway because the efficiency gains and cost savings outweigh any early termination considerations. We can help you calculate the ROI. Often, you'll save more in 2-3 months than any cancellation cost."
      ]
    },
    { 
      title: "Is FieldCamp really built for all trades?", 
      content: [
        "Yes. We have specific workflows, forms, and features for HVAC (equipment tracking, maintenance contracts), plumbing (emergency dispatch, parts inventory), electrical (permit tracking, inspection scheduling), and more. Not generic, not adapted—built specifically."
      ]
    }
  ];

const pageTitle = metadata.title?.toString() || 'FieldCamp';
const pageDescription = metadata.description || '';
const pageUrl = metadata.alternates?.canonical?.toString() || 'https://fieldcamp.ai/';

const schemaData = [
  {
    "@context": "https://schema.org/",
    "@type": "FAQPage",
    "mainEntity": faqItems.map(item => ({
      "@type": "Question",
      "name": item.title,
      "acceptedAnswer": {
        "@type": "Answer",
        "text": item.content[0]
      }
    }))
  },
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

export default function FieldCampVsGorillaDesk() {
  return (
    
    <div className="ppc-template">
      <Script
        id="structured-data"
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(schemaData) }}
      />
      <CalendlyEmbed/>
      <AppendUTMToAnchor/>
      
      <LPFormModalClass />
      <section className="relative bg-white overflow-hidden banner-section">
      {/* <div className="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-bl from-purple-100/30 via-pink-50/20 to-transparent"></div> */}
      <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0 pt-16 md:pt-24 pb-16 md:pb-20">
   <div className="grid lg:grid-cols-12 gap-8 lg:gap-12 items-center">
      <div className="lg:col-span-6 space-y-6 sm:space-y-8 text-center lg:text-left">
         <div className="space-y-4 sm:space-y-6">
            <div className="text-sm sm:text-base font-semibold text-gray-500 uppercase tracking-wider mb-4">
               FieldCamp vs GorillaDesk
            </div>
            <h1 className="text-3xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 leading-tight">More than scheduling software: <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">FieldCamp powers modern field operations</span></h1>
            <p className="text-lg sm:text-xl text-gray-600 leading-relaxed max-w-2xl mx-auto lg:mx-0">From dispatch to revenue insights, FieldCamp makes it easy for growing field service companies to work smarter. Hit your service goals with AI that actually ships today, and get real-time visibility to make informed decisions.</p>
         </div>
         <div className="flex justify-center lg:justify-start mt-8">
            <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open ppc-demo-btn bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity shadow-lg">
               Book a Demo
            </a>
         </div>
      </div>
      <div className="lg:col-span-6 relative mt-8 lg:mt-0">
         <div className="relative hidden md:flex">
            <img
               src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/cmp-gorila-baner-img.svg"
               alt="FieldCamp Field Service Management Software"
               className="w-auto object-contain"
            />
         </div>
         <div className="relative md:hidden">
            <img
               src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/cmp-gorila-baner-img.svg"
               alt="FieldCamp Field Service Management Software"
               className="w-auto object-contain"
            />
         </div>
      </div>
   </div>
</div>
   </section>
   {/* Logo New Section */}
   <section className='logo-new-ppc py-12 md:py-20 bg-white'>
     <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
       <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-8 items-center logo-new-ppc-sec">

         {/* Title - First on mobile, left side on desktop */}
         <div className="order-1 lg:order-1 lg:col-span-5 mobile-clmn">
           <h2 className="text-[24px] md:text-[36px] lg:text-[32px] font-semibold md:font-bold leading-[1.3] md:leading-[1.2] mb-8 md:mb-0 text-center lg:text-left">
             Why growing field service <br className="" />
             companies choose FieldCamp
           </h2>
         </div>

         <div className="order-2 lg:order-2 lg:col-span-7 mobile-clmn">
           <div className="flex flex-col md:flex-row items-center justify-between gap-6 md:gap-6 lg:gap-8 logo-new-bx">
             <div className="flex items-center justify-center flex-1">
               <img
                 src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/greenedge-nw.png"
                 alt="GreenEdge"
                 className="h-[50px] md:h-[60px] w-auto object-contain"
               />
             </div>

             <div className="flex items-center justify-center flex-1">
               <img
                 src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/lawnlift-sl-nw.png"
                 alt="LawnLift"
                 className="h-[50px] md:h-[60px] w-auto object-contain"
               />
             </div>

             <div className="flex items-center justify-center flex-1">
               <img
                 src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/grass-ctraft-nw.png"
                 alt="GrassCraft"
                 className="h-[50px] md:h-[60px] w-auto object-contain"
               />
             </div>

             <div className="flex items-center justify-center flex-1 md:hidden">
               <img
                 src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/snap-task-logo-nw.png"
                 alt="SnapTask"
                 className="h-[50px] md:h-[60px] w-auto object-contain"
               />
             </div>
           </div>
         </div>

       </div>
     </div>
   </section>

   {/* Features Section */}
   <section className='features-connected-section py-12 md:py-20 bg-white'>
     <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
       <div className="grid grid-cols-1 md:grid-cols-3 gap-12 lg:gap-12 feature-section-bx mobile-clmn">
         
         {/* Feature 1 - AI-native platform */}
         <div>
           <div className="mb-6">
            <img
                 src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/ai-native-platform-1.png"
                 alt="SnapTask"
                 className="w-auto object-contain"
               />
           </div>
           <h3 className="text-[20px] md:text-[24px] font-bold mb-4 h-[60px] flex items-start">
             AI-native platform
           </h3>
           <p className="text-[16px] md:text-[18px] leading-relaxed">
             FieldCamp isn't retrofitting AI onto legacy software—we're built on it from the ground up. Our AI learns your business patterns, automatically optimizes operations, and helps you make data-driven decisions that improve margins.
           </p>
         </div>

         {/* Feature 2 - Built for modern field service */}
         <div>
           <div className="mb-6">
           <img
                 src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/field-service-works.png"
                 alt="SnapTask"
                 className="w-auto object-contain"
               />
           </div>
           <h3 className="text-[20px] md:text-[24px] font-bold mb-4 h-[60px] flex items-start">
             Built for how modern field service works
           </h3>
           <p className="text-[16px] md:text-[18px] leading-relaxed">
             FieldCamp understands that today's field service is complex—recurring maintenance, emergency calls, multi-stage projects, and seasonal workflows. Our platform adapts to your business model, from residential HVAC to commercial electrical.
           </p>
         </div>

         {/* Feature 3 - Transparent pricing */}
         <div>
           <div className="mb-6">
           <img
                 src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/transparent-pricing.png"
                 alt="SnapTask"
                 className="w-auto object-contain"
               />
           </div>
           <h3 className="text-[20px] md:text-[24px] font-bold mb-4 h-[60px] flex items-start">
             Transparent, scalable pricing
           </h3>
           <p className="text-[16px] md:text-[18px] leading-relaxed">
             Simple pricing that makes sense: Starting at $39 per user, capping at $59 per user. No feature gates, no growth penalties, no surprise add-ons. Every tier includes full AI capabilities, unlimited jobs, and complete analytics.
           </p>
         </div>

       </div>
     </div>
   </section>

   {/* Modern Comparison Table */}
   <section className='comparison-table-section py-12 md:py-20 bg-white'>
     <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-0'>
       {/* Container with light gray background like Asana */}
       <div style={{ backgroundColor: 'rgba(0, 0, 0, 0.02)' }} className="rounded-3xl px-6 py-12 md:px-8 md:py-20 lg:px-12 lg:py-24">
         
         {/* Section Header */}
         <div className="text-center mb-10 md:mb-16">
           <h2 className="text-[32px] md:text-[40px] font-bold text-gray-900 leading-tight mb-3 md:mb-4">
             How does <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">FieldCamp</span> compare to GorillaDesk?
           </h2>
         </div>

         {/* Table Header */}
         <div className="hidden md:grid md:grid-cols-12 gap-6 mb-12 pb-8 border-b border-gray-200">
           <div className="col-span-6">
             <h3 className="text-[18px] font-semibold text-gray-900">Features</h3>
           </div>
           <div className="col-span-3 text-center">
             <h3 className="text-[18px] font-semibold text-gray-900">FieldCamp</h3>
           </div>
           <div className="col-span-3 text-center">
             <h3 className="text-[18px] font-semibold text-gray-600">GorillaDesk</h3>
           </div>
         </div>

         {/* Table Rows */}
         <div className="space-y-6">
           
           {/* AI-powered scheduling */}
           <div className="grid md:grid-cols-12 gap-6 items-center py-6 border-b border-gray-100 last:border-b-0">
             <div className="md:col-span-6">
               <h4 className="text-[20px] md:text-[24px] font-bold text-gray-900 mb-3">
                 AI-powered scheduling
               </h4>
               <p className="text-[16px] text-gray-600 leading-relaxed">
                 FieldCamp's AI suggests job schedules based on territory, skills, and real-time availability, so dispatchers save hours daily and techs complete more jobs without overtime.
               </p>
             </div>
             
             {/* Mobile comparison */}
             <div className="md:hidden flex justify-between items-center mt-4 px-4">
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-900">FieldCamp</span>
                 <div className="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-600">GorillaDesk</span>
                 <div className="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
             </div>

             {/* Desktop comparison */}
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
           </div>

           {/* Built for all trades */}
           <div className="grid md:grid-cols-12 gap-6 items-center py-6 border-b border-gray-100 last:border-b-0">
             <div className="md:col-span-6">
               <h4 className="text-[20px] md:text-[24px] font-bold text-gray-900 mb-3">
                 Built for all trades
               </h4>
               <p className="text-[16px] text-gray-600 leading-relaxed">
                 Designed from day one for HVAC, plumbing, electrical, landscaping, and more. Every feature works for your specific trade, not retrofitted from pest control workflows.
               </p>
             </div>
             
             {/* Mobile comparison */}
             <div className="md:hidden flex justify-between items-center mt-4 px-4">
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-900">FieldCamp</span>
                 <div className="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-600">GorillaDesk</span>
                 <div className="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
             </div>

             {/* Desktop comparison */}
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
           </div>

           {/* Transparent pricing */}
           <div className="grid md:grid-cols-12 gap-6 items-center py-6 border-b border-gray-100 last:border-b-0">
             <div className="md:col-span-6">
               <h4 className="text-[20px] md:text-[24px] font-bold text-gray-900 mb-3">
                 Transparent pricing
               </h4>
               <p className="text-[16px] text-gray-600 leading-relaxed">
                 Simple per-user pricing from $39-59 with every feature included. No confusing tiers, no unclear user limits, and no surprise costs as you scale your team.
               </p>
             </div>
             
             {/* Mobile comparison */}
             <div className="md:hidden flex justify-between items-center mt-4 px-4">
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-900">FieldCamp</span>
                 <div className="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-600">GorillaDesk</span>
                 <div className="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
             </div>

             {/* Desktop comparison */}
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
           </div>

           {/* Intelligent analytics */}
           <div className="grid md:grid-cols-12 gap-6 items-center py-6 border-b border-gray-100 last:border-b-0">
             <div className="md:col-span-6">
               <h4 className="text-[20px] md:text-[24px] font-bold text-gray-900 mb-3">
                 Intelligent analytics
               </h4>
               <p className="text-[16px] text-gray-600 leading-relaxed">
                 Ask questions in plain English like "What's my most profitable service?" and get instant visual reports. Build custom dashboards for owners, dispatchers, and managers without any technical skills.
               </p>
             </div>
             
             {/* Mobile comparison */}
             <div className="md:hidden flex justify-between items-center mt-4 px-4">
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-900">FieldCamp</span>
                 <div className="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-600">GorillaDesk</span>
                 <div className="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
             </div>

             {/* Desktop comparison */}
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
           </div>

           {/* Flexible automation */}
           <div className="grid md:grid-cols-12 gap-6 items-center py-6 border-b border-gray-100 last:border-b-0">
             <div className="md:col-span-6">
               <h4 className="text-[20px] md:text-[24px] font-bold text-gray-900 mb-3">
                 Flexible automation
               </h4>
               <p className="text-[16px] text-gray-600 leading-relaxed">
                 Visual workflow builder lets you automate anything—from follow-up texts to warranty reminders to seasonal service campaigns. Set it once, runs forever, no coding required.
               </p>
             </div>
             
             {/* Mobile comparison */}
             <div className="md:hidden flex justify-between items-center mt-4 px-4">
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-900">FieldCamp</span>
                 <div className="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-600">GorillaDesk</span>
                 <div className="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
             </div>

             {/* Desktop comparison */}
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
           </div>

           {/* Truly great support */}
           <div className="grid md:grid-cols-12 gap-6 items-center py-6 border-b border-gray-100 last:border-b-0">
             <div className="md:col-span-6">
               <h4 className="text-[20px] md:text-[24px] font-bold text-gray-900 mb-3">
                 Modern, stable platform
               </h4>
               <p className="text-[16px] text-gray-600 leading-relaxed">
                 Built in 2023 with modern architecture. No crashes, no lost data, no daily restarts. Your field data is too important for unstable apps.
               </p>
             </div>
             
             {/* Mobile comparison */}
             <div className="md:hidden flex justify-between items-center mt-4 px-4">
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-900">FieldCamp</span>
                 <div className="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
               <div className="flex items-center gap-2">
                 <span className="text-sm font-semibold text-gray-600">GorillaDesk</span>
                 <div className="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                   <svg className="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                     <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                   </svg>
                 </div>
               </div>
             </div>

             {/* Desktop comparison */}
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
             <div className="hidden md:flex md:col-span-3 justify-center">
               <div className="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                 <svg className="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                   <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                 </svg>
               </div>
             </div>
           </div>

         </div>

         {/* CTA Section */}
         <div className="text-center mt-12 pt-8 border-t border-gray-200">
           <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open ppc-demo-btn bg-green-600 hover:bg-green-700 text-white px-8 py-4 rounded-xl font-medium hover:opacity-90 transition-opacity shadow-lg">
             Schedule a call
           </a>
         </div>

       </div>
     </div>
   </section>

   <section id="features" className="py-12 md:py-20 bg-white">
      <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
         <div className="text-center mb-6 md:mb-16">
            <h2 className="text-4xl font-bold text-gray-900 mb-4">Sounds great, but I have a few questions…</h2>
         </div>
         <div className="max-w-7xl mx-auto space-y-16 md:space-y-32">
            {/* Question 1 - Left aligned */}
            <div className="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
               <div>
                  <h3 className="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-6">Is GorillaDesk really just for pest control?</h3>
                  <p className="text-xl text-gray-600 leading-relaxed mb-6">GorillaDesk started as pest control software and it shows. While they've added other trades, the core features, terminology, and workflows still center around pest routes. FieldCamp was built from scratch for all field service trades—your specific needs aren't an afterthought.</p>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open text-sm text-gray-600 hover:text-gray-900 underline">
                     Try for free →
                  </a>
               </div>
               <div className="relative">
                  <div className="flex items-center justify-center">
                     <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/Is-GorillaDesk-really-just-for-pest-control_-1.svg" alt="FieldCamp vs GorillaDesk Comparison" className="max-w-full h-auto" />
                  </div>
               </div>
            </div>

            {/* Question 2 - Right aligned */}
            <div className="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center lg:grid-flow-col-dense">
               <div className="lg:col-start-2">
                  <h3 className="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-6">What about GorillaDesk's pricing structure?</h3>
                  <p className="text-xl text-gray-600 leading-relaxed mb-6">GorillaDesk's pricing jumps from $49 to $99 to $299 monthly, but it's unclear how many users are included at each tier. Many features require the higher plans. FieldCamp is transparent: $39-59 per user, everything included, scales predictably as you grow.</p>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open text-sm text-gray-600 hover:text-gray-900 underline">
                     Try for free →
                  </a>
               </div>
               <div className="relative lg:col-start-1">
                  <div className="flex items-center justify-center">
                     <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/What-about-GorillaDesks-pricing-structure_.svg" alt="Smart Routing Features" className="max-w-full h-auto" />
                  </div>
               </div>
            </div>

            {/* Question 3 - Left aligned */}
            <div className="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
               <div>
                  <h3 className="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-6">We're a small shop. Is FieldCamp an overkill?</h3>
                  <p className="text-xl text-gray-600 leading-relaxed mb-6">Not at all. FieldCamp grows with you. Start with what you need—scheduling and invoicing—then add capabilities as you expand. No complex setup, no features built for pest control that you'll never use. Just the right tools for your trade.</p>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open text-sm text-gray-600 hover:text-gray-900 underline">
                     Try for free →
                  </a>
               </div>
               <div className="relative">
                  <div className="flex items-center justify-center">
                     <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/Were-a-small-shop.Is-FieldCamp-an-overkill_.svg" alt="Scalable Solutions" className="max-w-full h-auto" />
                  </div>
               </div>
            </div>

            {/* Question 4 - Right aligned */}
            <div className="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center lg:grid-flow-col-dense">
               <div className="lg:col-start-2">
                  <h3 className="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-6">Honestly, what's actually different day-to-day?</h3>
                  <p className="text-xl text-gray-600 leading-relaxed mb-6">Your morning dispatch takes 15 minutes instead of an hour. Routes actually make sense for your service area, not pest control territories. You type "show me this month's profit by service type" and get answers instantly. It's built for how you actually work.</p>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open text-sm text-gray-600 hover:text-gray-900 underline">
                     Try for free →
                  </a>
               </div>
               <div className="relative lg:col-start-1">
                  <div className="flex items-center justify-center">
                     <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/whats-actually-different-day-to-day_-1.svg" alt="Daily Workflow Improvements" className="max-w-full h-auto" />
                  </div>
               </div>
            </div>

            {/* Question 5 - Left aligned */}
            <div className="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
               <div>
                  <h3 className="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-6">Does the automation really work for non-pest control?</h3>
                  <p className="text-xl text-gray-600 leading-relaxed mb-6">Our automation is built for all trades. Set up HVAC maintenance reminders, plumbing warranty follow-ups, electrical inspection schedules—whatever your business needs. Visual workflow builder means no coding, just drag-and-drop logic that matches your process.</p>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open text-sm text-gray-600 hover:text-gray-900 underline">
                     Try for free →
                  </a>
               </div>
               <div className="relative">
                  <div className="flex items-center justify-center">
                     <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/Is-the-AI-scheduling-real-or-just-marketing_.svg" alt="Automation Features" className="max-w-full h-auto" />
                  </div>
               </div>
            </div>
         </div>
      </div>
      </section>

   {/* Testimonial Section */}
   <section className="py-16 md:py-24 bg-white">
      <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
         <div className="text-center max-w-4xl mx-auto">
            <blockquote className="text-xl md:text-2xl lg:text-3xl font-medium text-gray-900 leading-relaxed mb-8">
               "Switched from GorillaDesk 4 months ago. Finally software that understands HVAC operations. Routes make sense, pricing is clear, and our techs love the stable app."
            </blockquote>
            <div className="flex flex-col items-center">
               <cite className="text-lg font-semibold text-gray-900 not-italic">– Tom Bradley</cite>
               <p className="text-gray-600 mt-1">HVAC Operations Manager</p>
            </div>
         </div>
      </div>
   </section>
   
   <div className="py-12 sm:py-16 lg:py-20 bg-white ppc-faq">
      <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
         <h2 className="text-3xl sm:text-4xl lg:text-5xl font-black text-black mb-4 text-center mb-12 sm:mb-16">FREQUENTLY ASKED QUESTIONS</h2>
         <div className='max-w-4xl mx-auto'>
         <Accordion items={faqItems}/>
         </div>
      </div>
   </div>
   
   <section className="py-16 md:py-24 bg-black">
      <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0 text-center">
         <div className="max-w-4xl mx-auto">
            <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4 sm:mb-6">Ready to stop overpaying for outdated software?</h2>
            <p className="text-lg sm:text-xl text-gray-300 mb-8 sm:mb-12">100+ contractors who switched to FieldCamp and got their time back.</p>
            
            <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
               <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 hover:shadow-lg">
                  Book a Demo
               </a>
               <a href="tel:+18564602850" className="border border-white text-white px-6 py-3 rounded-xl font-semibold hover:bg-white/10 transition-all duration-300">
                  +1 856-460-2850
               </a>
            </div>
         </div>
      </div>
   </section>


    </div>
  );
}