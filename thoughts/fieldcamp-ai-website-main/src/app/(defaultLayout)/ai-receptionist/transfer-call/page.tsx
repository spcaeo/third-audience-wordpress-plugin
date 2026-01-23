import React from 'react';
import { Metadata } from 'next';
import Script from 'next/script';
import Accordion from '@/app/_components/Accordion';
import { CalendlyEmbed } from '@/app/_components/General/Custom';
import RoutingTab from './RoutingTab';
import AIReceptionistTranscallVideo from './AIReceptionistTranscallVideo'; 

export const metadata: Metadata = {
  title: 'Smart Call Transfer & Routing for AI Receptionist | FieldCamp',
  description: 'Your AI Receptionist knows exactly who should handle each call—routing to the right person, every time. Never lose a customer to poor call routing.',
  robots: 'index, follow',
  alternates: {
    canonical: 'https://fieldcamp.ai/ai-receptionist/transfer-call'
  }
};

const pageTitle = metadata.title?.toString() || 'Smart Call Transfer & Routing for AI Receptionist | FieldCamp';
const pageDescription = metadata.description || '';
const pageUrl = metadata.alternates?.canonical?.toString() || 'https://fieldcamp.ai/ai-receptionist/transfer-call';

const faqItems = [
  {
    title: "How quickly does the AI transfer calls?",
    content: [
      "Transfer begins within 1-2 seconds of identifying the right destination. The actual connection depends on your phone system but typically completes in under 5 seconds total."
    ]
  },
  {
    title: "Can I change routing rules anytime?",
    content: [
      "Yes, routing rules update instantly. Change them through your dashboard and the very next call will use the new rules. No downtime or restart needed."
    ]
  },
  {
    title: "What if the transfer destination doesn't answer?",
    content: [
      "You set the fallback behavior: try another number, take a message, send to voicemail, or return to the AI for alternative help. Every scenario can have its own fallback plan."
    ]
  },
  {
    title: "How many routing scenarios can I create?",
    content: [
      "Unlimited. Create as many routing rules as you need. Most businesses use 10-15 core scenarios and add specialized ones as needed."
    ]
  },
  {
    title: "Can the same person receive calls from different scenarios?",
    content: [
      "Yes, one team member can be the destination for multiple scenarios. For example, your service manager might handle warranties, complaints, and escalations."
    ]
  },
  {
    title: "Does the AI explain why it's transferring?",
    content: [
      "Yes, the AI provides context before and during transfer. Both the caller and the receiving party know exactly why the call is being transferred."
    ]
  },
  {
    title: "Can I route based on caller identity?",
    content: [
      "Yes, the AI can recognize returning customers and route based on their history, VIP status, or assigned account manager."
    ]
  },
  {
    title: "What about international calls?",
    content: [
      "The AI can detect international numbers and route to appropriate team members or provide specific information about international service."
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
      "name": "FieldCamp AI Call Transfer",
      "url": pageUrl,
      "description": pageDescription,
      "image": "https://fieldcamp.ai/_next/static/media/logo.6811b83e.svg"
    }
  },
  {
    "@context": "https://schema.org/",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "name": "Home",
        "item": "https://fieldcamp.ai/"
      },
      {
        "@type": "ListItem",
        "position": 2,
        "name": "AI Receptionist",
        "item": "https://fieldcamp.ai/ai-receptionist"
      },
      {
        "@type": "ListItem",
        "position": 3,
        "name": "Transfer Call",
        "item": "https://fieldcamp.ai/ai-receptionist/transfer-call"
      }
    ]
  }
];

export default function TransferCall() {
  return (
    <div className="transfer-call-page">
      <Script
        id="structured-data"
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(schemaData) }}
      />
      <CalendlyEmbed/>
      
      {/* Hero Section */}
      <section className="relative bg-white overflow-hidden py-[50px] md:py-[70px] lg:py-[90px]">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0 pt-[50px] md:pt-[70px] lg:pt-[90px]">
          <div className="text-center space-y-4 md:space-y-6 max-w-4xl mx-auto">
            <div className="space-y-4 md:space-y-6">
              <div className="text-[12px] md:text-[14px] font-medium text-gray-500 uppercase tracking-wider">
                SMART CALL TRANSFER & ROUTING
              </div>
              <h1 className="text-[32px] md:text-[42px] lg:text-[52px] font-bold text-gray-900 leading-[1.15]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Smart Call Routing</span> That Understands Context
              </h1>
              <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed pb-0" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Your AI Receptionist knows exactly who should handle each call—routing to the right person, every time.
              </p>
              <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Never lose a customer to poor call routing. Your AI understands urgency, intent, and context to connect callers instantly.
              </p>
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
        </div>
      </section> 

      {/* Call Routing Demo Section */}
      <section className="py-[30px] md:py-[50px] lg:py-[60px] bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="relative">
            <div className="rounded-3xl shadow-2xl p-8 lg:p-12" style={{ backgroundColor: 'oklch(91.97% .043758 2.0288)' }}>
              <div className="relative aspect-video bg-white rounded-2xl overflow-hidden">
                {/* Image placeholder */}
                {/* <AIReceptionistTranscallVideo /> */}
                <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transfer-call-feartures-img-scaled.webp"
                    alt="AI call analysis system that routes every call to the perfect team member automatically"
                    className="cta-ppc-img-nw object-contain"
                  />
              </div>
              
              {/* Section title and description */}
              <div className="mt-6 text-center">
                <h3 className="text-xl font-semibold text-black mb-2" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  See Intelligent Routing in Action
                </h3>
                <p className="text-black" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Watch how your AI analyzes each call and routes to the perfect team member every time.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section> 

      {/* How Intelligent Routing Works Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="text-center space-y-6 max-w-4xl mx-auto mb-16">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              How <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Intelligent Routing</span> Works
            </h2>
            <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Beyond Basic Call Forwarding
            </p>
            <p className="text-[16px] md:text-[18px] text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Your AI Receptionist doesn't just forward calls—it understands them. Using natural language understanding, it identifies who needs what and routes accordingly.
            </p>
          </div>
          
          {/* 3 Steps Process */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 lg:gap-12">
            
            {/* Step 1 */}
            <div className="text-center space-y-4 md:space-y-6">
              <div className="mb-4 md:mb-6">
                <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/call-transfer-write-rule.png"
                    alt="Easily write AI workflow rules in plain English for automation"
                    className="cta-ppc-img-nw object-contain"
                  />
              </div>
              <h3 className="text-[18px] md:text-[20px] lg:text-[24px] font-bold text-gray-900 mb-3 md:mb-4" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Write Rules in Plain English
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                No complex programming. Just describe situations like you'd explain to a new receptionist.
              </p>
            </div>

            {/* Step 2 */}
            <div className="text-center space-y-4 md:space-y-6">
              <div className="mb-4 md:mb-6">
                <img
                      src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/call-transfer-set-transfer.png"
                      alt="Configure AI call workflows by setting transfer destinations for teams"
                      className="cta-ppc-img-nw object-contain"
                    />
              </div>
              <h3 className="text-[18px] md:text-[20px] lg:text-[24px] font-bold text-gray-900 mb-3 md:mb-4" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Set Transfer Destinations
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Assign phone numbers and custom announcements for each scenario. Different situations can route to different team members.
              </p>
            </div>

            {/* Step 3 */}
            <div className="text-center space-y-4 md:space-y-6">
              <div className="mb-4 md:mb-6">
                <img
                      src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/call-transfer-ai-handle-everything.png"
                      alt="AI handles call routing, automation, and workflow management seamlessly"
                      className="cta-ppc-img-nw object-contain"
                    />
              </div>
              <h3 className="text-[18px] md:text-[20px] lg:text-[24px] font-bold text-gray-900 mb-3 md:mb-4" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                AI Handles Everything
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Your AI identifies the situation and transfers seamlessly. Callers hear a professional handoff, not a cold transfer.
              </p>
            </div>

          </div>
        </div>
      </section>

      {/* Smart Transfer Features Section Header */}
      <section className="bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="text-center space-y-6 max-w-4xl mx-auto mb-16">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Smart <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Transfer Features</span>
            </h2>
            <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Professional handoffs every time. Your AI doesn't just transfer calls—it provides context, passes information, and ensures smooth connections.
            </p>
          </div>
        </div>
      </section>

      {/* Warm Transfers Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            {/* Left Column - Content */}
            <div className="space-y-4 md:space-y-6">
              <div className="space-y-3 md:space-y-4">
                <h3 className="text-[20px] md:text-[28px] lg:text-[32px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Warm Transfers
                </h3>
                <p className="text-[16px] md:text-[18px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Your AI introduces the caller before connecting. No cold handoffs—every transfer includes context and preparation.
                </p>
              </div>
              <ul className="space-y-2 md:space-y-3 text-[14px] md:text-[16px] text-gray-700">
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>AI identifies caller needs</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Announces transfer reason</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Provides brief hold music</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Introduces caller to agent</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Stays on line if needed</span>
                </li>
              </ul>
            </div>
            {/* Right Column - Image Placeholder */}
            <div>
              <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/call-transfer-warm-transfer.png"
                    alt="AI introduces callers before transferring, ensuring no cold handoffs and smooth context sharing"
                    className="cta-ppc-img-nw object-contain"
                  />
            </div>
          </div>
        </div>
      </section>

      {/* Information Passing Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            {/* Left Column - Image Placeholder */}
            <div className="order-2 lg:order-1">
              <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/call-transfer-information-passing.png"
                    alt="AI transfers critical caller details so teams see full context before answering"
                    className="cta-ppc-img-nw object-contain"
                  />
            </div>
            {/* Right Column - Content */}
            <div className="order-1 lg:order-2 space-y-4 md:space-y-6">
              <div className="space-y-3 md:space-y-4">
                <h3 className="text-[20px] md:text-[28px] lg:text-[32px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Information Passing
                </h3>
                <p className="text-[16px] md:text-[18px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Critical details transfer with every call. Your team sees complete context before they even pick up.
                </p>
              </div>
              <ul className="space-y-2 md:space-y-3 text-[14px] md:text-[16px] text-gray-700">
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Caller name and contact info</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Detailed issue description</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Urgency level assessment</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Account and service history</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Complete conversation summary</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      {/* Smart Screening Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            {/* Left Column - Content */}
            <div className="space-y-4 md:space-y-6">
              <div className="space-y-3 md:space-y-4">
                <h3 className="text-[20px] md:text-[28px] lg:text-[32px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Smart Screening
                </h3>
                <p className="text-[16px] md:text-[18px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Filter calls intelligently before they reach your team. Only qualified, relevant calls get through to your valuable time.
                </p>
              </div>
              <ul className="space-y-2 md:space-y-3 text-[14px] md:text-[16px] text-gray-700">
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Verify existing customers</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Check service area coverage</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Confirm emergency criteria</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Gather required information</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Block spam and solicitors</span>
                </li>
              </ul>
            </div>
            {/* Right Column - Image Placeholder */}
            <div>
              <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/call-transfer-smart-screening.png"
                    alt="AI filters calls intelligently, allowing only qualified and relevant calls to reach your team"
                    className="cta-ppc-img-nw object-contain"
                  />
            </div>
          </div>
        </div>
      </section>

      {/* Pre-Built Routing Scenarios Tab Section */}
      <RoutingTab />

      {/* FAQ Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="text-center space-y-6 max-w-4xl mx-auto mb-12">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Frequently Asked Questions
            </h2>
          </div>

          <div className="max-w-4xl mx-auto">
            <Accordion items={faqItems} />
          </div>
        </div>
      </section>

      {/* Bottom CTA Section */}
      <section className="py-16 lg:py-24 bg-gradient-to-r from-purple-600 to-pink-600">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="text-center space-y-8">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-white leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Never Miss Another <span className="text-yellow-300">Important Call</span>
            </h2>
            <p className="text-[16px] md:text-[18px] lg:text-[20px] text-purple-100 max-w-2xl mx-auto" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Set up intelligent call routing that understands your business and connects every caller to exactly the right person.
            </p>
            <div className="flex justify-center">
              <a 
                href="https://calendly.com/jeel-fieldcamp/30min" 
                className="calendly-open inline-flex items-center justify-center bg-white text-purple-600 px-6 py-3 rounded-xl font-medium transition-all duration-300 hover:shadow-lg transform"
                style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}
              >
                Book a Demo
              </a>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}