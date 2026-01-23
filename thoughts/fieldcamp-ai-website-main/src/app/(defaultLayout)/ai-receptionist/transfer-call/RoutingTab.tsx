'use client';

import React, { useState } from 'react';

interface Tab {
  name: string;
  desktopImage: string;
  mobileImage: string;
  description: string;
  metric: string;
}

export default function RoutingTab() {
  const [activeTab, setActiveTab] = useState(0);

  const tabs: Tab[] = [
    {
      name: 'Emergency Dispatch',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-call-emergency-dispatch-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-call-emergency-dispatch-mb.png',
      description: 'Instantly routes emergency calls to your on-call technician with automatic alerts and location sharing.',
      metric: '2x faster emergency response time'
    },
    {
      name: 'Sales & Quotes',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-call-sales-Quotes-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-call-sales-quotes-mb.png',
      description: 'Routes pricing inquiries to your sales team while gathering basic project details before transfer.',
      metric: '73% more qualified leads to sales'
    },
    {
      name: 'Service Scheduling',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-callservice-scheduling-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-call-service-scheduling-mb.png',
      description: 'Connects appointment requests to dispatch while checking technician availability and service requirements.',
      metric: 'Book 67% more appointments automatically'
    },
    {
      name: 'Existing Customers',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-call-existing-customers-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-call-existing-customers-mb.png',
      description: 'Recognizes returning customers and routes to their assigned account manager or service history team.',
      metric: '94% customer satisfaction improvement'
    },
    {
      name: 'Billing & Payments',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-call-billing-payments-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-call-billing-payments-mb.png',
      description: 'Routes billing inquiries to accounting while offering direct payment processing options.',
      metric: '4.2x faster payment collection'
    },
    {
      name: 'Escalations',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-call-escalations-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/transform-call-escalations-mb.png',
      description: 'Detects upset customers and immediately connects to management with priority queue jumping.',
      metric: '89% complaint resolution rate'
    }
  ];

  return (
    <section className="py-12 lg:py-16 bg-white">
      <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
        
        {/* Big Rectangle Container */}
        <div className="">
          
          {/* Center-aligned Header */}
          <div className="text-center mb-12">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2] mb-4 md:mb-6" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Pre-Built <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Routing Scenarios</span>
            </h2>
            <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed max-w-2xl mx-auto" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Common Field Service Routes
            </p>
            <p className="text-[16px] md:text-[18px] text-gray-700 leading-relaxed max-w-2xl mx-auto mt-2" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Start with proven routing patterns used by successful field service businesses.
            </p>
          </div>

          {/* Center-aligned Tab Navigation */}
          <div className="flex flex-wrap justify-center gap-2 mb-8">
            {tabs.map((tab, index) => (
              <button
                key={index}
                onClick={() => setActiveTab(index)}
                className={`px-6 py-2 rounded-full text-sm font-medium transition-all min-w-[120px] text-center ${
                  activeTab === index
                    ? 'bg-black text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-black'
                }`}
              >
                {tab.name}
              </button>
            ))}
          </div>

          {/* Center-aligned Image */}
          <div className="text-center mb-6">
            <div className="inline-block bg-white rounded-2xl overflow-hidden shadow-lg max-w-4xl">
              {/* Desktop Image */}
              <img
                src={tabs[activeTab].desktopImage}
                alt={`${tabs[activeTab].name} Interface`}
                className="hidden md:block w-full h-auto object-cover"
              />
              {/* Mobile Image */}
              <img
                src={tabs[activeTab].mobileImage}
                alt={`${tabs[activeTab].name} Interface Mobile`}
                className="block md:hidden w-full h-auto object-cover"
              />
            </div>
          </div>

          {/* Tab Description */}
          <div className="text-center mb-8">
            <p className="text-[16px] md:text-[18px] text-gray-700 max-w-[500px] mx-auto">
              {tabs[activeTab].description}
            </p>
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