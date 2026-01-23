'use client';

import React, { useState } from 'react';

interface Tab {
  name: string;
  desktopImage: string;
  mobileImage: string;
  description: string;
  metric: string;
}

export default function WorkflowTab() {
  const [activeTab, setActiveTab] = useState(0);

  const tabs: Tab[] = [
    {
      name: 'Emergency Router',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/emergency-router-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/emergency-router-mb.png',
      description: 'Instantly routes emergency calls to the right team member. Your AI detects urgency, alerts technicians, and provides immediate assistance instructions.',
      metric: '2x faster emergency response time'
    },
    {
      name: 'Quote Calculator',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/quote-calculator-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/quote-calculator-mb.png',
      description: 'Automatically calculates service quotes based on your pricing rules. Handles variables like location, service type, and customer history.',
      metric: 'Generate quotes in under 45 seconds'
    },
    {
      name: 'Appointment Scheduler',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/appointment-schedular-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/appointment-scheduler-mb.png',
      description: 'Books appointments directly into your calendar while checking technician availability and travel time. Sends automatic confirmations.',
      metric: 'Book 73% more appointments automatically'
    },
    {
      name: 'Lead Qualifier',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lead-qualifier-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lead-qualifier-mb.png',
      description: 'Asks the right questions to qualify leads before booking. Collects essential information and prioritizes high-value opportunities.',
      metric: '94% improvement in lead quality'
    },
    {
      name: 'Payment Collector',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/payment-collector-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/paymenet-collector-mb.png',
      description: 'Processes payments over the phone and handles payment plan discussions. Integrates with your payment systems for secure transactions.',
      metric: '4.2x faster payment collection'
    }
  ];  

  return (
    <section className="py-8 lg:py-16 bg-white">
      <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
        
        {/* Big Rectangle Container */}
        <div className="">
          
          {/* Center-aligned Header */}
          <div className="text-center mb-12">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2] mb-4 md:mb-6" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Essential <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">AI Workflows</span>
            </h2>
            <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed max-w-2xl mx-auto" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Pre-built workflows that handle your most common scenarios. Click each tab to see how they work.
            </p>
          </div>

          {/* Center-aligned Tab Navigation */}
          <div className="flex flex-wrap justify-center gap-2 mb-8">
            {tabs.map((tab, index) => (
              <button
                key={index}
                onClick={() => setActiveTab(index)}
                className={`px-4 md:px-6 py-2 md:py-3 rounded-full text-sm md:text-base font-medium transition-all min-w-[120px] text-center ${
                  activeTab === index
                    ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300'
                }`}
                style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}
              >
                {tab.name}
              </button>
            ))}
          </div>

          {/* Center-aligned Image */}
          <div className="text-center mb-6">
            <div className="inline-block bg-white rounded-2xl overflow-hidden shadow-xl max-w-4xl border border-gray-200">
              {/* Desktop Image */}
              <img
                src={tabs[activeTab].desktopImage}
                alt={`${tabs[activeTab].name} Workflow Interface`}
                className="hidden md:block w-full h-auto object-cover"
              />
              {/* Mobile Image */}
              <img
                src={tabs[activeTab].mobileImage}
                alt={`${tabs[activeTab].name} Workflow Interface`}
                className="block md:hidden w-full h-auto object-cover"
              />
            </div>
          </div>

          {/* Tab Description */}
          <div className="text-center mb-8">
            <p className="text-[16px] md:text-[18px] text-gray-700 max-w-[600px] mx-auto mb-4" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              {tabs[activeTab].description}
            </p>
            <div className="inline-flex items-center justify-center bg-gradient-to-r from-purple-50 to-pink-50 px-4 py-2 rounded-full border border-purple-200">
              <span className="text-sm md:text-base font-semibold text-purple-800" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
                {tabs[activeTab].metric}
              </span>
            </div>
          </div>

          {/* Static Book Demo Button */}
          <div className="text-center">
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
  );
}