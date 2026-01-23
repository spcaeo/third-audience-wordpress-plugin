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
    title: 'FieldCamp vs Jobber: Modern AI Tools vs 2015 Software',
    description: 'Jobber charges $199+ for outdated tech with no AI, no inventory, basic routing. FieldCamp: real AI scheduling, smart dispatch, custom analytics. $35/user flat.',
  robots: 'index, follow',
  alternates: {
    canonical: 'https://fieldcamp.ai/compare/fieldcamp-vs-jobber'
  }
};
const faqItems = [
    { 
      title: "Why FieldCamp is a good choice in 2026?", 
      content: [
        "FieldCamp delivers intelligent scheduling, route optimization, and natural language analytics that work today. If those capabilities would help your business run more efficiently, then yes—it's worth evaluating. We offer a 14-day free trial so you can test it with your actual operations and see if it fits your needs."
      ]
    },
    { 
      title: "How does FieldCamp's pricing compare?", 
      content: [
        "FieldCamp charges $39-59 per user with all features included—AI scheduling, routing, analytics, and automation. No feature tiers, no add-ons. Jobber starts at $39 but requires their $199 \"Grow\" plan for basic reporting, plus $29 per additional user. For a 10-person team, that's a significant difference."
      ]
    },
    { 
      title: "Does FieldCamp have AI features?", 
      content: [
        "Yes, our AI scheduling and routing are live and being used by thousands of companies daily. The AI learns your business patterns—which jobs take longer, which techs work fastest, which routes make sense—and applies that knowledge automatically. It's practical intelligence, not promises."
      ]
    },
    { 
      title: "What about route optimization?", 
      content: [
        "FieldCamp includes route optimization that considers real factors like traffic patterns, job duration, and service windows. It's built into the platform, not an add-on. Dispatchers can drag-and-drop to adjust, and routes automatically recalculate for the entire team."
      ]
    },
    { 
      title: "Can I switch from Jobber easily?", 
      content: [
        "We handle the entire migration—customer data, job history, and open invoices transfer in about 24 hours. You can run both systems in parallel while your team gets comfortable. Most companies are fully switched within a week, and we provide free onboarding support throughout."
      ]
    },
    { 
      title: "Why include inventory management?", 
      content: [
        "Modern field service requires knowing what's on each truck. Without integrated inventory tracking, techs make unnecessary supply runs, jobs get delayed, and profits shrink. FieldCamp tracks inventory by vehicle and technician, so you dispatch the right person with the right parts the first time."
      ]
    },
    { 
      title: "Is FieldCamp really that different?", 
      content: [
        "The main differences are AI that works today (not \"coming soon\"), transparent pricing that doesn't punish growth, and features like routing and inventory that come standard. Whether that's \"better\" depends on your specific needs. Try it free for 14 days and see if it works for your business."
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

export default function FieldCampVsJobber() {
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
               FieldCamp vs Jobber
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
               src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/jobber-cmp-banner-img.svg"
               alt="FieldCamp Field Service Management Software"
               className="w-auto object-contain"
            />
         </div>
         <div className="relative md:hidden">
            <img
               src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/jobber-cmp-banner-img.svg"
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
       <div className="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12 feature-section-bx mobile-clmn">
         
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
             How does <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">FieldCamp</span> compare to Jobber?
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
             <h3 className="text-[18px] font-semibold text-gray-600">Jobber</h3>
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
                 <span className="text-sm font-semibold text-gray-600">Jobber</span>
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
                 Simple per-user pricing from $39-59 with every feature included. No tiers to outgrow, no features locked behind higher plans, and no surprise costs as you scale your team.
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
                 <span className="text-sm font-semibold text-gray-600">Jobber</span>
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

           {/* Modern mobile experience */}
           <div className="grid md:grid-cols-12 gap-6 items-center py-6 border-b border-gray-100 last:border-b-0">
             <div className="md:col-span-6">
               <h4 className="text-[20px] md:text-[24px] font-bold text-gray-900 mb-3">
                 Modern mobile experience
               </h4>
               <p className="text-[16px] text-gray-600 leading-relaxed">
                 Intuitive mobile app built for speed in the field. Big buttons, simple workflows, and fast loading means techs spend less time on paperwork and more time on jobs.
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
                 <span className="text-sm font-semibold text-gray-600">Jobber</span>
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
                 <span className="text-sm font-semibold text-gray-600">Jobber</span>
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
                 <span className="text-sm font-semibold text-gray-600">Jobber</span>
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
                 Truly great support
               </h4>
               <p className="text-[16px] text-gray-600 leading-relaxed">
                 AI Agents + Real humans available 24/7 who actually know field service. Migration help, unlimited training, and proactive check-ins to ensure you're getting maximum value every month.
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
                 <span className="text-sm font-semibold text-gray-600">Jobber</span>
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
                  <h3 className="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-6">Is Jobber worth it in {new Date().getFullYear()}?</h3>
                  <p className="text-xl text-gray-600 leading-relaxed mb-6">Jobber handles basic scheduling and invoicing well, but it's missing critical pieces for modern field service—no route optimization, no inventory tracking, no intelligent scheduling. FieldCamp includes everything you actually need: smart routing that reduces drive time, inventory management across trucks, and AI that learns your business patterns. One platform, not five different apps.</p>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open text-sm text-gray-600 hover:text-gray-900 underline">
                     Try for free →
                  </a>
               </div>
               <div className="relative">
                  <div className="flex items-center justify-center">
                     <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/is-jobber-worh-it.svg" alt="FieldCamp vs Jobber Comparison" className="max-w-full h-auto" />
                  </div>
               </div>
            </div>

            {/* Question 2 - Right aligned */}
            <div className="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center lg:grid-flow-col-dense">
               <div className="lg:col-start-2">
                  <h3 className="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-6">What about features Jobber doesn't have—routing, inventory tracking?</h3>
                  <p className="text-xl text-gray-600 leading-relaxed mb-6">Exactly why we built FieldCamp. Route optimization that actually considers traffic and job duration. Inventory tracking that knows what's on each truck. Job costing that shows true profitability. These aren't add-ons or integrations—they're built in, included, and work together seamlessly.</p>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open text-sm text-gray-600 hover:text-gray-900 underline">
                     Try for free →
                  </a>
               </div>
               <div className="relative lg:col-start-1">
                  <div className="flex items-center justify-center">
                     <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/routing-inventory-tracking.svg" alt="Smart Routing Features" className="max-w-full h-auto" />
                  </div>
               </div>
            </div>

            {/* Question 3 - Left aligned */}
            <div className="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
               <div>
                  <h3 className="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-6">We're a small shop. Is this overkill?</h3>
                  <p className="text-xl text-gray-600 leading-relaxed mb-6">Not at all. FieldCamp grows with you. Start with what you need—scheduling and invoicing—then add capabilities as you expand. No complex setup, no features you'll never touch. Just the right tools at the right time. Teams with 2 techs use the same powerful platform as teams with 50, paying only for what they need.</p>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open text-sm text-gray-600 hover:text-gray-900 underline">
                     Try for free →
                  </a>
               </div>
               <div className="relative">
                  <div className="flex items-center justify-center">
                     <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/Were-a-small-shop.Is-this-overkill_.svg" alt="Scalable Solutions" className="max-w-full h-auto" />
                  </div>
               </div>
            </div>

            {/* Question 4 - Right aligned */}
            <div className="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center lg:grid-flow-col-dense">
               <div className="lg:col-start-2">
                  <h3 className="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-6">Honestly, what's actually different day-to-day?</h3>
                  <p className="text-xl text-gray-600 leading-relaxed mb-6">Your morning dispatch takes 15 minutes instead of an hour. Your techs see their optimized routes instantly. You type "show me this month's profit by service type" and get answers in seconds. It's dozens of small improvements that add up to hours saved weekly. Less time managing software, more time growing your business.</p>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open text-sm text-gray-600 hover:text-gray-900 underline">
                     Try for free →
                  </a>
               </div>
               <div className="relative lg:col-start-1">
                  <div className="flex items-center justify-center">
                     <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/whats-actually-different-day-to-day_.svg" alt="Daily Workflow Improvements" className="max-w-full h-auto" />
                  </div>
               </div>
            </div>

            {/* Question 5 - Left aligned */}
            <div className="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
               <div>
                  <h3 className="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-6">Does the automation really work, or is it just hype?</h3>
                  <p className="text-xl text-gray-600 leading-relaxed mb-6">Our automation is practical, not magical. It handles repetitive tasks like sending appointment reminders, creating recurring jobs, and following up on quotes. When a job completes, it can trigger an invoice, request a review, and schedule the next service. Real stuff that saves real time, not sci-fi promises.</p>
                  <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open text-sm text-gray-600 hover:text-gray-900 underline">
                     Try for free →
                  </a>
               </div>
               <div className="relative">
                  <div className="flex items-center justify-center">
                     <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/Does-the-automation-really-work.svg" alt="Automation Features" className="max-w-full h-auto" />
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
               "Switched from Jobber 6 months ago. Saving $400/month on software, completing more jobs, and my dispatcher actually smiles now."
            </blockquote>
            <div className="flex flex-col items-center">
               <cite className="text-lg font-semibold text-gray-900 not-italic">– David Park</cite>
               <p className="text-gray-600 mt-1">General Manager</p>
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