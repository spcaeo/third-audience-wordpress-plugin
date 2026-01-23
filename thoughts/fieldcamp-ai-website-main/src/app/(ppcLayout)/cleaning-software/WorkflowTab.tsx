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
      name: 'Quoting',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/clening-workdlow-tab-1-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/clening-workdlow-tab-1-scaled.webp',
      description: 'Price by room count, square footage, or hourly. Generate professional quotes in under 30 seconds with automated calculations.',
      metric: 'Users quote 3.2x faster than Excel'
    },
    {
      name: 'CRM',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lawnppc-crm-2-dk-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lawnppc-crm-2-dk-scaled.webp',
      description: 'Complete customer profiles with service history, preferences, and automated follow-ups. Never lose track of client details again.',
      metric: '94% customer retention improvement'
    },
    {
      name: 'Scheduling',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/clening-workdlow-tab-3-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/clening-workdlow-tab-3-scaled.webp',
      description: 'Drag-drop recurring services with automatic route optimization. Handle last-minute changes without the chaos.',
      metric: 'Schedule 50+ jobs in 5 minutes'
    },
    {
      name: 'Team Tracking',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/clening-workdlow-tab-4-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/clening-workdlow-tab-4-scaled.webp',
      description: 'Live GPS tracking with automatic customer notifications. Your clients know exactly when you\'ll arrive.',
      metric: '73% reduction in "Where are you?" calls'
    },
    {
      name: 'Reviews',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/clening-workdlow-tab-5-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/clening-workdlow-tab-5-scaled.webp',
      description: 'Auto-request 5-star reviews immediately after job completion. Build your reputation while you sleep.',
      metric: '4.8x more reviews collected automatically'
    }
  ];

  return (
    <section className='workflow-section py-160 bg-white'>
      <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-[15px]'>
        
        {/* Big Rectangle Container */}
        <div className="md:p-12 lg:p-16">
          
          {/* Center-aligned Header */}
          <div className="text-center mb-12">
            <h2 className="text-[26px] md:text-[30px] lg:text-[32px] font-bold leading-[1.3] mb-6">
              Every Tool Your Cleaning Company Needs
            </h2>
            <p className="text-[18px] md:text-[18px] leading-relaxed max-w-[600px] mx-auto text-gray-600">
              Stop juggling between multiple apps. FieldCamp combines everything your cleaning business needs in one unified platform.
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
              href="https://calendly.com/jeel-fieldcamp/30min" style={{ height: "52px" }}
              className="calendly-open inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity"
            >
              Book a Demo
            </a>
          </div>

        </div>
        
      </div>
    </section>
  );
}