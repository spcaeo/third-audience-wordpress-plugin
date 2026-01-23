import React from 'react';
import { Metadata } from 'next';
import Script from 'next/script';
import Accordion from '@/app/_components/Accordion';
import AIReceptionistVideo from './AIReceptionistVideo';

export const metadata: Metadata = {
  title: 'AI Receptionist for Field Service Businesses | FieldCamp',
  description: 'AI Receptionist for plumbers, contractors & field service teams. Answer calls 24/7, book jobs, and handle customer questions automatically.',
  robots: 'index, follow',
  alternates: {
    canonical: 'https://fieldcamp.ai/ai-receptionist'
  }
};

const pageTitle = metadata.title?.toString() || 'FieldCamp AI Receptionist';
const pageDescription = metadata.description || '';
const pageUrl = metadata.alternates?.canonical?.toString() || 'https://fieldcamp.ai/ai-receptionist';

const faqItems = [
  { 
    title: "Can I prevent our clients from seeing unfinished work?", 
    content: [
      "Yes, absolutely. FieldCamp's AI Receptionist only shares information you've specifically uploaded and approved. You have complete control over what your AI knows and what it can share with customers. Unfinished work, internal notes, and private documentation remain private."
    ]
  },
  { 
    title: "Can I link my files from Google Docs, Figma, Dropbox, Airtable, and other apps?", 
    content: [
      "Yes, you can connect files from Google Drive, Dropbox, OneDrive, and other cloud storage platforms. Simply share the files or upload them directly to your AI's knowledge base. The AI will learn from all your documentation regardless of where it's stored."
    ]
  },
  { 
    title: "Can I see everything I need to do on a single screen?", 
    content: [
      "Yes, FieldCamp's dashboard shows all your AI Receptionist activity in one place. See incoming calls, booked appointments, message summaries, and required follow-ups on a single screen. No switching between multiple apps or interfaces."
    ]
  },
  { 
    title: "Can I see everything that I can do on a single screen?", 
    content: [
      "Absolutely. The AI Receptionist control panel gives you access to all features from one interface - manage knowledge base, edit workflows, view call logs, update availability, and monitor performance metrics all from a unified dashboard."
    ]
  },
  { 
    title: "Can I set questions even if some of my team can see?", 
    content: [
      "Yes, you can create custom qualification questions and workflows that only your AI uses during calls. Team members can see the results and summaries, but the specific qualifying questions and decision trees remain in your AI's training - not visible to your staff."
    ]
  },
  { 
    title: "Can I see all the work that was completed on any given day?", 
    content: [
      "Yes, your AI Receptionist provides daily activity reports showing all calls handled, appointments booked, leads qualified, and follow-ups completed. You get a comprehensive summary of your AI's work performance every day."
    ]
  },
  { 
    title: "Can I tell my clients that only some of my team can see?", 
    content: [
      "Yes, your AI can communicate role-based access clearly to customers. It can explain that only relevant team members (like your sales team for quotes, or service managers for technical questions) will see their specific information."
    ]
  },
  { 
    title: "Can I see everything that assigned to someone on a single page?", 
    content: [
      "Yes, the AI Receptionist dashboard shows all assignments by team member. See which calls were transferred to whom, what appointments were scheduled for each technician, and what follow-ups are assigned to specific staff members."
    ]
  },
  { 
    title: "Can I see exactly who worked on what today? Yesterday? Last week?", 
    content: [
      "Yes, detailed activity logs show exactly which team members handled transferred calls, who received appointment assignments, and what work was distributed by your AI Receptionist on any date range you specify."
    ]
  },
  { 
    title: "Can I easily reference an entire conversation from a year ago?", 
    content: [
      "Yes, all conversations are permanently stored and searchable. Use our search function to find any call by customer name, phone number, date, or keywords discussed. Complete transcripts and summaries are available for every interaction."
    ]
  },
  { 
    title: "Can I see everything that's happened across multiple projects in one place?", 
    content: [
      "Yes, the AI Receptionist provides a unified view of all customer interactions across all your service lines and projects. See complete communication history, appointment patterns, and relationship timeline for each customer account."
    ]
  },
  { 
    title: "Can I see everything that's assigned to someone on a single page?", 
    content: [
      "Yes, each team member has a dedicated assignment view showing all calls transferred to them, appointments scheduled with them, and follow-up tasks assigned by your AI Receptionist, organized by priority and due date."
    ]
  },
  { 
    title: "Can I mention someone so they're notified about something?", 
    content: [
      "Yes, your AI Receptionist can send immediate notifications to specific team members when certain conditions are met - emergency calls, high-value leads, or customer escalations. Notifications can be sent via text, email, or push notification."
    ]
  },
  { 
    title: "Can I tell that someone's done work over a long period of time?", 
    content: [
      "Yes, performance analytics show each team member's call handling volume, appointment booking success rates, and customer satisfaction scores over any time period. Track individual and team performance trends over weeks, months, or years."
    ]
  },
  { 
    title: "Can I see everything that's assigned to someone on a single page?", 
    content: [
      "Yes, the individual assignment dashboard consolidates all work items for each team member - incoming transferred calls, scheduled appointments, pending follow-ups, and priority tasks - all visible on one organized page."
    ]
  }
];

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
      "name": "FieldCamp AI Receptionist",
      "url": pageUrl,
      "description": pageDescription,
      "image": "https://fieldcamp.ai/_next/static/media/logo.6811b83e.svg"
    }
  },
  {
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "FieldCamp AI Receptionist",
    "description": "24/7 AI-powered call handling and appointment scheduling for field service businesses. Never miss a call or booking opportunity again.",
    "url": pageUrl,
    "image": "https://fieldcamp.ai/_next/static/media/logo.6811b83e.svg",
    "brand": {
      "@type": "Brand",
      "name": "FieldCamp"
    },
    "offers": {
      "@type": "Offer",
      "priceCurrency": "USD",
      "url": "https://fieldcamp.ai/pricing/",
      "availability": "https://schema.org/InStock"
    }
  }
];

export default function AIReceptionist() {
  return (
    <div className="ai-receptionist-page">
      <Script
        id="structured-data"
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(schemaData) }}
      />
      
      {/* Hero Section */}
      <section className="relative bg-white overflow-hidden py-[50px] md:py-[70px] lg:py-[90px]">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0 pt-[50px] md:pt-[70px] lg:pt-[90px]">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-12 items-start">
            {/* Left Column - Content */}
            <div className="space-y-6 md:space-y-8">
              <div className="space-y-4 md:space-y-6">
                <div className="text-[12px] md:text-[14px] font-medium text-gray-500 uppercase tracking-wider">
                  MEET FIELDCAMP'S
                </div>
                <h1 className="text-[32px] md:text-[42px] lg:text-[52px] font-bold text-gray-900 leading-[1.15]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">AI Receptionist</span> for Field Service Businesses
                </h1>
              </div>
              <div className="">
                <a 
                  href="https://calendly.com/jeel-fieldcamp/30min" 
                  className="calendly-open inline-flex items-center justify-center px-6 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity shadow-lg bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white"
                  style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}
                >
                  Book a Demo
                </a>
              </div>
            </div>

            {/* Right Column - Supporting Text */}
            <div className="space-y-6 md:space-y-8 lg:pl-8 mt-8 lg:mt-12">
              <div className="space-y-4 md:space-y-6">
                <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Your FieldCamp AI Receptionist answers calls 24/7, schedules appointments directly in your calendar, and handles customer questions exactly how you trained it. No hallucinations. No wrong answers. Just reliable call handling that sounds like your best employee.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="relative">
            <div className="bg-gradient-to-b from-blue-100 to-blue-200 rounded-3xl shadow-2xl p-2 lg:p-12">
              <AIReceptionistVideo />
            </div>
              
              {/* Optional: Video title and description */}
              <div className="mt-6 text-center">
                <h3 className="text-xl font-semibold text-gray-900 mb-2" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  See Your AI Receptionist in Action
                </h3>
                <p className="text-gray-600" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Watch how FieldCamp's AI Receptionist handles customer calls, books appointments, and answers questions with perfect accuracy.
                </p>
              </div>
            </div>
          </div>
        
      </section>

      
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="text-center space-y-6 max-w-4xl mx-auto">
            <div className="space-y-4">
              <div className="text-sm font-medium text-gray-500 uppercase tracking-wider">
                WHAT FIELDCAMP AI RECEPTIONIST DOES
              </div>
              <h2 className="text-4xl lg:text-5xl font-bold text-gray-900 leading-tight" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                More Than Just An <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Answering Service</span>
              </h2>
            </div>
            
            <div className="space-y-6">
              <p className="text-lg text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                FieldCamp AI Receptionist isn't another voicemail replacement—it's intelligent call handling infrastructure built specifically for field service businesses.
              </p>
            </div>
          </div>
        </div>
      </section>

      
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            {/* Left Column - Content */}
            <div className="space-y-6">
              <div className="space-y-4">
                <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Knowledge That <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Never Forgets</span>
                </h2>
                <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Upload once. Answer perfectly forever.
                </p>
              </div>
              
              <div className="space-y-6">
                <p className="text-lg text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Your AI receptionist learns your entire business from day one. Services, pricing, availability, policies—upload any document, and your AI instantly becomes an expert. No scripts. No hallucinations. Just accurate answers based on your actual business information.
                </p>
                
                <div className="space-y-4">
                  <h4 className="font-semibold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                    What you can upload:
                  </h4>
                  <div className="space-y-3">
                    <div className="flex items-start space-x-3">
                      <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                      <span className="text-gray-700" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                        Service menus and pricing sheets
                      </span>
                    </div>
                    <div className="flex items-start space-x-3">
                      <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                      <span className="text-gray-700" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                        FAQ documents and company policies
                      </span>
                    </div>
                    <div className="flex items-start space-x-3">
                      <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                      <span className="text-gray-700" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                        Website content and marketing materials
                      </span>
                    </div>
                    <div className="flex items-start space-x-3">
                      <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                      <span className="text-gray-700" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                        Technical specifications and warranty terms
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Right Column - Image Placeholder */}
            <div>
              <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/view-reception.webp"
                  alt="FieldCamp CTA Illustration"
                  className="cta-ppc-img-nw object-contain"
                />
            </div>
          </div>
        </div>
      </section>

      
      <section className="py-[50px] md:py-[70px] lg:py-[90px] bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            {/* Left Column - Image Placeholder */}
            <div className="order-2 lg:order-1">
              <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/virtchual-reception.webp"
                  alt="FieldCamp CTA Illustration"
                  className="cta-ppc-img-nw object-contain"
                />
            </div>

            {/* Right Column - Content */}
            <div className="order-1 lg:order-2 space-y-6">
              <div className="space-y-4">
                <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Workflows That <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Think Like You Do</span>
                </h2>
                <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Program your AI with simple rules. No code required.
                </p>
              </div>
              
              <div className="space-y-6">
                <p className="text-lg text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Create unlimited workflows using plain English. Tell your AI exactly what to do in any situation—just like training a new employee, but they remember everything perfectly.
                </p>
                
                <div className="space-y-4">
                  <h4 className="font-semibold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                    Common workflows ready to use:
                  </h4>
                  <div className="space-y-4">
                    <div className="space-y-1">
                      <div className="flex items-start space-x-3">
                        <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                        <div className="flex-1">
                          <span className="font-semibold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                            Emergency Router
                          </span>
                          <span className="text-gray-700 ml-1" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                            - Detects urgent keywords and immediately alerts on-call staff
                          </span>
                        </div>
                      </div>
                    </div>
                    <div className="space-y-1">
                      <div className="flex items-start space-x-3">
                        <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                        <div className="flex-1">
                          <span className="font-semibold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                            Quote Calculator
                          </span>
                          <span className="text-gray-700 ml-1" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                            - Gathers project details and provides instant estimates
                          </span>
                        </div>
                      </div>
                    </div>
                    <div className="space-y-1">
                      <div className="flex items-start space-x-3">
                        <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                        <div className="flex-1">
                          <span className="font-semibold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                            Appointment Scheduler
                          </span>
                          <span className="text-gray-700 ml-1" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                            - Books directly into your calendar with proper buffer time
                          </span>
                        </div>
                      </div>
                    </div>
                    <div className="space-y-1">
                      <div className="flex items-start space-x-3">
                        <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                        <div className="flex-1">
                          <span className="font-semibold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                            Lead Qualifier
                          </span>
                          <span className="text-gray-700 ml-1" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                            - Asks screening questions and scores prospects
                          </span>
                        </div>
                      </div>
                    </div>
                    <div className="space-y-1">
                      <div className="flex items-start space-x-3">
                        <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                        <div className="flex-1">
                          <span className="font-semibold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                            Payment Collector
                          </span>
                          <span className="text-gray-700 ml-1" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                            - Securely processes payments over the phone
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            {/* Left Column - Content */}
            <div className="space-y-6">
              <div className="space-y-4">
                <h2 className="text-4xl lg:text-5xl font-bold text-gray-900 leading-tight" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Books Directly Into Your <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">FieldCamp Calendar</span>
                </h2>
                <p className="text-xl text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Seamless scheduling that knows your business.
                </p>
              </div>
              
              <div className="space-y-6">
                <p className="text-lg text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Your AI receptionist books appointments directly into FieldCamp's dispatch calendar during the call. No external tools needed. It sees tech availability, understands job types, and automatically assigns the right person for the job.
                </p>
                
                <div className="space-y-4">
                  <h4 className="font-semibold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                    Intelligent booking features:
                  </h4>
                  <div className="space-y-3">
                    <div className="flex items-start space-x-3">
                      <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                      <span className="text-gray-700" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                        Sees real-time tech availability in dispatch board
                      </span>
                    </div>
                    <div className="flex items-start space-x-3">
                      <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                      <span className="text-gray-700" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                        Assigns jobs based on skills and location
                      </span>
                    </div>
                    <div className="flex items-start space-x-3">
                      <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                      <span className="text-gray-700" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                        Adds appropriate duration for each service type
                      </span>
                    </div>
                    <div className="flex items-start space-x-3">
                      <div className="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                      <span className="text-gray-700" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                        Includes travel time between appointments
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Right Column - Image Placeholder */}
            <div>
              <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/booking-log.webp"
                  alt="FieldCamp CTA Illustration"
                  className="cta-ppc-img-nw object-contain"
                />
            </div>
          </div>
        </div>
      </section>

      
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          
          <div className="grid lg:grid-cols-10 gap-4 lg:gap-12 mb-8">
            <div className="lg:col-span-7 space-y-6">
              <h2 className="text-4xl lg:text-5xl font-bold text-gray-900 leading-tight" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Everything Your Field Service Business Needs
              </h2>
              <p className="text-lg text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Complete call management infrastructure that replaces your entire phone system.
              </p>
            </div>
            <div className="lg:col-span-3"></div>
          </div>

          
          <div className="mb-16 text-center">
            <img 
              src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/bussiness-needs-scaled.webp" 
              alt="Complete Call Management Infrastructure" 
              className="w-full max-w-[1235px] h-auto mx-auto"
              loading="lazy"
              decoding="async"
            />
          </div>

          
          <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 mb-16">
            <div className="space-y-4">
              <h3 className="text-2xl lg:text-3xl font-bold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Smart Call Transfer
              </h3>
              <p className="text-lg text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Route to the right person, every time.<br/><br/>
                Your AI understands context and urgency. It doesn't just transfer blindly—it knows when to send emergency calls to on-call techs, warranty issues to service managers, and sales inquiries to your closers.
              </p>
              <div className='w-full h-auto smart-img'>
              <img 
                src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/call-transfer.webp" 
                alt="Smart Call Transfer" 
                className="w-full h-auto"
                loading="lazy"
                decoding="async"
              />
            </div>
            </div>
            <div className="space-y-4">
              <h3 className="text-2xl lg:text-3xl font-bold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Complete Call Records
              </h3>
              <p className="text-lg text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Every conversation recorded, transcribed, and summarized.<br/><br/>
                Never wonder what was discussed. Every call gets recorded with automatic transcription. AI-generated summaries highlight key points, customer sentiment, and required follow-ups.
              </p>
              <div className='w-full h-auto smart-img'>
              <img 
                src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/Complete-Call-Records.webp" 
                alt="Complete Call Records" 
                className="w-full h-auto"
                loading="lazy"
                decoding="async"
              />
            </div>
            </div>
          </div>

        
          {/* Additional Features Section - Two More */}
          <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 mb-16">
            <div className="space-y-4">
              <h3 className="text-2xl lg:text-3xl font-bold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Instant SMS During Calls
              </h3>
              <p className="text-lg text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Text confirmations, links, and info while still talking.<br/><br/>
                Your AI sends texts without interrupting the conversation. Appointment confirmations, payment links, directions—delivered instantly to their phone while you're still talking.
              </p>
              <div className='w-full h-auto smart-img'>
              <img 
                src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/samll-call-transfer.webp" 
                alt="Instant SMS During Calls" 
                className="w-full h-auto"
                loading="lazy"
                decoding="async"
              />
            </div>
            </div>
            <div className="space-y-4">
              <h3 className="text-2xl lg:text-3xl font-bold text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Natural Intake Forms
              </h3>
              <p className="text-lg text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Gather information conversationally, not robotically.<br/><br/>
                No questionnaires. No "press 1" menus. Your AI collects customer details through natural conversation, automatically populating your CRM with complete information.
              </p>
              <div className='w-full h-auto smart-img'>
              <img 
                src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/intake-forms.webp" 
                alt="Natural Intake Forms" 
                className="w-full h-auto"
                loading="lazy"
                decoding="async"
              />
            </div>
            </div>
          </div>

          
        </div>
      </section>

      {/* Testimonial Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="text-center space-y-6">
            <blockquote className="text-2xl lg:text-4xl text-gray-900 leading-tight max-w-4xl mx-auto" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              "Our AI Receptionist handles 80% of our incoming calls without any human intervention. Customers love that they can book appointments at 2 AM, and we love that we never miss a lead."
            </blockquote>
            <div className="space-y-2">
              <p className="text-lg text-gray-900" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                – <strong>Mike Rodriguez</strong>
              </p>
              <p className="text-lg text-gray-600" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Owner, Rodriguez HVAC Services
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* FAQ Section */}
      <section className="py-12 sm:py-16 lg:py-20 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <h2 className="text-3xl sm:text-4xl lg:text-5xl font-black text-black mb-4 text-center mb-12 sm:mb-16" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>FREQUENTLY ASKED QUESTIONS</h2>
          <div className='max-w-4xl mx-auto'>
            <Accordion items={faqItems}/>
          </div>
        </div>
      </section>

    </div>
  );
}