

import { CalendlyEmbed } from '@/app/_components/General/Custom';
import './module.scss';
import React from 'react';
import { AppendUTMToAnchor } from '@/app/_components/General/Custom';
import Script from 'next/script';
import { Metadata } from 'next';
import { FAQ } from './faq';
import ClickFeaturepage from './onClickFeature';
import HoverFeaturepage from './onHoverFeature';


export const metadata: Metadata = {
    title: 'Service Area Management Software for Home Service Business',
    description: 'Validate service zones and capture profitable online bookings only. Eliminate fuel waste and time loss by serving areas you can deliver to.',
    robots: 'index, follow',
    alternates: {
      canonical: 'https://fieldcamp.ai/online-booking/service-area-management/'
    }
  };
  


const pageTitle = 'Service Area Management Software for Home Service Business';
const pageDescription = 'Validate service zones and capture profitable online bookings only. Eliminate fuel waste and time loss by serving areas you can deliver to.';
const pageUrl = 'https://fieldcamp.ai/online-booking/service-area-management/';
type FAQItem = {
    question: string;
    answer: string;
  };
 
 type FAQCategory = 'customers' | 'setup' | 'smartFeatures' | 'results';
 
 const faqs: Record<FAQCategory, FAQItem[]> = {
    customers: [
      {
        question: "What is service area management in FieldCamp?",
        answer: "Service area management defines the geographic boundaries where your business provides services. The system checks customer addresses against these boundaries before allowing bookings."
      },
      {
        question: "Can I customize my service area boundaries?",
        answer: "Yes. You can set up clear zones to match your coverage area and adjust them anytime as your business expands or contracts."
      },
      {
        question: "Can I set different service zones for different services?",
        answer: "Yes. You can create separate zones for different services. For example, a larger zone for general maintenance and a smaller one for specialized jobs."
      }
    ],
    setup: [
      {
        question: "How do I configure service areas in FieldCamp?",
        answer: "During setup, you define your service boundaries in the system. Customers are required to enter their address, which is validated against these boundaries before booking."
      },
      {
        question: "How often should I review service area boundaries?",
        answer: "It’s recommended to review them regularly based on technician availability, travel time, operational costs, and demand patterns."
      }
    ],
    smartFeatures: [
      {
        question: "How does address validation work?",
        answer: "When customers enter their address in the booking widget or chat, FieldCamp verifies if it’s within your service zone. Inside zone: They see “Good news! We service your area.” Outside zone: The system blocks the booking and redirects them."
      },
      {
        question: "Do service area rules apply across all booking methods?",
        answer: "Yes. Whether customers book through the widget, live chat, or customer portal, the same service area validation applies."
      }
    ],
    results: [
      {
        question: "Why is service area validation important?",
        answer: "Service area validation helps your business avoid unprofitable or distant jobs, optimize technician travel routes, prevent scheduling conflicts, and deliver faster, more reliable service to customers."
      },
      {
        question: "How does service area management affect customer experience?",
        answer: "Customers get instant clarity if you serve their location. This reduces booking frustration and builds trust in your business."
      }
    ]
  };
  const faqItems = Object.values(faqs).flat();

  const tabs = [
    { id: 'customers' as const, label: 'Getting Started' },
    { id: 'setup' as const, label: 'Setup' },
    { id: 'smartFeatures' as const, label: 'Smart Features' },
    { id: 'results' as const, label: 'Results' }
  ];

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
export default function ServiceAreaManagement() {


   return (
      <>
         <Script
            id="structured-data"
            type="application/ld+json"
            dangerouslySetInnerHTML={{ __html: JSON.stringify(schemaData) }}
         />

         <AppendUTMToAnchor />
         <div className="ppc-template min-h-screen bg-white  relative">
            <CalendlyEmbed />
            <div className="absolute inset-0 bg-[linear-gradient(to_right,#e5e7eb_1px,transparent_1px),linear-gradient(to_bottom,#e5e7eb_1px,transparent_1px)] bg-[size:20px_20px] opacity-40"></div>
            <div className="relative z-10">

               <section className="py-20 px-4 relative bg-white overflow-hidden banner-ob">
                  <div className="max-w-7xl mx-auto">
                     <div className="grid lg:grid-cols-2 gap-12 items-center">

                        <div className="space-y-6">
                           <h1 className="text-4xl lg:text-5xl xl:text-6xl font-bold text-transparent">
                              <span className="text-black">Smart Territory Control for</span> <span
                                 className="bg-gradient-to-r from-red-500 to-orange-500 bg-clip-text"> Field Service
                                 Excellence</span>
                           </h1>
                           <p className="text-lg lg:text-xl text-gray-700 leading-relaxed">
                              Filter profitable jobs with smart service area checks. Get real-time geo-verification,
                              updated jobs, instant time slots, and calendar sync. This avoids wasted trips and you
                              can serve right customers faster.
                           </p>

                           <div className="mt-8 md:mt-10 flex flex-col sm:flex-row gap-4">

                              <a href="https://app.fieldcamp.ai/signup"
                                 className="min-w-[35%] inline-flex utm-medium-signup items-center justify-center gap-2 md:whitespace-nowrap ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:text-accent-foreground md:h-11 rounded-md px-8 py-2 md:py-4 text-base md:text-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 font-semibold shadow-lg">Start
                                 Free Trial</a>
                              <a href="https://calendly.com/jeel-fieldcamp/30min" target="_blank" rel="noopener noreferrer"
                                 data-medium="Banner-cta-free-trail"
                                 className="min-w-[35%] calendly-open inline-flex items-center justify-center gap-2 md:whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary hover:bg-primary/90 md:h-11 rounded-md px-8 py-2 md:py-4 text-base md:text-lg bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold shadow-xl transform hover:scale-105 transition-all duration-200 border-0">Book
                                 a Demo </a>
                           </div>

                        </div>


                        <div className="relative">

                           <div className="hero-card transform rotate-1">
                              <div className="aspect-[4/3] mb-4 rounded-2xl overflow-hidden">
                                 <img
                                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/service-area-management-banner-image.svg"
                                    alt="Smart Territory Control Dashboard"
                                    className="w-full h-full object-cover"
                                 />
                              </div>
                           </div>


                           <div className="absolute -top-4 -right-4 hero-card transform -rotate-3 max-w-xs">
                              <div className="text-center">
                                 <div className="text-sm text-gray-600 mb-2">Service Area Status</div>
                                 <div className="space-y-2">
                                    <div className="flex justify-between items-center">
                                       <span className="text-sm">North Zone</span>
                                       <span className="w-3 h-3 bg-green-500 rounded-full"></span>
                                    </div>
                                    <div className="flex justify-between items-center">
                                       <span className="text-sm">South Zone</span>
                                       <span className="w-3 h-3 bg-blue-500 rounded-full"></span>
                                    </div>
                                 </div>
                              </div>
                           </div>

                           <div className="absolute -bottom-4 -left-4 hero-card transform rotate-2 max-w-xs">
                              <div className="text-center">
                                 <div className="text-xs text-green-600 font-semibold mb-1">✓ VALIDATED</div>
                                 <div className="text-sm font-medium">Johnson Residence</div>
                                 <div className="text-xs text-gray-600">Within service area</div>
                              </div>
                           </div>
                        </div>
                     </div>



                  </div>
               </section>

               <section className="py-20 px-4 relative cost-mng">
                <div className="network-pattern"></div>
                <div className="max-w-7xl mx-auto relative z-10">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl lg:text-4xl xl:text-5xl font-bold mb-6  text-transparent">
                            <span className="text-black">The Hidden Cost of Poor </span> <span
                                className="bg-gradient-to-r from-red-500 to-orange-500 bg-clip-text">Service Area
                                Management</span>
                        </h2>
                        <p className="text-lg lg:text-xl text-black-300 max-w-4xl mx-auto leading-relaxed">
                            Field service businesses lose thousands annually due to territory planning issues. Here's
                            what it really costs when you don't define your service boundaries effectively.
                        </p>
                    </div>

                    <div className="grid md:grid-cols-3 gap-8">
                        
                        <div
                            className="bg-white/80 backdrop-blur-sm rounded-2xl p-6 md:p-8 lg:p-10 shadow-2xl border border-white/50 ring-1 ring-black/5">

                            <div className="pt-8">
                                <h3 className="text-2xl lg:text-3xl font-bold mb-4">$150-300 Per Failed Visit</h3>
                                <p
                                    className="text-muted-foreground text-base md:text-lg leading-relaxed mb-6 leading-relaxed">
                                    Each time a technician accepts a job outside your optimal service radius, you lose
                                    money on wasted fuel, wages, and lost productivity.
                                </p>
                                <ul className="space-y-3 text-sm text-black-400">
                                    <li className="flex items-start">
                                        <span className="text-red-400 mr-2">•</span>
                                        Technician wages for failed trips
                                    </li>
                                    <li className="flex items-start">
                                        <span className="text-red-400 mr-2">•</span>
                                        Fuel costs for long-distance travel
                                    </li>
                                    <li className="flex items-start">
                                        <span className="text-red-400 mr-2">•</span>
                                        Lost opportunity for profitable jobs
                                    </li>
                                </ul>
                            </div>
                        </div>

                        
                        <div
                            className="bg-white/80 backdrop-blur-sm rounded-2xl p-6 md:p-8 lg:p-10 shadow-2xl border border-white/50 ring-1 ring-black/5">

                            <div className="pt-8">
                                <h3 className="text-2xl lg:text-3xl font-bold mb-4">1-2 Hours Wasted Daily</h3>
                                <p
                                    className="text-muted-foreground text-base md:text-lg leading-relaxed mb-6 leading-relaxed">
                                    Poor territory management and ineffective route coordination burns valuable time
                                    that could be spent on revenue-generating activities.
                                </p>
                                <ul className="space-y-3 text-sm text-black-400">
                                    <li className="flex items-start">
                                        <span className="text-blue-400 mr-2">•</span>
                                        Office staff checking addresses manually
                                    </li>
                                    <li className="flex items-start">
                                        <span className="text-blue-400 mr-2">•</span>
                                        Awkward territory conversations with customers
                                    </li>
                                    <li className="flex items-start">
                                        <span className="text-blue-400 mr-2">•</span>
                                        Reputation damage from last-minute cancellations
                                    </li>
                                </ul>
                            </div>
                        </div>

                        
                        <div
                            className="bg-white/80 backdrop-blur-sm rounded-2xl p-6 md:p-8 lg:p-10 shadow-2xl border border-white/50 ring-1 ring-black/5">

                            <div className="pt-8">
                                <h3 className="text-2xl lg:text-3xl font-bold mb-4">$36,000-60,000 Annual Cost</h3>
                                <p
                                    className="text-muted-foreground text-base md:text-lg leading-relaxed mb-6 leading-relaxed">
                                    For a typical 5-crew operation, poor territory management compounds into massive
                                    yearly losses that crush your bottom line.
                                </p>
                                <ul className="space-y-3 text-sm text-black-400">
                                    <li className="flex items-start">
                                        <span className="text-green-400 mr-2">•</span>
                                        Cumulative failed visit costs
                                    </li>
                                    <li className="flex items-start">
                                        <span className="text-green-400 mr-2">•</span>
                                        Administrative overhead
                                    </li>
                                    <li className="flex items-start">
                                        <span className="text-green-400 mr-2">•</span>
                                        Lost customer acquisition opportunities
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
               </section>

               <HoverFeaturepage/>

               <section className="py-20 px-4 comparison-section bg-white relative overflow-hidden">
                <div className="max-w-7xl mx-auto">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl lg:text-4xl xl:text-5xl font-bold text-gray-900 mb-6">
                            What Makes FieldCamp's Service Area Intelligence Superior to Basic Mapping Tools
                        </h2>
                        <p className="text-lg lg:text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                            Generic mapping shows where customers live, but FieldCamp shows where your business actually
                            makes money. Here's how:
                        </p>
                    </div>

                    <div className="grid lg:grid-cols-2 gap-12">
                        
                        <div className="comparison-negative">
                            <div className="flex items-center mb-6">
                                <h3 className="text-2xl font-bold text-red-800">Generic Territory Tools</h3>
                            </div>

                            <div className="space-y-4">
                                <ul>
                                    <li>
                                        <div className="mb-4">
                                            <h4 className="font-semibold text-red-800 mb-2">Don't Understand Service Areas
                                            </h4>
                                            <p className="text-red-700 text-sm leading-relaxed">Don't confirm if you serve a
                                                customer's location or respect real service boundaries.</p>
                                        </div>
                                    </li>

                                    <li>
                                        <div className="mb-4">
                                            <h4 className="font-semibold text-red-800 mb-2">Can't Check Technician
                                                Availability</h4>
                                            <p className="text-red-700 text-sm leading-relaxed">No real-time sync to your
                                                team's schedule, workload, or technician expertise by area.</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div className="mb-4">
                                            <h4 className="font-semibold text-red-800 mb-2">Don't Create Jobs Automatically
                                            </h4>
                                            <p className="text-red-700 text-sm leading-relaxed">Every inquiry needs manual
                                                follow-up, data entry, and scheduling effort.</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div className="space-y-4">
                                <ul>
                                    <li>
                                        <div className="mb-4">
                                            <h4 className="font-semibold text-red-800 mb-2">No Territory Performance
                                                Tracking</h4>
                                            <p className="text-red-700 text-sm leading-relaxed">Can't analyze which service
                                                areas
                                                are most profitable or guide expansion decisions</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div className="mb-4">
                                            <h4 className="font-semibold text-red-800 mb-2">Require Constant Manual
                                                Monitoring</h4>
                                            <p className="text-red-700 text-sm leading-relaxed">Depend on staff to reply to
                                                every
                                                inquiry and validate requests.</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div className="mt-6 p-4 bg-red-100 rounded-lg border border-red-200">
                                <p className="text-red-800 text-sm italic font-medium">Result: Like having a receptionist
                                    who doesn't know your business, service areas, or team capabilities</p>
                            </div>
                        </div>

                        
                        <div className="comparison-positive">
                            <div className="flex items-center mb-6">
                                <h3 className="text-2xl font-bold text-green-800">FieldCamp's Service Area Management</h3>
                            </div>

                            <div className="space-y-4">

                                <ul>
                                    <li>
                                        <div className="mb-4">
                                            <h4 className="font-semibold text-green-800 mb-2">Knows Your Territory Inside
                                                Out</h4>
                                            <p className="text-green-700 text-sm leading-relaxed">Engages only the customers
                                                you
                                                serve based predefined service boundary mapping</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div className="mb-4">
                                            <h4 className="font-semibold text-green-800 mb-2">Books Real Appointments
                                                Instantly</h4>
                                            <p className="text-green-700 text-sm leading-relaxed">Tied directly to your
                                                schedule,
                                                technician availability, and capacity.</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div className="mb-4">
                                            <h4 className="font-semibold text-green-800 mb-2">Creates Jobs Automatically
                                            </h4>
                                            <p className="text-green-700 text-sm leading-relaxed">Once the territory gets
                                                validated
                                                it flows automatically into job creation and scheduling</p>
                                        </div>
                                    </li>
                                </ul>





                            </div>

                            <div className="space-y-4">
                                <ul>
                                    <li>
                                        <div className="mb-4">
                                            <h4 className="font-semibold text-green-800 mb-2">Optimizes Territory
                                                Profitability</h4>
                                            <p className="text-green-700 text-sm leading-relaxed">Tracks revenue by service
                                                area and
                                                provides insights for strategic expansion</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div className="mb-4">
                                            <h4 className="font-semibold text-green-800 mb-2">Works Completely Autonomous
                                            </h4>
                                            <p className="text-green-700 text-sm leading-relaxed">Validates, books, and
                                                creates jobs
                                                24/7 with no staff needed.</p>
                                        </div>
                                    </li>
                                </ul>



                            </div>

                            <div className="mt-6 p-4 bg-green-100 rounded-lg border border-green-200">
                                <p className="text-green-800 text-sm italic font-medium">Result: Like having an experienced
                                    dispatcher who knows your business, territories, and team capabilities by heart</p>
                            </div>
                        </div>
                    </div>
                </div>
               </section>

               <ClickFeaturepage/>

               <section className="py-20 px-4 control-step">
                <div className="max-w-7xl mx-auto">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl lg:text-4xl xl:text-5xl font-bold text-gray-900 mb-6">
                            Know Your Best Areas, Spot New Opportunities,<br></br>
                            and Expand the Right Way
                        </h2>
                        <p className="text-lg lg:text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                            Turn every service zone into a smart decision backed by data.
                        </p>
                    </div>

                    
                    <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    
                        <div className="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300">
                            <div className="rounded-2xl mb-4 shadow-sm overflow-hidden">
                                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/stop-wasting-time-on-wrong-addresses.svg" alt="No More Wrong Addresses"
                                    className="w-full h-auto object-cover"></img>
                            </div>
                            <h3 className="text-xl font-bold mb-3 text-gray-900">No More Wrong Addresses</h3>
                            <p className="text-gray-600 leading-relaxed">
                                Every booking is checked against your service zones. No wasted trips or awkward "sorry,
                                not in our area" calls. Only jobs your team can deliver.
                            </p>
                        </div>

                    
                        <div className="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300">
                            <div className="rounded-2xl mb-4 shadow-sm overflow-hidden">
                                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/schedule-jobs-your-way-simple-or-combined.svg" alt="Flexible Job Scheduling"
                                    className="w-full h-auto object-cover"></img>
                            </div>
                            <h3 className="text-xl font-bold mb-3 text-gray-900">Flexible Job Scheduling</h3>
                            <p className="text-gray-600 leading-relaxed">
                                Schedule multiple services in one visit or separate appointments across different days.
                                Stay in control, reduce reschedules, & keep customers happy.
                            </p>
                        </div>

                        
                        <div className="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300">
                            <div className="rounded-2xl mb-4 shadow-sm overflow-hidden">
                                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/always-show-customers-the-right-time-slots.svg" alt="Accurate Time Slots"
                                    className="w-full h-auto object-cover"></img>
                            </div>
                            <h3 className="text-xl font-bold mb-3 text-gray-900">Accurate Time Slots</h3>
                            <p className="text-gray-600 leading-relaxed">
                                Customers see only real-time availability your team can handle. Smooth calendars, fewer
                                conflicts, and a seamless booking experience.
                            </p>
                        </div>

                        
                        <div className="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300">
                            <div className="rounded-2xl mb-4 shadow-sm overflow-hidden">
                                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/the-right-technician-for-every-job.svg" alt="Right Tech for Every Job"
                                    className="w-full h-auto object-cover"></img>
                            </div>
                            <h3 className="text-xl font-bold mb-3 text-gray-900">Right Tech for Every Job</h3>
                            <p className="text-gray-600 leading-relaxed">
                                Skill, location, and workload are factored automatically. The best technician gets the
                                job—done faster, better, and with less stress.
                            </p>
                        </div>

                        
                        <div className="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300">
                            <div className="rounded-2xl mb-4 shadow-sm overflow-hidden">
                                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/communication-without-lifting-a-finger.svg"
                                    alt="Automatic Client Communication" className="w-full h-auto object-cover"></img>
                            </div>
                            <h3 className="text-xl font-bold mb-3 text-gray-900">Automatic Client Communication</h3>
                            <p className="text-gray-600 leading-relaxed">
                                Confirmations, reminders, and receipts go out instantly. Stay professional and
                                responsive without wasting hours on manual follow-ups.
                            </p>
                        </div>

                        
                        <div className="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300">
                            <div className="rounded-2xl mb-4 shadow-sm overflow-hidden">
                                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/adapt-quickly-as-things-change.svg" alt="Adapt Quickly as Things Change"
                                    className="w-full h-auto object-cover"></img>
                            </div>
                            <h3 className="text-xl font-bold mb-3 text-gray-900">Adapt Quickly as Things Change</h3>
                            <p className="text-gray-600 leading-relaxed">
                                Tech sick? Demand spike? New area to cover? Adjust service zones and schedules instantly
                                so your business never misses a beat.
                            </p>
                        </div>
                    </div>
                </div>
               </section>

               <section className="bg-white py-20 px-4">
                <div className="max-w-7xl mx-auto">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl lg:text-4xl xl:text-5xl font-bold text-gray-900 mb-6">
                            What Our Users Are Saying
                        </h2>
                        <p className="text-lg lg:text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                            Let AI handle the thinking while you focus on growing and keeping customers happy
                        </p>
                    </div>

                    <div className="grid lg:grid-cols-3 gap-8">
                        
                        <div className="feature-card text-center">
                            <div className="flex justify-center mb-4">
                                <div className="flex text-yellow-400">
                                    <span className="text-xl">★★★★★</span>
                                </div>
                            </div>
                            <p className="text-gray-700 mb-6 leading-relaxed italic">
                                "I didn't even know how broken our territory processes were until we saw how FieldCamp
                                handled them. From basic territory checking to automated assignment, it feels like
                                everything just... flows better now."
                            </p>
                            <div className="flex items-center justify-center">
                                <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <span className="text-blue-600 font-semibold text-lg">K</span>
                                </div>
                                <div className="text-left">
                                    <div className="font-semibold text-gray-900">Karen</div>
                                    <div className="text-sm text-gray-600">Operations Manager at Home Appliance Repair</div>
                                </div>
                            </div>
                        </div>

                        
                        <div className="feature-card text-center">
                            <div className="flex justify-center mb-4">
                                <div className="flex text-yellow-400">
                                    <span className="text-xl">★★★★★</span>
                                </div>
                            </div>
                            <p className="text-gray-700 mb-6 leading-relaxed italic">
                                "We've been running field service operations for years but nothing brought this much
                                clarity to our territory processes until FieldCamp. Whether builder gives us real-time
                                access to job details, and simple scheduling just makes our day smoother."
                            </p>
                            <div className="flex items-center justify-center">
                                <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <span className="text-green-600 font-semibold text-lg">T</span>
                                </div>
                                <div className="text-left">
                                    <div className="font-semibold text-gray-900">Tyler</div>
                                    <div className="text-sm text-gray-600">Owner of Field Operations at Landscaping Services
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div className="feature-card text-center">
                            <div className="flex justify-center mb-4">
                                <div className="flex text-yellow-400">
                                    <span className="text-xl">★★★★★</span>
                                </div>
                            </div>
                            <p className="text-gray-700 mb-6 leading-relaxed italic">
                                "As a small field service organization, we needed something that didn't require tons of
                                training. FieldCamp gave our employees clear workflows, better scheduling, and
                                visibility into resources. It honestly helped us save time and money."
                            </p>
                            <div className="flex items-center justify-center">
                                <div className="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <span className="text-purple-600 font-semibold text-lg">M</span>
                                </div>
                                <div className="text-left">
                                    <div className="font-semibold text-gray-900">Marcus</div>
                                    <div className="text-sm text-gray-600">Field Team Lead at Electrical Contracting Service
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
               </section>

               

               <section className="py-20 px-4 bottom-line">
                <div className="max-w-4xl mx-auto">
                    <div className="bottom-line-card">
                        <h3 className="text-3xl lg:text-4xl font-bold text-gray-900 mb-6">
                            Win More Profitable Service Area Jobs
                        </h3>

                        <p className="text-lg lg:text-xl text-gray-600 mb-8 max-w-3xl mx-auto leading-relaxed">
                            Bad jobs, wasted travel, missed opportunities in good areas - the longer you wait, the more
                            profits slip away to competitors with better geographic strategy. Ready to take control of
                            your service areas?
                        </p>

                        <div className="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="https://app.fieldcamp.ai/signup" target="_blank" className="utm-medium-signup" rel="noopener noreferrer">
                                <button className="btn-blue-primary text-lg">Get Started for FREE</button>
                            </a>

                            <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open" target="_blank"
                                rel="noopener noreferrer">
                                <button className="btn-white-secondary text-lg">Book a Demo Now</button>
                            </a>


                        </div>
                    </div>
                </div>
               </section>

               <section className="relative py-10 md:py-20 px-6 lg:px-8 bg-white overflow-hidden">
                  <div className="mx-auto max-w-7xl relative z-10">
                     <FAQ tabs={tabs} faqs={faqs}/>
                  </div>
               </section>


            </div>

         </div>
      </>
   );
}