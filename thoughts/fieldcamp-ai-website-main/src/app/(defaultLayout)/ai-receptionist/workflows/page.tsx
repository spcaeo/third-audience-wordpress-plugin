import React from 'react';
import { Metadata } from 'next';
import Script from 'next/script';
import Accordion from '@/app/_components/Accordion';
import { CalendlyEmbed } from '@/app/_components/General/Custom';
import WorkflowTab from './WorkflowTab';
import AIReceptionistWorkflowsVideo from './AIReceptionistWorkflowsVideo';


export const metadata: Metadata = {
  title: 'Workflows for AI Receptionist | FieldCamp',
  description: 'Program your AI Receptionist with intelligent workflows. No coding required—just plain English instructions. Create unlimited workflows that tell your AI exactly what to do in any situation.',
  robots: 'index, follow',
  alternates: {
    canonical: 'https://fieldcamp.ai/ai-receptionist/workflows'
  }
};

const pageTitle = metadata.title?.toString() || 'Workflows for AI Receptionist | FieldCamp';
const pageDescription = metadata.description || '';
const pageUrl = metadata.alternates?.canonical?.toString() || 'https://fieldcamp.ai/ai-receptionist/workflows';

const faqItems = [
  {
    title: "How many workflows can I create?",
    content: [
      "Unlimited. Create as many workflows as your business needs. Most businesses use 5-10 core workflows and add specialized ones over time."
    ]
  },
  {
    title: "Can workflows work together?",
    content: [
      "Yes, workflows can trigger other workflows. For example, your Lead Qualifier workflow can trigger the Appointment Scheduler workflow for qualified leads."
    ]
  },
  {
    title: "What happens if a workflow fails?",
    content: [
      "Every workflow has fallback options. If something unexpected happens, the AI can transfer to a human, take a message, or follow your specified backup plan."
    ]
  },
  {
    title: "Can I test workflows before going live?",
    content: [
      "Yes, use our simulation mode to test workflows with sample calls. See exactly how your AI will respond before any real customer interactions."
    ]
  },
  {
    title: "How do I know which workflow was used?",
    content: [
      "Call logs show which workflows triggered, what path was taken, and the outcome. You can see exactly how your AI handled each situation."
    ]
  },
  {
    title: "Can workflows access customer history?",
    content: [
      "Yes, workflows can check previous interactions, past services, and customer preferences to provide personalized responses."
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
      "name": "FieldCamp AI Workflows",
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
        "name": "Workflows",
        "item": "https://fieldcamp.ai/ai-receptionist/workflows"
      }
    ]
  }
];

export default function Workflows() {
  return (
    <div className="workflows-page">
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
                WORKFLOWS
              </div>
              <h1 className="text-[32px] md:text-[42px] lg:text-[52px] font-bold text-gray-900 leading-[1.15]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Workflows That Think</span> Like You Do
              </h1>
              <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Program your AI Receptionist with intelligent workflows. No coding required—just plain English instructions.
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

      {/* Workflow Builder Demo Section */}
      <section className="py-[30px] md:py-[50px] lg:py-[60px] bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="relative">
            <div className="rounded-3xl shadow-2xl p-8 lg:p-12" style={{ backgroundColor: 'oklch(91.97% .043758 2.0288)' }}>
              <div className="relative aspect-video bg-white rounded-2xl overflow-hidden">
                {/* Image placeholder */}
                <AIReceptionistWorkflowsVideo />
              </div>
              
              {/* Section title and description */}
              <div className="mt-6 text-center">
                <h3 className="text-xl font-semibold text-black mb-2" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Build Intelligent Workflows Visually
                </h3>
                <p className="text-black" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Create unlimited workflows that tell your AI exactly what to do in any situation. Like training your best employee, but they never forget.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* What Are Workflows Section Header */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="text-center space-y-6 max-w-4xl mx-auto">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              What Are <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Workflows?</span>
            </h2>
            <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Workflows are the skills that make your AI Receptionist intelligent. Each workflow is a set of instructions that guides your AI through specific situations.
            </p>
          </div>
        </div>
      </section>

      {/* Skills Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            {/* Left Column - Content */}
            <div className="space-y-4 md:space-y-6">
              <div className="space-y-3 md:space-y-4">
                <h3 className="text-[20px] md:text-[28px] lg:text-[32px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Think of Workflows as Skills
                </h3>
                <p className="text-[16px] md:text-[18px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Your AI Receptionist can have unlimited skills to handle any situation that comes up.
                </p>
              </div>
              <ul className="space-y-2 md:space-y-3 text-[14px] md:text-[16px] text-gray-700">
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Book appointments</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Calculate quotes</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Handle emergencies</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Qualify leads</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Process payments</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Answer FAQs</span>
                </li>
              </ul>
            </div>
            {/* Right Column - Image Placeholder */}
            <div>
              <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/think-of-workflow.png"
                  alt="AI workflows designed as skills to automate tasks with intelligence"
                  className="cta-ppc-img-nw object-contain"
                />
            </div>
          </div>
        </div>
      </section>

      {/* How Workflows Work Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            {/* Left Column - Image Placeholder */}
            <div className="order-2 lg:order-1">
              <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/the-building-blocks.png"
                  alt="AI building blocks for creating intelligent workflows and automation"
                  className="cta-ppc-img-nw object-contain"
                />
            </div>
            {/* Right Column - Content */}
            <div className="order-1 lg:order-2 space-y-4 md:space-y-6">
              <div className="space-y-3 md:space-y-4">
                <h3 className="text-[20px] md:text-[28px] lg:text-[32px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  The Building Blocks of Intelligence
                </h3>
                <p className="text-[16px] md:text-[18px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Every workflow follows a simple pattern: scenarios trigger workflows, nodes execute actions, and transitions connect the flow.
                </p>
              </div>
              <ul className="space-y-2 md:space-y-3 text-[14px] md:text-[16px] text-gray-700">
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Scenarios trigger workflows</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Nodes execute actions</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Transitions connect the flow</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      {/* Pre-Built Workflows Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            {/* Left Column - Content */}
            <div className="space-y-4 md:space-y-6">
              <div className="space-y-3 md:space-y-4">
                <h3 className="text-[20px] md:text-[28px] lg:text-[32px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Ready-to-Use Workflows
                </h3>
                <p className="text-[16px] md:text-[18px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Start immediately with field service workflows that work out of the box. Customize them to match your business exactly.
                </p>
              </div>
              <ul className="space-y-2 md:space-y-3 text-[14px] md:text-[16px] text-gray-700">
                <li className="flex items-center space-x-3">
                  <span className="text-red-500 font-bold">•</span>
                  <span>Emergency Router</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">•</span>
                  <span>Quote Calculator</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-blue-500 font-bold">•</span>
                  <span>Appointment Scheduler</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-purple-500 font-bold">•</span>
                  <span>Lead Qualifier</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-orange-500 font-bold">•</span>
                  <span>Payment Collector</span>
                </li>
              </ul>
            </div>
            {/* Right Column - Image Placeholder */}
            <div>
              <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/ready-to-use-workflow.png"
                  alt="Pre-built AI workflows ready to use for business automation"
                  className="cta-ppc-img-nw object-contain"
                />
            </div>
          </div>
        </div>
      </section>

      {/* Custom Workflows Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            {/* Left Column - Image Placeholder */}
            <div className="order-2 lg:order-1">
              <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/create-your-own.png"
                  alt="Easily create custom AI workflows in minutes with FieldCamp AI"
                  className="cta-ppc-img-nw object-contain"
                />
            </div>
            {/* Right Column - Content */}
            <div className="order-1 lg:order-2 space-y-4 md:space-y-6">
              <div className="space-y-3 md:space-y-4">
                <h3 className="text-[20px] md:text-[28px] lg:text-[32px] font-bold text-gray-900 leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Create Your Own in Minutes
                </h3>
                <p className="text-[16px] md:text-[18px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                  Build workflows specific to your business using our visual builder. No coding required.
                </p>
              </div>
              <ul className="space-y-2 md:space-y-3 text-[14px] md:text-[16px] text-gray-700">
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Visual drag & drop interface</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Plain English instructions</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Test before going live</span>
                </li>
                <li className="flex items-center space-x-3">
                  <span className="text-green-500 font-bold">✓</span>
                  <span>Unlimited customization</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      {/* How Workflows Work Section */}
      <section className="py-8 lg:py-24 bg-white">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          {/* Section Header */}
          <div className="text-center mb-16">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2] mb-4 md:mb-6" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              How <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Workflows Work</span>
            </h2>
          </div>
          
          {/* 3 Steps Grid */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 lg:gap-12">
            
            {/* Step 1 */}
            <div className="text-center space-y-4 md:space-y-6">
              <div className="mb-4 md:mb-6">
                {/* Image Placeholder */}
                <div className='workflow-list'>
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/scenarios-trigger.png"
                  alt="Trigger workflows using scenarios for smarter automation"
                  className="cta-ppc-img-nw object-contain"
                />
                </div>
              </div>
              <h3 className="text-[18px] md:text-[20px] lg:text-[24px] font-bold text-gray-900 mb-3 md:mb-4" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Scenarios Trigger Workflows
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Every workflow starts with a trigger scenario—a specific situation that tells your AI "this is when you should use this skill."
              </p>
            </div>

            {/* Step 2 */}
            <div className="text-center space-y-4 md:space-y-6">
              <div className="mb-4 md:mb-6">
                {/* Image Placeholder */}
                <div className='workflow-list'>
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/nodes-execute.png"
                  alt="Workflow nodes executing automated AI actions and tasks"
                  className="cta-ppc-img-nw object-contain"
                />
                </div>
              </div>
              <h3 className="text-[18px] md:text-[20px] lg:text-[24px] font-bold text-gray-900 mb-3 md:mb-4" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Nodes Execute Actions
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Inside each workflow are nodes—individual steps your AI follows. Connect nodes like building blocks to create any process.
              </p>
            </div>

            {/* Step 3 */}
            <div className="text-center space-y-4 md:space-y-6">
              <div className="mb-4 md:mb-6">
                {/* Image Placeholder */}
                <div className='workflow-list'>
                <img
                  src="https://cms.fieldcamp.ai/wp-content/uploads/2025/09/transotion-connect.png"
                  alt="AI workflow transitions connecting nodes for seamless automation"
                  className="cta-ppc-img-nw object-contain"
                />
                </div>
              </div>
              <h3 className="text-[18px] md:text-[20px] lg:text-[24px] font-bold text-gray-900 mb-3 md:mb-4" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Transitions Connect the Flow
              </h3>
              <p className="text-[14px] md:text-[16px] text-gray-600 leading-relaxed" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                Nodes connect through transitions that determine the path. Your AI follows these paths based on customer responses.
              </p>
            </div>

          </div>
        </div>
      </section>

      {/* Workflow Tab Section */}
      <WorkflowTab />

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
      <section className="py-8 lg:py-24 bg-gradient-to-r from-purple-600 to-pink-600">
        <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
          <div className="text-center space-y-8">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-white leading-[1.2]" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Start Building Intelligent <span className="text-yellow-300">Workflows Today</span>
            </h2>
            <p className="text-[16px] md:text-[18px] lg:text-[20px] text-purple-100 max-w-2xl mx-auto" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Transform your AI Receptionist from an answering service into a skilled team member.
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