import React from 'react';
import { Metadata } from 'next';
import Script from 'next/script';
import Accordion from '@/app/_components/Accordion';
import { CalendlyEmbed } from '@/app/_components/General/Custom';
import RecordingTab from './RecordingTab';

export const metadata: Metadata = {
  title: 'Call Recording & Transcripts for AI Receptionist | FieldCamp',
  description: 'Every conversation recorded, transcribed, and summarized automatically. Get instant access to every call with AI-generated summaries and full transcriptions.',
  robots: 'index, follow',
  alternates: {
    canonical: 'https://fieldcamp.ai/ai-receptionist/call-recording'
  }
};

const pageTitle = metadata.title?.toString() || 'Call Recording & Transcripts for AI Receptionist | FieldCamp';
const pageDescription = metadata.description || '';
const pageUrl = metadata.alternates?.canonical?.toString() || 'https://fieldcamp.ai/ai-receptionist/call-recording';

const faqItems = [
  {
    title: "How long are recordings stored?",
    content: [
      "Call recordings are stored for 90 days by default. You can extend storage, download recordings for permanent storage, or set custom retention periods based on your needs."
    ]
  },
  {
    title: "How accurate is the transcription?",
    content: [
      "Our transcription achieves 95%+ accuracy for clear audio. Technical terms specific to your industry are learned over time for even better accuracy."
    ]
  },
  {
    title: "Can I download recordings and transcripts?",
    content: [
      "Yes, you can download individual recordings as MP3 files and transcripts as text or PDF documents. Bulk export is also available."
    ]
  },
  {
    title: "How quickly are summaries available?",
    content: [
      "Summaries are generated within 30 seconds of call completion. Recording and transcription are available instantly."
    ]
  },
  {
    title: "Do notifications work outside business hours?",
    content: [
      "Yes, you can set different notification rules for business hours vs. after-hours, ensuring urgent calls always reach the right person."
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
      "name": "FieldCamp AI Call Recording",
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
        "name": "Call Recording",
        "item": "https://fieldcamp.ai/ai-receptionist/call-recording"
      }
    ]
  }
];

export default function CallRecording() {
  return (
    <div className="call-recording-page">
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
                CALL RECORDING & TRANSCRIPTS
              </div>
              <h1 className="text-[32px] md:text-[42px] lg:text-[52px] font-bold text-gray-900 leading-[1.15]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Call Recording & Transcripts</span>
              </h1>
              <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed pb-0" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Every conversation recorded, transcribed, and summarized automatically. Never wonder what was discussed again.
              </p>
              <p className="text-[16px] md:text-[18px] text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Get instant access to every call with AI-generated summaries, full transcriptions, and actionable insights.
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

      {/* Call Recording Demo Section */}
      <section className="py-[30px] md:py-[50px] lg:py-[60px] bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="relative">
            <div className="rounded-3xl shadow-2xl p-8 lg:p-12" style={{ backgroundColor: 'oklch(91.97% .043758 2.0288)' }}>
              <div className="relative aspect-video bg-white rounded-2xl overflow-hidden">
                {/* Image placeholder */}
                <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/call-recording-mani-banner-scaled.webp"
                    alt="AI automatically records, transcribes, and summarizes every conversation for instant access"
                    className="cta-ppc-img-nw object-contain"
                  />
              </div>
              
              {/* Section title and description */}
              <div className="mt-6 text-center">
                <h3 className="text-xl font-semibold text-black mb-2" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  See Every Call Detail Captured
                </h3>
                <p className="text-black" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Watch how every conversation is automatically recorded, transcribed, and summarized for instant access and review.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* What You Get Section Header */}
      <section className="bg-white py-8 lg:py-24">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="text-center space-y-6 max-w-4xl mx-auto">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              What You Get With <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Every Call</span>
            </h2>
            <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed pb-0" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Complete Documentation Automatically
            </p>
            <p className="text-[16px] md:text-[18px] text-gray-700 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Your AI Receptionist captures every detail of every conversation. No more "he said, she said" or forgotten commitments.
            </p>
          </div>
        </div>
      </section>

      {/* Crystal Clear Recording Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            {/* Left Column - Content */}
            <div className="space-y-4 md:space-y-6">
              <div className="space-y-3 md:space-y-4">
                <h3 className="text-[20px] md:text-[28px] lg:text-[32px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Crystal Clear Recording
                </h3>
                <p className="text-[16px] md:text-[18px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Every call recorded in high quality with automatic storage and instant access.
                </p>
              </div>
              <ul className="space-y-2 md:space-y-3 text-[14px] md:text-[16px] text-gray-700">
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Automatic start/stop recording</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Secure cloud storage</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Instant playback anytime</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Download recordings as MP3</span>
                </li>
              </ul>
            </div>
            {/* Right Column - Image Placeholder */}
            <div>
              <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/crystal-clear-recording.webp"
                    alt="AI records every call in high quality with automatic cloud storage and instant playback access."
                    className="cta-ppc-img-nw object-contain"
                  />
            </div>
          </div>
        </div>
      </section>

      {/* Accurate Transcription Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            {/* Left Column - Image Placeholder */}
            <div className="order-2 lg:order-1">
                <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/accurate-transcription.png"
                    alt="AI converts every spoken word into searchable text with industry-leading transcription accuracy."
                    className="cta-ppc-img-nw object-contain"
                  />
            </div>
            {/* Right Column - Content */}
            <div className="order-1 lg:order-2 space-y-4 md:space-y-6">
              <div className="space-y-3 md:space-y-4">
                <h3 className="text-[20px] md:text-[28px] lg:text-[32px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Accurate Transcription
                </h3>
                <p className="text-[16px] md:text-[18px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Every word converted to searchable text with industry-leading accuracy.
                </p>
              </div>
              <ul className="space-y-2 md:space-y-3 text-[14px] md:text-[16px] text-gray-700">
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>95%+ transcription accuracy</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Speaker identification</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Timestamp markers</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Search any keyword instantly</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      {/* Smart Summaries Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            {/* Left Column - Content */}
            <div className="space-y-4 md:space-y-6">
              <div className="space-y-3 md:space-y-4">
                <h3 className="text-[20px] md:text-[28px] lg:text-[32px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Smart Summaries
                </h3>
                <p className="text-[16px] md:text-[18px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  AI extracts what matters most from every conversation automatically.
                </p>
              </div>
              <ul className="space-y-2 md:space-y-3 text-[14px] md:text-[16px] text-gray-700">
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Main request and issue identified</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Actions taken documented</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Follow-ups needed highlighted</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Customer sentiment analyzed</span>
                </li>
              </ul>
            </div>
            {/* Right Column - Image Placeholder */}
            <div>
                <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/smart-summaries.webp"
                    alt="AI extracts key insights and important details from every conversation automatically."
                    className="cta-ppc-img-nw object-contain"
                  />
            </div>
          </div>
        </div>
      </section>

      {/* How It Works Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          {/* Section Header */}
          <div className="text-center mb-16">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2] mb-4 md:mb-6" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              How <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">It Works</span>
            </h2>
            <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Automatic From Start to Finish
            </p>
          </div>
          
          {/* 3 Steps Grid */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 lg:gap-12">
            
            {/* Step 1 */}
            <div className="text-center space-y-4 md:space-y-6">
              <div className="mb-4 md:mb-6">
                {/* Image Placeholder */}
                <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/call-connect.webp"
                    alt="Call recording starts automatically when AI answers, no manual activation required."
                    className="cta-ppc-img-nw object-contain"
                  />
              </div>
              <h3 className="text-[18px] md:text-[20px] lg:text-[24px] font-bold text-gray-900 mb-3 md:mb-4" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Call Connects
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Recording starts the moment your AI answers. Nothing to activate, nothing to remember.
              </p>
            </div>

            {/* Step 2 */}
            <div className="text-center space-y-4 md:space-y-6">
              <div className="mb-4 md:mb-6">
                {/* Image Placeholder */}
                <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/real-time-processing.webp"
                    alt="AI performs live voice-to-text transcription and sentiment analysis during every call."
                    className="cta-ppc-img-nw object-contain"
                  />
              </div>
              <h3 className="text-[18px] md:text-[20px] lg:text-[24px] font-bold text-gray-900 mb-3 md:mb-4" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Real-Time Processing
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                While you talk, we transcribe and analyze. Voice-to-text conversion and sentiment analysis happen live.
              </p>
            </div>

            {/* Step 3 */}
            <div className="text-center space-y-4 md:space-y-6">
              <div className="mb-4 md:mb-6">
                {/* Image Placeholder */}
                <img
                    src="https://cms.fieldcamp.ai/wp-content/uploads/2025/10/instant-access.webp"
                    alt="Instant access to call recordings, transcripts, and AI summaries right after each call ends."
                    className="cta-ppc-img-nw object-contain"
                  />
              </div>
              <h3 className="text-[18px] md:text-[20px] lg:text-[24px] font-bold text-gray-900 mb-3 md:mb-4" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Instant Access
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Everything ready the second the call ends. Play recording, read transcript, review summary instantly.
              </p>
            </div>

          </div>
        </div>
      </section>

      {/* Recording Tab Section */}
      <RecordingTab />

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
              Never Miss Important <span className="text-yellow-300">Call Details</span> Again
            </h2>
            <p className="text-[16px] md:text-[18px] lg:text-[20px] text-purple-100 max-w-2xl mx-auto" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Every conversation documented, summarized, and searchable. Start building your call intelligence today.
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