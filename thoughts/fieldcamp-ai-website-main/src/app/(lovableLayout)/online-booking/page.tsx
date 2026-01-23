import { CalendlyEmbed } from '@/app/_components/General/Custom';
import { FAQ } from './faq';
import React from 'react';
import { Metadata } from 'next';
import { AppendUTMToAnchor } from '@/app/_components/General/Custom';
import { ChevronDown, Settings, TrendingUp, Users, Zap } from "lucide-react";
import Script from 'next/script';

export const metadata: Metadata = {
  title: 'Online Booking Made Simple with FieldCamp',
  description: 'Empower clients to schedule field jobs instantly. Reduce manual tasks and improve service flow with FieldCamp\'s smart booking system.',
  robots: 'index, follow',
  alternates: {
    canonical: 'https://fieldcamp.ai/online-booking/'
  }
};



type FAQItem = {
   question: string;
   answer: string;
 };

type FAQCategory = 'customers' | 'setup' | 'smartFeatures' | 'results';

const faqs: Record<FAQCategory, FAQItem[]> = {
   customers: [
     {
       question: "Will my customers actually use digital scheduling?",
       answer: "Yes. Businesses using online booking software see higher engagement because customers value convenience. Our mobile-friendly widget with large buttons and simple navigation works for all age groups."
     },
     {
       question: "Can customers book services 24/7?",
       answer: "Absolutely. With 24/7 booking software, customers can schedule appointments any time, even after business hours — capturing the 40% of jobs most businesses lose."
     },
     {
       question: "Can I let customers choose specific services and pricing online?",
       answer: "Yes. Your field service booking system displays services with clear descriptions, durations, and prices, so customers know exactly what they’re getting."
     }
   ],
   setup: [
     {
       question: "How quickly can I set up FieldCamp’s booking system?",
       answer: "Most users are live in under 10 minutes. Just define your service area boundaries (radius, polygon, or zip code), add services, and embed the booking widget on your site."
     },
     {
       question: "Do I need a developer to install it?",
       answer: "No. You can embed our online booking widget yourself with a simple copy-paste code or use our WordPress plugin for one-click setup."
     },
     {
       question: "Can I integrate with my existing calendar?",
       answer: "Yes. We offer two-way sync with Google and FieldCamp’s native calendar to prevent double bookings."
     }
   ],
   smartFeatures: [
     {
       question: "How does the service area verification work?",
       answer: "Our Geographic Intelligence Engine validates addresses in real time using radius, polygon mapping, or zip-code lists. This prevents unprofitable, out-of-area bookings."
     },
     {
       question: "Can the system handle travel time between jobs?",
       answer: "Yes. Our field service scheduling software calculates travel time and applies buffers to ensure realistic job scheduling."
     },
     {
       question: "Does it support secure payments?",
       answer: "Yes. You can require deposits or full payments during booking with our online payment booking system, helping reduce no-shows and improve cash flow."
     }
   ],
   results: [
     {
       question: "How will online booking impact my revenue?",
       answer: "Most users report 20–40% revenue growth within months by capturing after-hours bookings, reducing no-shows, and improving efficiency."
     },
     {
       question: "Will this reduce my no-shows?",
       answer: "Yes. Automated email and SMS reminders cut average no-show rates by up to 30%."
     },
     {
       question: "What about pricing?",
       answer: "Can I track booking performance?"
     }
   ]
 };
 const faqItems = Object.values(faqs).flat();


 const pageTitle = metadata.title?.toString() || 'FieldCamp';
 const pageDescription = metadata.description || '';
 const pageUrl = metadata.alternates?.canonical?.toString() || 'https://fieldcamp.ai/';
 
 
 const schemaData = [
   {
     "@context": "https://schema.org/",
     "@type": "FAQPage",
     "mainEntity": faqItems.map(item => ({
       "@type": "Question",
       "name": item.question,
       "acceptedAnswer": {
         "@type": "Answer",
         "text": item.answer
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

 // Combine all FAQs into a single array for schema


const tabs = [
  { id: 'customers' as const, label: 'Customers' },
  { id: 'setup' as const, label: 'Setup' },
  { id: 'smartFeatures' as const, label: 'Smart Features' },
  { id: 'results' as const, label: 'Results' }
];
export default function OnlineBooking() {
  return (
   <>
   <Script
        id="structured-data"
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(schemaData) }}
      />
      
      <AppendUTMToAnchor/>
      <div className="ppc-template min-h-screen bg-white  relative">
       <CalendlyEmbed/>
       <div className="absolute inset-0 bg-[linear-gradient(to_right,#e5e7eb_1px,transparent_1px),linear-gradient(to_bottom,#e5e7eb_1px,transparent_1px)] bg-[size:20px_20px] opacity-40"></div>
       <div className="relative z-10">
      <section className="relative overflow-hidden bg-white px-6 py-16 lg:px-8 lg:py-20">
      <div className="mx-auto max-w-7xl relative z-10">
         <div className="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">
            <div className="animate-fade-in">
               <div className="mb-8">
                  <span className="inline-flex items-center rounded-full bg-blue-50 backdrop-blur-sm px-4 py-2 text-sm font-medium text-blue-600 ring-1 ring-blue-200 shadow-lg">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 mr-2 text-yellow-500">
                        <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                     </svg>
                     Join 15,000+ field service professionals
                  </span>
               </div>
               <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl lg:text-7xl">Your Customers Want to <span className="bg-gradient-to-r from-yellow-500 to-orange-500 bg-clip-text text-transparent">Book Online</span></h1>
               <p className="mt-6 text-base md:text-xl text-gray-600 leading-relaxed max-w-[590px]"><strong className="text-gray-900">FieldCamp’s field service booking software captures more jobs, qualifies leads instantly, and eliminates after-hours missed opportunities — all in one online booking system.</strong></p>
               <div className="mt-5 md:mt-8 grid grid-cols-2 gap-3 md:gap-4 text-sm text-gray-600">
                  <div className="flex items-center gap-2">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-5 w-5 text-green-500">
                        <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                        <path d="m9 11 3 3L22 4"></path>
                     </svg>
                     <span>Service area intelligence prevents wasted trips</span>
                  </div>
                  <div className="flex items-center gap-2">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-5 w-5 text-green-500">
                        <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                        <path d="m9 11 3 3L22 4"></path>
                     </svg>
                     <span>Automated job creation eliminates data entry</span>
                  </div>
                  <div className="flex items-center gap-2">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-5 w-5 text-green-500">
                        <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                        <path d="m9 11 3 3L22 4"></path>
                     </svg>
                     <span>Real-time calendar sync prevents conflicts</span>
                  </div>
                  <div className="flex items-center gap-2">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-5 w-5 text-green-500">
                        <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                        <path d="m9 11 3 3L22 4"></path>
                     </svg>
                     <span>Professional customer experience builds trust</span>
                  </div>
               </div>
               <div className="mt-8 md:mt-10 flex flex-col sm:flex-row gap-4">
                   
               <a href="https://app.fieldcamp.ai/signup" target="_blank" rel="noopener noreferrer"  data-medium="Banner-cta-free-trail" className="min-w-[50%] utm-medium-signup inline-flex items-center justify-center gap-2 md:whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary hover:bg-primary/90 md:h-11 rounded-md px-8 py-2 md:py-4 text-base md:text-lg bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold shadow-xl transform hover:scale-105 transition-all duration-200 border-0">Start 14-Day Free Trial </a>
               <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open min-w-[50%] calendly-open inline-flex items-center justify-center gap-2 md:whitespace-nowrap ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:text-accent-foreground md:h-11 rounded-md px-8 py-2 md:py-4 text-base md:text-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 font-semibold shadow-lg">Schedule Live Demo</a></div>
               <div className="mt-8 flex items-center gap-8 text-sm text-gray-600">
                  <div className="text-center">
                     <div className="text-2xl font-bold text-gray-900">7/10</div>
                     <div>customers prefer online booking</div>
                  </div>
                  <div className="text-center">
                     <div className="text-2xl font-bold text-gray-900">4/10</div>
                     <div>bookings happen after hours</div>
                  </div>
                  <div className="text-center">
                     <div className="text-2xl font-bold text-gray-900">2x</div>
                     <div>revenue increase typical</div>
                  </div>
               </div>
            </div>
            <div className="mt-12 lg:mt-0 animate-slide-up">
               <div className="relative">
                  <div className="rounded-2xl bg-white p-[25px_15px] md:p-8 shadow-2xl ring-1 ring-black/5 backdrop-blur-sm">
                     <div className="mb-6">
                        <div className="flex items-center gap-3 mb-2">
                           <div className="w-10 h-10 bg-gradient-to-br from-blue-500 to-green-500 rounded-lg flex items-center justify-center">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-wrench h-5 w-5 text-white">
                                 <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                              </svg>
                           </div>
                           <div>
                              <p className="text-lg font-semibold text-gray-900">Book HVAC Service</p>
                              <p className="text-sm text-gray-600">Available 24/7 online booking</p>
                           </div>
                        </div>
                     </div>
                     <div className="space-y-4">
                        <div className="rounded-lg bg-gradient-to-r from-gray-50 to-blue-50 p-[10px] md:p-4 border border-blue-100">
                           <div className="flex items-center justify-between">
                              <div className="flex items-center gap-3">
                                 <div className="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center"><span className="text-red-600 text-sm font-bold">!</span></div>
                                 <span className="text-sm font-medium text-gray-900">Emergency AC Repair</span>
                              </div>
                              <span className="text-sm font-semibold text-gray-900">$150</span>
                           </div>
                        </div>
                        <div className="rounded-lg bg-gradient-to-r from-blue-50 to-green-50 p-[10px] md:p-4 ring-1 ring-blue-200 border border-blue-200">
                           <div className="flex items-center gap-3">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-calendar h-5 w-5 text-blue-600">
                                 <path d="M8 2v4"></path>
                                 <path d="M16 2v4"></path>
                                 <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                 <path d="M3 10h18"></path>
                              </svg>
                              <div>
                                 <div className="text-sm font-medium text-gray-900">Tomorrow, 2:00 PM</div>
                                 <div className="flex items-center gap-1 text-xs text-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-map-pin h-3 w-3">
                                       <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                       <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span>Mike Thompson available</span>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <button className="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary hover:bg-primary/90 h-10 px-4 w-full bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white font-semibold py-3 shadow-lg transform hover:scale-[1.02] transition-all duration-200">Book Appointment</button>
                     </div>
                     <div className="mt-4 flex items-center gap-2 text-xs text-gray-500 bg-gray-50 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-clock h-4 w-4 text-green-600">
                           <circle cx="12" cy="12" r="10"></circle>
                           <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Booked at 10:47 PM - while you were sleeping</span>
                     </div>
                  </div>
                  <div className="absolute -bottom-4 -right-4 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 p-4 text-white shadow-2xl animate-float ring-4 ring-white/20">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-phone h-6 w-6">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                     </svg>
                  </div>
                  <div className="custom-badge">✓ Live Booking</div>
               </div>
            </div>
         </div>
      </div>
   </section>
   <section className="relative py-10 md:py-20 px-6 lg:px-8 overflow-hidden">
      <div className="mx-auto max-w-7xl relative z-10">
         <div className="text-center mb-8 md:mb-16 animate-fade-in">
            <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl lg:text-5xl">Operational <span className="bg-gradient-to-r from-red-500 to-orange-500 bg-clip-text text-transparent">Inefficiencies Solved</span>
            <span className="block font-normal mt-3 md:mt-6 text-base md:text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">Manual Scheduling is Costing Your Business Money. Stop losing revenue to operational inefficiencies that professional booking software eliminates.</span></h2>
         </div>
         <div className="max-w-4xl mx-auto mb-8 md:mb-16 animate-slide-up">
            <div className="bg-white/80 backdrop-blur-sm rounded-2xl p-6 md:p-8 lg:p-10 shadow-2xl border border-white/50 ring-1 ring-black/5">
               <div className="mb-6 md:mb-8 pb-6 md:pb-8 border-b border-gray-100">
                  <div className="flex items-start gap-4 mb-6 flex-col md:flex-row">
                     <div className="w-14 h-14 bg-gradient-to-br from-red-100 to-red-200 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg animate-float animation-delay-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-heart h-7 w-7 text-red-600">
                           <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                        </svg>
                     </div>
                     <div>
                        <h3 className="text-xl lg:text-2xl font-semibold text-foreground mb-3">Missed Revenue from Poor Response Times</h3>
                        <p className="text-muted-foreground text-base md:text-lg leading-relaxed">78% of field service jobs go to first responders. Manual phone coordination creates delays that result in lost qualified opportunities to competitors with instant booking software.</p>
                     </div>
                  </div>
                  <blockquote className="text-muted-foreground italic text-left md:text-center py-2 md:py-4 px-4 md:px-6 bg-gradient-to-r from-gray-50 to-red-50 rounded-lg border border-red-100 shadow-sm">"Mom, you promised we'd make the Mickey Mouse pancakes..."</blockquote>
               </div>
               <div className="mb-8">
                  <div className="flex items-start gap-4 mb-6 flex-col md:flex-row">
                     <div className="w-14 h-14 bg-gradient-to-br from-orange-100 to-orange-200 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg animate-float animation-delay-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-coffee h-7 w-7 text-orange-600">
                           <path d="M10 2v2"></path>
                           <path d="M14 2v2"></path>
                           <path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"></path>
                           <path d="M6 2v2"></path>
                        </svg>
                     </div>
                     <div>
                        <h3 className="text-xl lg:text-2xl font-semibold text-foreground mb-3">Administrative Overhead Eating Profits</h3>
                        <p className="text-muted-foreground text-base md:text-lg leading-relaxed">Field service owners spend 15-20% of operational time on scheduling coordination. Automated booking systems redirect this time toward revenue-generating activities.</p>
                     </div>
                  </div>
                  <blockquote className="text-muted-foreground italic text-left lg:text-center py-4 px-4 md:px-6 bg-gradient-to-r from-gray-50 to-orange-50 rounded-lg border border-orange-100 shadow-sm">He turns his phone face down, but they both know he's mentally calculating if he can squeeze in that emergency call before tomorrow's appointments.</blockquote>
               </div>
               <div className="mb-8">
                  <div className="flex items-start gap-4 mb-6 flex-col md:flex-row">
                     <div className="w-14 h-14 bg-gradient-to-br from-orange-100 to-orange-200 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg animate-float animation-delay-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-coffee h-7 w-7 text-orange-600">
                           <path d="M10 2v2"></path>
                           <path d="M14 2v2"></path>
                           <path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"></path>
                           <path d="M6 2v2"></path>
                        </svg>
                     </div>
                     <div>
                        <h3 className="text-xl lg:text-2xl font-semibold text-foreground mb-3">Unqualified Bookings Waste Resources</h3>
                        <p className="text-muted-foreground text-base md:text-lg leading-relaxed">25-30% of manual bookings come from unprofitable service areas. Service area verification prevents these costly mistakes before they impact operations.</p>
                     </div>
                  </div>
                  <blockquote className="text-muted-foreground italic text-left lg:text-center py-4 px-4 md:px-6 bg-gradient-to-r from-gray-50 to-orange-50 rounded-lg border border-orange-100 shadow-sm">He turns his phone face down, but they both know he's mentally calculating if he can squeeze in that emergency call before tomorrow's appointments.</blockquote>
               </div>

               <div className="text-center p-4 md:p-6 bg-gradient-to-r from-primary/5 to-blue-500/5 rounded-xl border border-primary/20 shadow-sm" style={{
    backgroundImage: `linear-gradient(
      to right,
      rgba(var(--primary-rgb, 59, 130, 246), 0.05),
      rgba(59, 130, 246, 0.05)
    )`,
  }}>
                  <p className="text-lg lg:text-xl font-medium text-foreground mb-2">Sound familiar? You're not alone.</p>
                  <p className="text-muted-foreground leading-relaxed">Every field service owner we talk to has these stories. The missed family moments. The customers who hang up when you don't answer immediately. The feeling that your phone owns you, not the other way around.</p>
               </div>
            </div>
         </div>
         <div className="grid md:grid-cols-2 gap-8 md:mb-16 mb-8">
            <div className="bg-white/80 backdrop-blur-sm rounded-xl p-6 md:p-8 shadow-xl border border-white/50 ring-1 ring-black/5 animate-slide-up animation-delay-200">
               <div className="flex items-center gap-3 mb-6">
                  <div className="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-triangle-alert h-6 w-6 text-white">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                     </svg>
                  </div>
                  <h3 className="text-2xl font-bold text-foreground">What No Online Booking Costs You?</h3>
               </div>
               <div className="space-y-6">
                  <div className="flex items-start gap-3 p-4 bg-gradient-to-r from-red-50 to-pink-50 rounded-lg border border-red-100">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-heart h-5 w-5 text-red-500 mt-1 flex-shrink-0">
                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                     </svg>
                     <div>
                        <h4 className="font-semibold text-foreground mb-1">Your Family Life</h4>
                        <p className="text-sm text-muted-foreground">Missed birthdays, interrupted dinners, and no true downtime — all because the phone might ring with “the big job.”</p>
                     </div>
                  </div>
                  <div className="flex items-start gap-3 p-4 bg-gradient-to-r from-orange-50 to-yellow-50 rounded-lg border border-orange-100">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-dollar-sign h-5 w-5 text-orange-500 mt-1 flex-shrink-0">
                        <line x1="12" x2="12" y1="2" y2="22"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                     </svg>
                     <div>
                        <h4 className="font-semibold text-foreground mb-1">Your Revenue</h4>
                        <p className="text-sm text-muted-foreground"><strong>40%+ of service calls</strong> happen after 6 PM. Without 24/7 booking software, those jobs go to competitors offering instant booking.</p>
                     </div>
                  </div>
                  <div className="flex items-start gap-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-clock h-5 w-5 text-blue-500 mt-1 flex-shrink-0">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                     </svg>
                     <div>
                        <h4 className="font-semibold text-foreground mb-1">Your Sanity</h4>
                        <p className="text-sm text-muted-foreground">12+ missed calls a week, double bookings from manual scheduling, and constant “calendar Tetris” just to keep up.</p>
                     </div>
                  </div>
               </div>
            </div>
            <div className="backdrop-blur-sm rounded-xl p-5 md:p-8 border border-gray-200/50 shadow-xl animate-slide-up animation-delay-400" style={{
     background: `linear-gradient(
      to bottom right,
      rgba(255, 255, 255, 0.9),
      rgba(249, 250, 251, 0.09)
    )`,
  }}>
               <div className="flex items-center gap-3 mb-6">
                  <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-phone h-6 w-6 text-white">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                     </svg>
                  </div>
                  <h3 className="text-2xl font-bold text-foreground">The Win With Online Booking</h3>
               </div>
               <div className="space-y-4">
                  <blockquote className="text-muted-foreground italic p-4 bg-white/70 backdrop-blur-sm rounded-lg border border-gray-200/50 shadow-sm">“I booked my HVAC repair online at midnight. Got instant confirmation — no waiting.”</blockquote>
                  <blockquote className="text-muted-foreground italic p-4 bg-white/70 backdrop-blur-sm rounded-lg border border-gray-200/50 shadow-sm">“Finally! A plumber with an online appointment booking system. I picked my time, paid a deposit, and I’m set.”</blockquote>
                  <blockquote className="text-muted-foreground italic p-4 bg-white/70 backdrop-blur-sm rounded-lg border border-gray-200/50 shadow-sm">“They had a customer self-service portal — it took less than 2 minutes to book.”</blockquote>
                  <blockquote className="text-muted-foreground italic p-4 bg-white/70 backdrop-blur-sm rounded-lg border border-gray-200/50 shadow-sm">“I won’t leave voicemails anymore. If I can’t book a service online, I’m calling someone else.”</blockquote>
               </div>
            </div>
         </div>
         <div className="text-center p-6 bg-green-50 text-green-700 p-[20px] md:p-[30px] rounded-xl border border-green-200 shadow-sm animate-fade-in">
                  <p className="text-lg lg:text-xl text-green-700 font-semibold text-foreground mb-2">Turn Missed Calls into Booked Jobs, Automatically</p>
                  <p className="leading-relaxed text-green-700">Tired of chasing missed calls and losing jobs after hours? With 24/7 Online Booking, your customers can schedule services instantly, even while you sleep. No phone tag. No stress. Just more time, more jobs, and more peace of mind.</p>
                  <a href='https://calendly.com/jeel-fieldcamp/30min' className="calendly-open mt-[18px] text-sm bg-green-600 inline-flex items-center px-6 py-2 rounded-[8px] text-white">Book a Free Demo — See It in Action</a>
               </div>
      </div>
   </section>
   <section className="relative pt-0 md:pt-20 py-10 md:py-20 px-6 lg:px-8 bg-white overflow-hidden">
      <div className="absolute inset-0 bg-[linear-gradient(to_right,#e5e7eb_1px,transparent_1px),linear-gradient(to_bottom,#e5e7eb_1px,transparent_1px)] bg-[size:20px_20px] opacity-20"></div>
      <div className="absolute top-1/3 right-20 w-6 h-6  rounded-full animate-float animation-delay-500"></div>
      <div className="mx-auto max-w-7xl relative z-10">
         <div className="text-center mb-8 md:mb-16 animate-fade-in">
            <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl lg:text-5xl">So What Exactly Is <span className="bg-gradient-to-r from-blue-500 to-green-500 bg-clip-text text-transparent">Online Booking</span> for Field Service?</h2>
            <p className="mt-3 md:mt-6 text-base md:text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">Think of it as your 24/7 virtual receptionist that never sleeps, never gets overwhelmed, and never forgets to check if you actually serve that area before booking the appointment.</p>
         </div>
         <div className="grid lg:grid-cols-2 gap-6 lg:gap-16 md:mb-20 mb-8">
            <div className="order-2 lg:order-1 animate-slide-up">
               <div className="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-8 shadow-2xl border border-white/50 ring-1 ring-black/5">
                  <h3 className="text-2xl font-bold text-foreground mb-2">How FieldCamp Online Booking Works</h3>
                  <p className="text-muted-foreground mb-8 leading-relaxed">No confusing tech jargon — just a seamless, step-by-step process that turns visitors into confirmed jobs in minutes.</p>
                  <div className="space-y-8">
                     <div className="group relative animate-slide-up" style={{ animationDelay: '100ms' }}>
                        <div className="flex gap-4 flex-col md:flex-row">
                           <div className="flex-shrink-0">
                              <div className="custom-float-badge" style={{ animationDelay: '100ms' }}>1</div>
                           </div>
                           <div className="flex-1">
                              <h4 className="font-semibold text-foreground mb-2 text-lg">Instant Service Area Check</h4>
                              <p className="text-muted-foreground mb-2 leading-relaxed">Customer clicks your booking widget and enters their address. System instantly checks if you serve their location.

</p>
                           </div>
                        </div>
                     </div>
                     <div className="group relative animate-slide-up" style={{ animationDelay: '200ms' }}>
                        <div className="flex gap-4 flex-col md:flex-row">
                           <div className="flex-shrink-0">
                              <div className="custom-float-badge" style={{ animationDelay: '200ms' }}>2</div>
                           </div>
                           <div className="flex-1">
                              <h4 className="font-semibold text-foreground mb-2 text-lg">Simple Contact Details</h4>
                              <p className="text-muted-foreground mb-2 leading-relaxed">Once location is verified, customer provides name, email, and phone. Clean, simple form - no complexities.

</p>
                           </div>
                        </div>
                     </div>
                     <div className="group relative animate-slide-up" style={{ animationDelay: '300ms' }}>
                        <div className="flex gap-4 flex-col md:flex-row">
                           <div className="flex-shrink-0">
                              <div className="custom-float-badge" style={{ animationDelay: '400ms' }}>3</div>
                           </div>
                           <div className="flex-1">
                              <h4 className="font-semibold text-foreground mb-2 text-lg">Service Selection Made Clear</h4>
                              <p className="text-muted-foreground mb-2 leading-relaxed">Your services appear with clear descriptions, pricing, and duration. Transparent pricing builds trust before they book.

</p>
                           </div>
                        </div>
                     </div>
                     <div className="group relative animate-slide-up" style={{ animationDelay: '400ms' }}>
                        <div className="flex gap-4 flex-col md:flex-row">
                           <div className="flex-shrink-0">
                              <div className="custom-float-badge" style={{ animationDelay: '600ms' }}>4</div>
                           </div>
                           <div className="flex-1">
                              <h4 className="font-semibold text-foreground mb-2 text-lg">Live Calendar Availability</h4>
                              <p className="text-muted-foreground mb-2 leading-relaxed">Customer sees your actual available time slots. They pick what works for both of you - no back-and-forth needed.

</p>
                           </div>
                        </div>
                     </div>
                     <div className="group relative animate-slide-up" style={{ animationDelay: '500ms' }}>
                        <div className="flex gap-4 flex-col md:flex-row">
                           <div className="flex-shrink-0">
                              <div className="custom-float-badge" style={{ animationDelay: '800ms' }}>5</div>
                           </div>
                           <div className="flex-1">
                              <h4 className="font-semibold text-foreground mb-2 text-lg">Secure Payments Processing</h4>
                              <p className="text-muted-foreground mb-2 leading-relaxed">Customer can pay deposit or full amount upfront through Stripe. Reduces no-shows and improves cash flow.

</p>
                           </div>
                        </div>
                     </div>
                     <div className="group relative animate-slide-up" style={{ animationDelay: '500ms' }}>
                        <div className="flex gap-4 flex-col md:flex-row">
                           <div className="flex-shrink-0">
                              <div className="custom-float-badge" style={{ animationDelay: '800ms' }}>6</div>
                           </div>
                           <div className="flex-1">
                              <h4 className="font-semibold text-foreground mb-2 text-lg">Instant Confirmation & Automation</h4>
                              <p className="text-muted-foreground mb-2 leading-relaxed">Job appears in your FieldCamp dashboard instantly. Customer gets confirmation email. Everyone's happy and informed.

</p>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div className="order-1 lg:order-2 mb-12 lg:mb-0 animate-slide-up animation-delay-200">
               <div className="relative">
                  <div className="relative bg-white/90 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/50 ring-1 ring-black/5 hover:shadow-3xl transition-all duration-300">
                     <div className="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200">
                        <div className="flex gap-1">
                           <div className="w-3 h-3 bg-red-400 rounded-full shadow-sm animate-pulse" style={{ animationDelay: '0ms' }}></div>
                           <div className="w-3 h-3 bg-yellow-400 rounded-full shadow-sm animate-pulse" style={{ animationDelay: '200ms' }}></div>
                           <div className="w-3 h-3 bg-green-400 rounded-full shadow-sm animate-pulse" style={{ animationDelay: '400ms' }}></div>
                        </div>
                        <div className="flex-1 bg-gray-50 rounded px-3 py-1 text-xs text-gray-500 text-center border border-gray-200">yourfieldservice.com/book</div>
                     </div>
                     <div className="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div className="custom-gradient-box">
                           <div className="flex items-center gap-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-navigation h-5 w-5 animate-spin" style={{ animationDuration: '3s' }}>
                                 <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                              </svg>
                              <h4 className="font-semibold">Service Area Check</h4>
                           </div>
                        </div>
                        <div className="p-6">
                           <div className="relative mb-6">
                              <div className="h-32 bg-gradient-to-br from-blue-50 to-green-50 rounded-lg border border-blue-200 flex items-center justify-center relative overflow-hidden shadow-inner">
                                 <div className="absolute inset-0 opacity-20">
                                    <div className="grid grid-cols-8 grid-rows-4 h-full w-full">
                                       {Array.from({ length: 32 }).map((_, i) => (
                                         <div 
                                           key={i}
                                           className="border border-blue-200/50 transition-all duration-300 hover:bg-blue-100/30" 
                                           style={{ animationDelay: `${i * 50}ms` }}
                                         />
                                       ))}
                                    </div>
                                 </div>
                                 <div className="relative">
                                    <div className="w-16 h-16 bg-green-500/30 rounded-full border-2 border-green-500 border-dashed flex items-center justify-center animate-pulse">
                                       <div className="w-3 h-3 bg-green-600 rounded-full animate-ping"></div>
                                    </div>
                                    <div className="absolute -top-8 left-1/2 transform -translate-x-1/2 animate-bounce">
                                       <div className="bg-green-600 text-white px-2 py-1 rounded text-xs font-medium shadow-lg">Your Area</div>
                                    </div>
                                    <div className="absolute inset-0 w-16 h-16 border-2 border-green-400 rounded-full animate-ping opacity-50"></div>
                                    <div className="absolute inset-0 w-16 h-16 border-2 border-green-300 rounded-full animate-ping opacity-30" style={{ animationDelay: '1s' }}></div>
                                 </div>
                                 <div className="absolute inset-0">
                                    <div className="absolute top-1/2 left-0 right-0 h-0.5 bg-blue-300/60 transform -translate-y-1/2">
                                       <div className="h-full w-2 bg-blue-500 animate-pulse" style={{ animationDelay: '0.5s' }}></div>
                                    </div>
                                    <div className="absolute left-1/2 top-0 bottom-0 w-0.5 bg-blue-300/60 transform -translate-x-1/2">
                                       <div className="w-full h-2 bg-blue-500 animate-pulse" style={{ animationDelay: '1s' }}></div>
                                    </div>
                                 </div>
                                 <div className="absolute top-4 left-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-map-pin h-3 w-3 text-blue-500 animate-bounce" style={{ animationDelay: '0.2s' }}>
                                       <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                       <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                 </div>
                                 <div className="absolute bottom-6 right-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-map-pin h-3 w-3 text-green-500 animate-bounce" style={{ animationDelay: '0.8s' }}>
                                       <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                       <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                 </div>
                                 <div className="absolute top-8 right-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-map-pin h-3 w-3 text-purple-500 animate-bounce" style={{ animationDelay: '1.2s' }}>
                                       <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                       <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                 </div>
                              </div>
                              <div className="absolute -bottom-2 -right-2 bg-white rounded-full p-2 shadow-lg border border-gray-200 animate-float">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-globe h-4 w-4 text-primary animate-spin" style={{ animationDuration: '8s' }}>
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                                    <path d="M2 12h20"></path>
                                 </svg>
                              </div>
                           </div>
                           <div className="text-center mb-6">
                              <div className="inline-flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-full border border-green-200 shadow-sm animate-fade-in">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-5 w-5 text-green-600 animate-pulse">
                                    <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                    <path d="m9 11 3 3L22 4"></path>
                                 </svg>
                                 <span className="font-semibold">✓ Great news! We serve your area</span>
                              </div>
                              <p className="text-sm text-muted-foreground mt-2 animate-fade-in" style={{ animationDelay: '0.5s' }}>Available for same-day and next-day service</p>
                           </div>
                           <div className="border-t pt-6">
                              <h4 className="font-semibold text-foreground mb-4 flex items-center gap-2">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-zap h-4 w-4 text-primary animate-pulse">
                                    <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                                 </svg>
                                 Available Services
                              </h4>
                              <div className="space-y-3">
                                 <div className="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-all duration-300 cursor-pointer shadow-sm hover:shadow-md transform hover:-translate-y-0.5 animate-slide-up" style={{ animationDelay: '0ms' }}>
                                    <div className="flex items-center gap-3">
                                       <div className="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                       <span className="font-medium">Emergency Repair</span>
                                    </div>
                                    <span className="text-primary font-semibold">$150</span>
                                 </div>
                                 <div className="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-all duration-300 cursor-pointer shadow-sm hover:shadow-md transform hover:-translate-y-0.5 animate-slide-up" style={{ animationDelay: '200ms' }}>
                                    <div className="flex items-center gap-3">
                                       <div className="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                       <span className="font-medium">Maintenance Check</span>
                                    </div>
                                    <span className="text-primary font-semibold">$95</span>
                                 </div>
                                 <div className="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-all duration-300 cursor-pointer shadow-sm hover:shadow-md transform hover:-translate-y-0.5 animate-slide-up" style={{ animationDelay: '400ms' }}>
                                    <div className="flex items-center gap-3">
                                       <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                       <span className="font-medium">Installation</span>
                                    </div>
                                    <span className="text-primary font-semibold">$200</span>
                                 </div>
                              </div>
                           </div>
                           <div className="border-t pt-6 mt-6">
                              <h4 className="font-semibold text-foreground mb-4 flex items-center gap-2">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-clock h-4 w-4 text-accent animate-spin" style={{ animationDuration: '4s' }}>
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                 </svg>
                                 Pick Your Time
                              </h4>
                              <div className="grid grid-cols-2 gap-2">
                                 <button className="p-3 rounded-lg text-sm transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 animate-fade-in border border-gray-300 hover:bg-gray-50" style={{ animationDelay: '0ms' }}>
                                    <div className="font-medium">Today</div>
                                    <div className="text-xs text-muted-foreground">3:00 PM</div>
                                 </button>
                                 <button className="custom-card" style={{ animationDelay: '100ms' }}>
                                    <div className="font-medium">Tomorrow</div>
                                    <div className="text-xs text-primary-foreground/80">10:00 AM</div>
                                 </button>
                                 <button className="p-3 rounded-lg text-sm transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 animate-fade-in border border-gray-300 hover:bg-gray-50" style={{ animationDelay: '200ms' }}>
                                    <div className="font-medium">Wednesday</div>
                                    <div className="text-xs text-muted-foreground">2:00 PM</div>
                                 </button>
                                 <button className="p-3 rounded-lg text-sm transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 animate-fade-in border border-gray-300 hover:bg-gray-50" style={{ animationDelay: '300ms' }}>
                                    <div className="font-medium">Thursday</div>
                                    <div className="text-xs text-muted-foreground">9:00 AM</div>
                                 </button>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div className="relative overflow-hidden animate-slide-up animation-delay-600">
            <div className="absolute top-0 left-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -translate-x-32 -translate-y-32"></div>
            <div className="absolute bottom-0 right-0 w-64 h-64 bg-accent/5 rounded-full blur-3xl translate-x-32 translate-y-32"></div>
            <div className="relative z-10">
               <div className="text-center mb-8 md:mb-16">
                  <div className="inline-flex items-center gap-2 bg-white/80 backdrop-blur-sm border border-primary/20 rounded-full px-6 py-3 mb-6 shadow-sm">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-trophy h-5 w-5 text-primary">
                        <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                        <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                        <path d="M4 22h16"></path>
                        <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                        <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                        <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                     </svg>
                     <span className="font-semibold text-primary">Why Pros Choose FieldCamp</span>
                  </div>
                  <h3 className="text-3xl font-bold text-foreground">Because Every Service Call Counts</h3>
                  <p className="mt-3 md:mt-6 text-base md:text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">Smart booking software that understands your territory, your team, and your time.</p>
               </div>
               <div className="grid lg:grid-cols-2 gap-8 max-w-6xl mx-auto">
                  <div className="group relative">
                     <div className="absolute inset-0 bg-gradient-to-br from-primary/10 to-transparent rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-300"></div>
                     <div className="relative bg-white/90 backdrop-blur-sm rounded-2xl p-5 md:p-8 border border-white/20 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <div className="flex items-start gap-4 md:mb-6 flex-col md:flex-row">
                           <div className="relative">
                              <div className="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/80 shadow-lg custom-gradient-box">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-map-pin h-7 w-7 text-white">
                                    <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                 </svg>
                              </div>
                              <div className="absolute -top-1 -right-1 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center shadow-md">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-4 w-4 text-white">
                                    <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                    <path d="m9 11 3 3L22 4"></path>
                                 </svg>
                              </div>
                           </div>
                           <div className="flex-1">
                              <h4 className="text-xl font-bold text-foreground mb-3">Geographic Intelligence Engine</h4>
                              <p className="text-muted-foreground text-base md:text-lg leading-relaxed mb-4">FieldCamp’s service area verification uses radius boundaries, polygon mapping, and zip code validation to prevent unqualified bookings.</p>
                           </div>
                        </div>
                        <div className="bg-gradient-to-r from-green-50 to-green-100/50 border border-green-200/50 rounded-xl p-4">
                           <div className="flex items-start gap-3">
                              <div className="flex min-h-8 min-w-8 w-8 h-8 items-center justify-center rounded-full bg-green-500/20 mt-0.5">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 text-green-600 fill-current">
                                    <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                 </svg>
                              </div>
                              <div>
                                 <p className="font-semibold text-green-800 mb-1 text-sm md:text-base">Real Result:</p>
                                 <p className="text-green-700 text-sm md:text-base">"We eliminated all out-of-area bookings. That alone saves us hours of wasted drive time every week."</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div className="group relative">
                     <div className="absolute inset-0 bg-gradient-to-br from-accent/10 to-transparent rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-300"></div>
                     <div className="relative bg-white/90 backdrop-blur-sm rounded-2xl p-5 md:p-8 border border-white/20 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <div className="flex items-start gap-4 md:mb-6 flex-col md:flex-row">
                           <div className="relative">
                              <div className="flex min-h-14 min-w-14 h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 shadow-lg">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-coffee h-7 w-7 text-white">
                                    <path d="M10 2v2"></path>
                                    <path d="M14 2v2"></path>
                                    <path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"></path>
                                    <path d="M6 2v2"></path>
                                 </svg>
                              </div>
                              <div className="absolute -top-1 -right-1 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center shadow-md">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-4 w-4 text-white">
                                    <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                    <path d="m9 11 3 3L22 4"></path>
                                 </svg>
                              </div>
                           </div>
                           <div className="flex-1">
                              <h4 className="text-xl font-bold text-foreground mb-3">Automated Work Order Integration</h4>
                              <p className="text-muted-foreground text-base md:text-lg leading-relaxed mb-4">Every booking instantly becomes a complete job in FieldCamp — with customer details, service specifications, and automated team assignments.</p>
                           </div>
                        </div>
                        <div className="bg-gradient-to-r from-blue-50 to-blue-100/50 border border-blue-200/50 rounded-xl p-4">
                           <div className="flex items-start gap-3">
                              <div className="flex min-h-8 min-w-8 h-8 w-8 items-center justify-center rounded-full bg-blue-500/20 mt-0.5">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-clock h-4 w-4 text-blue-600">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                 </svg>
                              </div>
                              <div>
                                 <p className="font-semibold text-blue-800 mb-1 text-sm md:text-base">True Story:</p>
                                 <p className="text-blue-700 text-sm md:text-base">"We used to spend 2+ hours daily creating jobs manually. Now it’s automatic."</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div className="group relative">
                     <div className="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-300"></div>
                     <div className="relative bg-white/90 backdrop-blur-sm rounded-2xl p-5 md:p-8 border border-white/20 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <div className="flex items-start gap-4 md:mb-6 flex-col md:flex-row">
                           <div className="relative">
                              <div className="flex min-h-14 min-w-14 h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-heart h-7 w-7 text-white">
                                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                                 </svg>
                              </div>
                              <div className="absolute -top-1 -right-1 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center shadow-md">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-4 w-4 text-white">
                                    <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                    <path d="m9 11 3 3L22 4"></path>
                                 </svg>
                              </div>
                           </div>
                           <div className="flex-1">
                              <h4 className="text-xl font-bold text-foreground mb-3">Real-Time Scheduling Coordination</h4>
                              <p className="text-muted-foreground text-base md:text-lg leading-relaxed mb-4">Our dynamic time slot availability management allows customers to see only accurate, bookable time slots,  ensuring your field service scheduling software runs at peak capacity.</p>
                           </div>
                        </div>
                        <div className="bg-gradient-to-r from-purple-50 to-purple-100/50 border border-purple-200/50 rounded-xl p-4">
                           <div className="flex items-start gap-3 md:gap-4">
                              <div className="flex min-h-8 min-w-8 h-8 w-8 items-center justify-center rounded-full bg-purple-500/20 mt-0.5">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-zap h-4 w-4 text-purple-600">
                                    <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                                 </svg>
                              </div>
                              <div>
                                 <p className="font-semibold text-purple-800 mb-1 text-sm md:text-base">Like:</p>
                                 <p className="text-purple-700 text-sm md:text-base">Real-time slot adjustments that prevent back-to-back jobs in different zones.</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div className="group relative">
                     <div className="absolute inset-0 bg-gradient-to-br from-orange-500/10 to-transparent rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-300"></div>
                     <div className="relative bg-white/90 backdrop-blur-sm rounded-2xl p-5 md:p-8 border border-white/20 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <div className="flex items-start gap-4 mb-0 md:mb-6 flex-col md:flex-row">
                           <div className="relative">
                              <div className="flex min-h-14 min-w-14 h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 shadow-lg">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-house h-7 w-7 text-white">
                                    <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
                                    <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                 </svg>
                              </div>
                              <div className="absolute -top-1 -right-1 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center shadow-md">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-4 w-4 text-white">
                                    <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                    <path d="m9 11 3 3L22 4"></path>
                                 </svg>
                              </div>
                           </div>
                           <div className="flex-1">
                              <h4 className="text-xl font-bold text-foreground mb-3">Secure Payment & No-Show Prevention</h4>
                              <p className="text-muted-foreground text-base md:text-lg leading-relaxed mb-4">Collect deposits or full payments at booking through our online payment booking system, reducing no-shows by up to 30% and improving cash flow. </p>
                           </div>
                        </div>
                        <div className="bg-gradient-to-r from-orange-50 to-orange-100/50 border border-orange-200/50 rounded-xl p-4">
                           <div className="flex items-start gap-3 md:gap-4">
                              <div className="flex min-h-8 min-w-8 h-8 w-8 items-center justify-center rounded-full bg-orange-500/20 mt-0.5">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-heart h-4 w-4 text-orange-600 fill-current">
                                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                                 </svg>
                              </div>
                              <div>
                                 <p className="font-semibold text-orange-800 mb-1 text-sm md:text-base">The Goal:</p>
                                 <p className="text-orange-700 text-sm md:text-base">More booked jobs completed, fewer last-minute cancellations.</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div className="md:mt-16 mt-8 text-center">
                  <div className="relative">
                     <div className="relative bg-white border border-[#3b82f6] backdrop-blur-sm rounded-2xl p-5 md:p-8 shadow-xl max-w-4xl mx-auto">
                        <div className="inline-flex items-center gap-2 bg-gradient-to-r from-primary/10 to-accent/10 text-primary px-4 py-2 rounded-full mb-6">
                           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-zap h-4 w-4">
                              <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                           </svg>
                           <span className="font-semibold">Bottom Line</span>
                        </div>
                        <h4 className="text-2xl font-bold text-foreground mb-4">This Isn't Just About Booking Appointments</h4>
                        <p className="text-muted-foreground text-lg max-w-3xl mx-auto md:mb-8 mb-5 leading-relaxed">It's about building a system that runs smart, not just hard. A business that keeps moving forward—bringing in jobs, keeping things organized, and staying one step ahead, even when you're off the clock.</p>
                        <div className="flex flex-col sm:flex-row gap-6 justify-center items-center">
                           <a href="https://app.fieldcamp.ai/signup" data-medium="Get Started for FREE" className="utm-medium-signup custom-primary-btn md:whitespace-nowrap h-auto md:h-11 gap-2 font-medium md:text-lg text-base md:py-2 py-4 md:px-8 px-6">Get Started for FREE</a>
                           <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open flex items-center gap-2 text-muted-foreground text-left h-auto md:h-11 border border-[#3b82f6] rounded-lg px-4 py-2 text-[#3b82f6]">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-users min-h-4 min-w-4 h-4 w-4">
                                 <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                 <circle cx="9" cy="7" r="4"></circle>
                                 <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                 <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                              </svg>
                              <span className="cursor-pointer text-[#3b82f6] text-sm">Book a Demo Now</span>
                           </a>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
   <section className="relative pt-0 md:pt-20 py-10 md:py-20 px-6 lg:px-8 bg-white overflow-hidden">
      <div className="absolute inset-0 opacity-[0.04]">
         <div className="absolute inset-0" style={{ backgroundImage: 'url("data:image/svg+xml,%3Csvg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%23000000" fill-opacity="1" fill-rule="evenodd"%3E%3Ccircle cx="20" cy="20" r="1.5"/%3E%3C/g%3E%3C/svg%3E")' }}></div>
      </div>
      <div className="absolute inset-0 pointer-events-none">
         <div className="absolute top-20 left-10 w-20 h-20 bg-primary/5 rounded-full blur-xl animate-float"></div>
         <div className="absolute bottom-40 right-20 w-16 h-16 bg-accent/5 rounded-full blur-xl animate-float" style={{ animationDelay: '3s' }}></div>
      </div>
      <div className="relative mx-auto max-w-7xl">
         <div className="text-center mb-8 md:mb-16">
            <div className="flex items-center justify-center gap-3 mb-4">
               <div className="flex items-center gap-2 bg-white/80 px-3 py-1.5 rounded-full border border-primary/20">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-target h-4 w-4 text-primary">
                     <circle cx="12" cy="12" r="10"></circle>
                     <circle cx="12" cy="12" r="6"></circle>
                     <circle cx="12" cy="12" r="2"></circle>
                  </svg>
                  <span className="text-sm font-medium text-primary">Real Results</span>
               </div>
            </div>
            <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">What This Really Means for Your Business</h2>
            <p className="mt-3 md:mt-6 text-base md:text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">Let’s skip the fluff — here’s how FieldCamp’s online booking software transforms field service operations in real numbers and real wins.</p>
         </div>
         <div className="mb-16">
            <div className="grid lg:grid-cols-2 gap-8 items-center">
               <div className="space-y-6">
                  <div className="space-y-4">
                     <div className="flex items-start gap-3">
                        <div className=" p-3 rounded-lg bg-[#0f172a1a]">
                           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-dollar-sign h-5 w-5 text-primary">
                              <line x1="12" x2="12" y1="2" y2="22"></line>
                              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                           </svg>
                        </div>
                        <div>
                           <h3 className="text-lg font-bold text-foreground mb-2">More Jobs, More Revenue</h3>
                           <p className="text-muted-foreground mb-3 text-sm">Go from chasing calls to closing them instantly. With 24/7 booking software, customers can book even after business hours — capturing the 40% of jobs most businesses lose.</p>
                           <div className="bg-blue-50 rounded-lg p-3 border border-blue-200">
                              <p className="text-sm font-medium text-blue-800 mb-1">"In 3 months, I went from $12K to $20K monthly revenue."</p>
                              <p className="text-xs text-blue-600">— Clara R., Phoenix HVAC</p>
                           </div>
                        </div>
                     </div>
                     <div className="flex items-start gap-3">
                        <div className="p-3 rounded-lg bg-[#0f172a1a]">
                           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-heart h-5 w-5 text-accent">
                              <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                           </svg>
                        </div>
                        <div>
                           <h3 className="text-lg font-bold text-foreground mb-2">Your Team Focused, Not Frantic</h3>
                           <p className="text-muted-foreground mb-3 text-sm">Automated job creation, real-time scheduling coordination, and instant technician assignments mean no more constant interruptions. Your staff works on quality jobs — not juggling calls and calendars.</p>
                           <div className="bg-pink-50 rounded-lg p-3 border border-pink-200">
                              <p className="text-sm font-medium text-pink-800 mb-1">"I can actually think now." </p>
                              <p className="text-xs text-pink-600">— Lisa M., Office Manager</p>
                           </div>
                        </div>
                     </div>
                      {/* <div className="flex items-start gap-3">
                        <div className="p-3 rounded-lg bg-[#0f172a1a]">
                           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-heart h-5 w-5 text-accent">
                              <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                           </svg>
                        </div>
                        <div>
                           <h3 className="text-lg font-bold text-foreground mb-2">Savings That Stack Up</h3>
                           <p className="text-muted-foreground mb-3 text-sm">Smarter route planning and optimized scheduling save hours of drive time and hundreds in fuel costs every month. Lower operating costs, higher profit margins, and a business that runs like clockwork.</p>
                           <div className="bg-pink-50 rounded-lg p-3 border border-pink-200">
                              <p className="text-sm font-medium text-pink-800 mb-1">"Best investment I ever made." </p>
                              <p className="text-xs text-pink-600">— Marcus P., Contractor</p>
                           </div>
                        </div>
                     </div> */}
                  </div>
               </div>
               <div className="relative">
                  <div className="bg-white/90 rounded-xl md:p-6 p-4 shadow-lg border border-gray-200">
                     <div className="text-center mb-4">
                        <div className="flex items-center justify-center gap-2 mb-2">
                           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-trending-up h-5 w-5 text-gray-700">
                              <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                              <polyline points="16 7 22 7 22 13"></polyline>
                           </svg>
                           <h4 className="text-base font-semibold text-foreground">Typical Business Transformation</h4>
                        </div>
                     </div>
                     <div className="space-y-4">
                        <div className="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                           <div className="flex items-center gap-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-phone h-4 w-4 text-red-600">
                                 <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                              </svg>
                              <span className="text-sm font-medium text-red-700">Before: Phone Only</span>
                           </div>
                           <span className="font-bold text-red-600">$12K/month</span>
                        </div>
                        <div className="flex justify-center">
                           <div className="bg-gradient-to-r from-red-500 to-green-500 h-2 w-24 rounded-full"></div>
                        </div>
                        <div className="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                           <div className="flex items-center gap-2">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-calendar h-4 w-4 text-green-600">
                                 <path d="M8 2v4"></path>
                                 <path d="M16 2v4"></path>
                                 <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                 <path d="M3 10h18"></path>
                              </svg>
                              <span className="text-sm font-medium text-green-700">After: Online Booking</span>
                           </div>
                           <span className="font-bold text-green-600">$20K/month</span>
                        </div>
                     </div>
                     <div className="mt-4 grid grid-cols-3 gap-3 text-center">
                        <div className="p-2 bg-gray-50 rounded-lg">
                           <div className="text-lg font-bold text-gray-800">67%</div>
                           <div className="text-xs text-gray-600">More Revenue</div>
                        </div>
                        <div className="p-2 bg-blue-50 rounded-lg">
                           <div className="text-lg font-bold text-blue-700">40%</div>
                           <div className="text-xs text-blue-600">After Hours</div>
                        </div>
                        <div className="p-2 bg-green-50 rounded-lg">
                           <div className="text-lg font-bold text-green-600">24/7</div>
                           <div className="text-xs text-green-600">Available</div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div className="grid md:grid-cols-3 gap-6 mb-16">
            <div className="bg-white/80 rounded-xl md:p-6 p-4 shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300">
               <div className="flex items-center gap-3 mb-4">
                  <div className=" p-2 rounded-lg bg-[#0f172a1a]">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-chart-column h-5 w-5 text-primary">
                        <path d="M3 3v16a2 2 0 0 0 2 2h16"></path>
                        <path d="M18 17V9"></path>
                        <path d="M13 17V5"></path>
                        <path d="M8 17v-3"></path>
                     </svg>
                  </div>
                  <h3 className="text-lg font-bold text-foreground">Know Your Business</h3>
               </div>
               <p className="text-muted-foreground mb-3 text-sm">David from Dallas Electric used to guess which days would be busy. Now he knows exactly when to schedule his team.</p>
               <div className="bg-blue-50 rounded-lg p-3 mb-4">
                  <p className="text-sm font-medium text-blue-800 mb-1">"Tuesday mornings are my goldmine"</p>
                  <p className="text-xs text-blue-700">David discovered 70% of his emergency calls happen Tuesday mornings.</p>
               </div>
               <div className="space-y-2 text-sm">
                  <div className="flex items-center gap-2">
                     <div className="w-1.5 h-1.5 bg-primary rounded-full"></div>
                     <span className="text-foreground">See your busiest hours and days</span>
                  </div>
                  <div className="flex items-center gap-2">
                     <div className="w-1.5 h-1.5 bg-primary rounded-full"></div>
                     <span className="text-foreground">Track which services customers want most</span>
                  </div>
                  <div className="flex items-center gap-2">
                     <div className="w-1.5 h-1.5 bg-primary rounded-full"></div>
                     <span className="text-foreground">Spot trends before your competition does</span>
                  </div>
               </div>
            </div>
            <div className="bg-white/80 rounded-xl md:p-6 p-4 shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300">
               <div className="flex items-center gap-3 mb-4">
                  <div className=" p-2 rounded-lg bg-[#0f172a1a]">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-users h-5 w-5 text-accent">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                     </svg>
                  </div>
                  <h3 className="text-lg font-bold text-foreground">Your Team Will Thank You</h3>
               </div>
               <p className="text-muted-foreground mb-3 text-sm">Lisa's office manager used to spend 3 hours a day just answering booking calls. Now she focuses on growing the business.</p>
               <div className="bg-green-50 rounded-lg p-3 mb-4">
                  <p className="text-sm font-medium text-green-800 mb-1">"I can actually think now"</p>
                  <p className="text-xs text-green-700">Lisa's team went from constant interruptions to focusing on quality work.</p>
               </div>
               <div className="space-y-2 text-sm">
                  <div className="flex items-center gap-2">
                     <div className="w-1.5 h-1.5 bg-accent rounded-full"></div>
                     <span className="text-foreground">No more constant phone interruptions</span>
                  </div>
                  <div className="flex items-center gap-2">
                     <div className="w-1.5 h-1.5 bg-accent rounded-full"></div>
                     <span className="text-foreground">Time for meaningful work and growth</span>
                  </div>
                  <div className="flex items-center gap-2">
                     <div className="w-1.5 h-1.5 bg-accent rounded-full"></div>
                     <span className="text-foreground">Happier staff who want to stay</span>
                  </div>
               </div>
            </div>
            <div className="bg-white/80 rounded-xl md:p-6 p-4 shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300">
               <div className="flex items-center gap-3 mb-4">
                  <div className=" p-2 rounded-lg bg-[#0f172a1a]">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-leaf h-5 w-5 text-primary">
                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"></path>
                        <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path>
                     </svg>
                  </div>
                  <h3 className="text-lg font-bold text-foreground">The Surprises That Add Up</h3>
               </div>
               <p className="text-muted-foreground mb-3 text-sm">Marcus saved $300/month in gas from better route planning. Plus, the automated booking system added $50K to his sale price.</p>
               <div className="bg-purple-50 rounded-lg p-3 mb-4">
                  <p className="text-sm font-medium text-purple-800 mb-1">"Best investment I ever made"</p>
                  <p className="text-xs text-purple-700">Marcus's business became so much more efficient that buyers competed for it.</p>
               </div>
               <div className="space-y-2 text-sm">
                  <div className="flex items-center gap-2">
                     <div className="w-1.5 h-1.5 bg-primary rounded-full"></div>
                     <span className="text-foreground">Smarter routing saves time and fuel</span>
                  </div>
                  <div className="flex items-center gap-2">
                     <div className="w-1.5 h-1.5 bg-primary rounded-full"></div>
                     <span className="text-foreground">Lower operating costs every month</span>
                  </div>
                  <div className="flex items-center gap-2">
                     <div className="w-1.5 h-1.5 bg-primary rounded-full"></div>
                     <span className="text-foreground">A business buyers actually want</span>
                  </div>
               </div>
            </div>
         </div>
         <div className="bg-gradient-to-br from-white/90 via-blue-50/90 to-indigo-50/70 backdrop-blur-sm rounded-2xl p-6 md:p-12 shadow-lg border border-white/50 relative overflow-hidden">
            <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-red-500 via-yellow-500 via-green-500 to-blue-500"></div>
            <div className="text-center md:mb-12 mb-6">
               <div className="flex items-center justify-center gap-2 mb-6">
                  <div className="bg-gradient-to-r from-red-100 to-pink-100 p-3 rounded-full border border-red-200">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-heart h-6 w-6 text-red-500">
                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                     </svg>
                  </div>
                  <div className="h-px w-12 bg-gradient-to-r from-red-300 to-transparent"></div>
                  <div className="bg-gradient-to-r from-blue-100 to-indigo-100 p-3 rounded-full border border-blue-200">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-users h-6 w-6 text-blue-500">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                     </svg>
                  </div>
                  <div className="h-px w-12 bg-gradient-to-r from-blue-300 to-transparent"></div>
                  <div className="bg-gradient-to-r from-green-100 to-emerald-100 p-3 rounded-full border border-green-200">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-trending-up h-6 w-6 text-green-500">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                        <polyline points="16 7 22 7 22 13"></polyline>
                     </svg>
                  </div>
               </div>
               <h3 className="md:text-3xl text-2xl font-bold text-foreground md:mb-6 mb-2">Put Your Field Service Bookings on Autopilot</h3>
               <p className="text-muted-foreground max-w-3xl mx-auto md:text-lg text-base leading-relaxed">While you're out working, your next appointments are already lining up, with 24/7 booking. Your business keeps growing—even when you're not on the phone.</p>
            </div>
            <div className="grid lg:grid-cols-3 gap-8 mb-12">
               <div className="text-center group">
                  <div className="relative mb-6">
                     <div className="bg-gradient-to-br from-red-100 to-pink-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-heart h-10 w-10 text-red-500">
                           <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                        </svg>
                     </div>
                     {/* <div className="absolute -top-2 -right-2 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-4 w-4 text-white">
                           <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                           <path d="m9 11 3 3L22 4"></path>
                        </svg>
                     </div> */}
                  </div>
                  <h4 className="text-xl font-bold text-foreground mb-4">Reconnect with What Matters</h4>
                  <div className="bg-red-50 border border-red-200 rounded-xl p-4 md:p-6 text-left">
                     <p className="text-sm font-medium text-red-800 mb-3">"Step away from the phone—without losing business."</p>
                     <p className="text-sm text-red-700">24/7 online bookings frees up time for what matters; running your business, leading your crew and enjoying life without interruptions.</p>
                  </div>
               </div>
               <div className="text-center group">
                  <div className="relative mb-6">
                     <div className="bg-gradient-to-br from-blue-100 to-indigo-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-calendar h-10 w-10 text-blue-500">
                           <path d="M8 2v4"></path>
                           <path d="M16 2v4"></path>
                           <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                           <path d="M3 10h18"></path>
                        </svg>
                     </div>
                     {/* <div className="absolute -top-2 -right-2 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-4 w-4 text-white">
                           <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                           <path d="m9 11 3 3L22 4"></path>
                        </svg>
                     </div> */}
                  </div>
                  <h4 className="text-xl font-bold text-foreground mb-4">Auto-Synced & Organized</h4>
                  <div className="bg-blue-50 border border-blue-200 rounded-xl p-4 md:p-6 text-left">
                     <p className="text-sm font-medium text-blue-800 mb-3">"New bookings are fully-synced and go straight into your calendar."</p>
                     <p className="text-sm text-blue-700">Stay effortlessly organized with real-time scheduling updates. Say goodbye to double-bookings and manual inputs.</p>
                  </div>
               </div>
               <div className="text-center group">
                  <div className="relative mb-6">
                     <div className="bg-gradient-to-br from-green-100 to-emerald-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-trending-up h-10 w-10 text-green-500">
                           <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                           <polyline points="16 7 22 7 22 13"></polyline>
                        </svg>
                     </div>
                     {/* <div className="absolute -top-2 -right-2 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-4 w-4 text-white">
                           <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                           <path d="m9 11 3 3L22 4"></path>
                        </svg>
                     </div> */}
                  </div>
                  <h4 className="text-xl font-bold text-foreground mb-4">Grow Without the Guilt</h4>
                  <div className="bg-green-50 border border-green-200 rounded-xl p-4 md:p-6 text-left">
                     <p className="text-sm font-medium text-green-800 mb-3">"Scale your business without sacrificing your personal life."</p>
                     <p className="text-sm text-green-700">Automated booking means more jobs, more revenue, and more time at home. You no longer have to choose between growth and balance.</p>
                  </div>
               </div>
            </div>
            <div className="text-center rounded-xl p-5 md:p-8 border border-white/60 shadow-lg relative overflow-hidden" style={{ background: 'linear-gradient(to bottom right, rgba(255, 255, 255, 0.9), rgba(249, 250, 251, 0.09))' }}>
               {/* <div className="absolute top-0 left-1/2 transform -translate-x-1/2 w-20 h-1 bg-[#0000001a] rounded-full"></div> */}
               <div className="max-w-4xl mx-auto">
                  {/* <div className="text-6xl text-[#0f172a33] font-serif md:mb-4 mb-2">"</div> */}
                  <p className="md:text-xl text-lg text-foreground font-medium mb-6 leading-relaxed">You're in field service to get things done—on-site, not behind a screen.
                  Automate the back office, and let your business perform like it should: sharp, fast, and always moving.</p>
                  <p className="text-lg text-muted-foreground flex items-center justify-center gap-2">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-heart min-h-5 min-w-5 h-5 w-5 text-red-500">
                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                     </svg>
                     <span className='md:text-base text-sm'>That's exactly what should happen.</span>
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-sparkles min-h-5 min-w-5 h-5 w-5 text-primary">
                        <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"></path>
                        <path d="M20 3v4"></path>
                        <path d="M22 5h-4"></path>
                        <path d="M4 17v2"></path>
                        <path d="M5 18H3"></path>
                     </svg>
                  </p>
               </div>
            </div>
         </div>
      </div>
   </section>
   <section className="relative py-10 md:py-20 px-6 lg:px-8">
      <div className="absolute top-20 right-20 w-8 h-8 bg-blue-100/20 rounded-full animate-float"></div>
      <div className="absolute bottom-20 left-20 w-6 h-6 bg-green-100/20 rounded-full animate-float animation-delay-400"></div>
      <div className="mx-auto max-w-7xl relative z-10">
         <div className="text-center md:mb-16 mb-8 animate-fade-in">
            <div className="inline-flex items-center gap-2 bg-white/80 backdrop-blur-sm text-primary px-4 py-2 rounded-full text-sm font-medium mb-6 border border-gray-200 shadow-sm">
               <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-4 w-4 text-green-600">
                  <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                  <path d="m9 11 3 3L22 4"></path>
               </svg>
               Verified Customer Stories
            </div>
            <h2 className="md:text-4xl text-3xl font-bold tracking-tight text-foreground sm:text-5xl">Real Stories from <span className="text-primary">Real Businesses</span></h2>
            <p className="mt-3 md:mt-6 text-base md:text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">Don't just take our word for it. Here's what field service professionals are saying about their transformation.</p>
         </div>
         <div className="grid lg:grid-cols-3 gap-8 md:mb-16 mb-8">
            <div className="rounded-lg text-card-foreground group relative border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-white animate-slide-up" style={{ animationDelay: '0s' }}>
               <div className="absolute -top-2 -right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md border border-gray-100">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-quote h-4 w-4 text-gray-400">
                     <path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                     <path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                  </svg>
               </div>
               <div className="p-5 md:p-8">
                  <div className="flex items-center justify-between mb-6">
                     <div className="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                     </div>
                     <div className="px-3 py-1 rounded-full text-xs font-medium text-primary ">40% more revenue</div>
                  </div>
                  <blockquote className="text-foreground mb-8 leading-relaxed italic">"I was missing my kids' childhood answering phones. Now I capture more emergency calls than ever, make more money, and I'm actually present for my family. Should have done this years ago."</blockquote>
                  <div className="border-t border-gray-100 pt-6">
                     <div className="flex items-center gap-4 mb-4">
                        <div className="w-12 h-12 rounded-full bg-[#0f172a1a] flex items-center justify-center text-primary font-bold">MT</div>
                        <div className="flex-1">
                           <div className="font-bold text-foreground">Alex Carter</div>
                           <div className="text-primary font-medium">Carter Cooling Co.g</div>
                           <div className="text-sm text-muted-foreground">📍 Las Vegas, NV</div>
                        </div>
                     </div>
                     <div className="flex items-center gap-3 text-xs"><span className="bg-[#0f172a1a] text-primary px-2 py-1 rounded-full font-medium">HVAC</span><span className="bg-gray-100 text-gray-700 px-2 py-1 rounded-full font-medium">12 employees</span></div>
                  </div>
               </div>
            </div>
            <div className="rounded-lg text-card-foreground group relative border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-white animate-slide-up" style={{ animationDelay: '0.1s' }}>
               <div className="absolute -top-2 -right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md border border-gray-100">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-quote h-4 w-4 text-gray-400">
                     <path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                     <path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                  </svg>
               </div>
               <div className="p-5 md:p-8">
                  <div className="flex items-center justify-between mb-6">
                     <div className="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                     </div>
                     <div className="px-3 py-1 rounded-full text-xs font-medium text-primary ">2 new territories</div>
                  </div>
                  <blockquote className="text-foreground mb-8 leading-relaxed italic">"I went from being a phone operator to actually managing our operations. We expanded to two new territories because I finally had time to focus on growth. Game changer."</blockquote>
                  <div className="border-t border-gray-100 pt-6">
                     <div className="flex items-center gap-4 mb-4">
                        <div className="w-12 h-12 rounded-full bg-[#0f172a1a] flex items-center justify-center text-primary font-bold">ER</div>
                        <div className="flex-1">
                           <div className="font-bold text-foreground">Emma Rodriguez</div>
                           <div className="text-primary font-medium">Chicago Plumbing Co.</div>
                           <div className="text-sm text-muted-foreground">📍 Chicago</div>
                        </div>
                     </div>
                     <div className="flex items-center gap-3 text-xs"><span className="bg-[#0f172a1a] text-primary px-2 py-1 rounded-full font-medium">Plumbing</span><span className="bg-gray-100 text-gray-700 px-2 py-1 rounded-full font-medium">8 employees</span></div>
                  </div>
               </div>
            </div>
            <div className="rounded-lg text-card-foreground group relative border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-white animate-slide-up" style={{ animationDelay: '0.2s' }}>
               <div className="absolute -top-2 -right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md border border-gray-100">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-quote h-4 w-4 text-gray-400">
                     <path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                     <path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                  </svg>
               </div>
               <div className="p-5 md:p-8">
                  <div className="flex items-center justify-between mb-6">
                     <div className="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-star h-4 w-4 fill-yellow-400 text-yellow-400">
                           <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                     </div>
                     <div className="px-3 py-1 rounded-full text-xs font-medium text-primary ">15% price increase</div>
                  </div>
                  <blockquote className="text-foreground mb-8 leading-relaxed italic">"Our customers book their entire season online now. We raised prices 15% and nobody complained because the convenience is worth it. Best investment we've made."</blockquote>
                  <div className="border-t border-gray-100 pt-6">
                     <div className="flex items-center gap-4 mb-4">
                        <div className="w-12 h-12 rounded-full bg-[#0f172a1a] flex items-center justify-center text-primary font-bold">SC</div>
                        <div className="flex-1">
                           <div className="font-bold text-foreground">Sarah Chen</div>
                           <div className="text-primary font-medium">GreenScape Lawn Care</div>
                           <div className="text-sm text-muted-foreground">📍 Austin</div>
                        </div>
                     </div>
                     <div className="flex items-center gap-3 text-xs"><span className="bg-[#0f172a1a] text-primary px-2 py-1 rounded-full font-medium">Landscaping</span><span className="bg-gray-100 text-gray-700 px-2 py-1 rounded-full font-medium">25 employees</span></div>
                  </div>
               </div>
            </div>
         </div>
         <div className="relative bg-white/60 backdrop-blur-sm rounded-2xl md:p-8 p-5 shadow-lg border border-gray-200 animate-slide-up">
            <div className="text-center mb-6">
               <div className="inline-flex items-center gap-2  px-3 py-1.5 rounded-full text-sm font-medium text-primary mb-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-award h-4 w-4">
                     <path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"></path>
                     <circle cx="12" cy="8" r="6"></circle>
                  </svg>
                  Proven Results
               </div>
               <h3 className="text-2xl font-bold text-foreground mb-2">Join the Growing Success Stories</h3>
               <p className="text-muted-foreground">These results speak for themselves</p>
            </div>
            <div className="grid md:grid-cols-3 gap-6">
               <div className="text-center p-4 rounded-xl bg-white/80 border border-gray-100 hover:shadow-md transition-all duration-300">
                  <div className="w-10 h-10 bg-[#0f172a1a]  rounded-full flex items-center justify-center mx-auto mb-3">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-users h-5 w-5 text-primary">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                     </svg>
                  </div>
                  <div className="text-2xl font-bold text-primary mb-1 ">Growing</div>
                  <div className="text-gray-700 font-medium text-sm mb-1">community of field service professionals</div>
                  <div className="text-xs text-muted-foreground">trusting our solution daily</div>
               </div>
               <div className="text-center p-4 rounded-xl bg-white/80 border border-gray-100 hover:shadow-md transition-all duration-300">
                  <div className="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-trending-up h-5 w-5 text-green-600">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                        <polyline points="16 7 22 7 22 13"></polyline>
                     </svg>
                  </div>
                  <div className="text-2xl font-bold text-green-600 mb-1">40%</div>
                  <div className="text-gray-700 font-medium text-sm mb-1">more jobs captured on average</div>
                  <div className="text-xs text-muted-foreground">in the first 90 days</div>
               </div>
               <div className="text-center p-4 rounded-xl bg-white/80 border border-gray-100 hover:shadow-md transition-all duration-300">
                  <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-award h-5 w-5 text-blue-600">
                        <path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"></path>
                        <circle cx="12" cy="8" r="6"></circle>
                     </svg>
                  </div>
                  <div className="text-2xl font-bold text-blue-600 mb-1">98%</div>
                  <div className="text-gray-700 font-medium text-sm mb-1">customer satisfaction rate</div>
                  <div className="text-xs text-muted-foreground">based on verified reviews</div>
               </div>
            </div>
         </div>
      </div>
   </section>
     
   <section className="relative py-10 md:py-20 px-6 lg:px-8 bg-white overflow-hidden">
        <div className="mx-auto max-w-7xl relative z-10">
          <FAQ tabs={tabs} faqs={faqs}/>
        </div>
      </section>
 
   <section className="relative overflow-hidden bg-white px-6 py-8 lg:px-8 lg:py-20">
      <div className="absolute inset-0 bg-white "></div>
      <div className="absolute inset-0 bg-[linear-gradient(to_right,#e5e7eb_1px,transparent_1px),linear-gradient(to_bottom,#e5e7eb_1px,transparent_1px)] bg-[size:20px_20px] opacity-40"></div>
      <div className="mx-auto max-w-3xl relative z-10 text-center animate-fade-in">
         <div className="inline-flex items-center gap-2 bg-blue-50 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium text-blue-600 ring-1 ring-blue-200 shadow-lg mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-clock min-h-4 min-w-4">
               <circle cx="12" cy="12" r="10"></circle>
               <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            Last Chance: Free Setup for New Signups
         </div>
         <h2 className="md:text-4xl text-3xl font-bold tracking-tight text-gray-900 mb-4">Ready for Zero-Hassle <span className="bg-gradient-to-r from-yellow-500 to-orange-500 bg-clip-text text-transparent">Field Job Bookings?</span></h2>
         <p className="text-lg md:text-base text-gray-600 leading-relaxed md:mb-8 mb-6 max-w-2xl mx-auto"><strong className="text-gray-900">Join hundreds of field service businesses</strong> increasing revenue with 24/7 online booking. Start capturing field jobs while you sleep.</p>
         <div className="md:mb-8 mb-6">
            <a href="https://app.fieldcamp.ai/signup" data-medium="btn-start-free-trial-2" className="utm-medium-signup inline-flex items-center justify-center gap-2 md:whitespace-nowrap whitespace-normal ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary hover:bg-primary/90 md:h-11 h-auto rounded-md py-4 px-8 md:text-lg text-base font-semibold bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white shadow-xl transform hover:scale-105 transition-all duration-200 border-0">
            Let Jobs Roll In – Try It Free for 14 Days <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-arrow-right ml-2 h-5 w-5">
                  <path d="M5 12h14"></path>
                  <path d="m12 5 7 7-7 7"></path>
               </svg>
            </a>
            <p className="text-sm text-gray-600 mt-3">14 days free • No credit card needed • 5-minute setup</p>
         </div>
         <div className="flex items-center justify-center md:gap-8 gap-4 text-sm text-gray-600 mb-6 flex-wrap md:flex-nowrap">
            <div className="flex items-center gap-2">
               <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-shield h-4 w-4 text-green-500">
                  <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
               </svg>
               <span>No contracts</span>
            </div>
            <div className="flex items-center gap-2">
               <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-circle-check-big h-4 w-4 text-green-500">
                  <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                  <path d="m9 11 3 3L22 4"></path>
               </svg>
               <span>Cancel anytime</span>
            </div>
            <div className="flex items-center gap-2">
               <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-clock h-4 w-4 text-green-500">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
               </svg>
               <span>5-min setup</span>
            </div>
         </div>
         <div className="flex items-center justify-center md:gap-8 gap-4 text-sm text-gray-600 flex-wrap md:flex-nowrap">
            <div className="text-center">
               <div className="text-xl font-bold text-gray-900">15k+</div>
               <div>businesses trust us</div>
            </div>
            <div className="text-center">
               <div className="text-xl font-bold text-gray-900">40%</div>
               <div>more bookings typical</div>
            </div>
            <div className="text-center">
               <div className="text-xl font-bold text-gray-900">24/7</div>
               <div>revenue capture</div>
            </div>
         </div>
      </div>
   </section>
</div>
   
    </div>
    </>
  );
}