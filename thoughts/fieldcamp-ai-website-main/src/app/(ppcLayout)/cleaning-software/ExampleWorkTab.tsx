'use client';

import React, { useState } from 'react';

interface ExampleTab {
  name: string;
  title: string;
  description: string;
  desktopImage: string;
  mobileImage: string;
}

export default function ExampleWorkTab() {
  const [activeTab, setActiveTab] = useState(0);

  const tabs: ExampleTab[] = [
    {
      name: 'Handle morning chaos',
      title: 'Handle morning chaos',
      description: 'Three cleaners just called in sick. Drag their 15 jobs to other teams. FieldCamp shows who has capacity and who is already nearby. What used to be 45 minutes of frantic phone calls becomes 5 minutes of dragging jobs to teams that make sense. Clients get notified automatically.',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/cleaning-soft-example-tab-1-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/cleaning-soft-example-tab-1-scaled.webp'
    },
    {
      name: 'Book any service type',
      title: 'Book any service type',
      description: 'Set up automatic invoicing for your recurring customers. FieldCamp generates and sends invoices based on completed jobs, tracking payments and sending reminders. No more Sunday nights spent creating invoices - the system handles it while you sleep.',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/cleaning-soft-example-tab-2-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/cleaning-soft-example-tab-2-scaled.webp'
    },
    {
      name: 'Track what matters',
      title: 'Track what matters',
      description: 'Keep all customer information in one place - gate codes, dog warnings, payment preferences, service history. Your crew sees what they need on their phones. No more lost sticky notes or forgotten instructions. Every detail travels with the job.',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/cleaning-soft-example-tab-3-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/cleaning-soft-example-tab-3-scaled.webp'
    }
  ];

  return (
    <section className='examplework-tab-section bg-white'>
      <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-[15px]'>
        
        {/* Section Header */}
        <div className="text-center mb-12">
          <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2] mb-8">
          What cleaning companies do with FieldCamp? See examples
          </h2>
          
          {/* Tab Navigation */}
          <div className="flex flex-wrap justify-center gap-3 mb-8">
            {tabs.map((tab, index) => (
              <button
                key={index}
                onClick={() => setActiveTab(index)}
                className={`px-5 py-2.5 rounded-full text-sm font-medium transition-all ${
                  activeTab === index
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-100 hover:bg-gray-200'
                }`}
              >
                {tab.name}
              </button>
            ))}
          </div>
        </div>

        {/* Tab Content */}
        <div className="max-w-[1000px] mx-auto">
          {/* Description Text */}
          <p className="text-[18px] md:text-[18px] leading-relaxed text-center mb-10 max-w-[900px] mx-auto">
            {tabs[activeTab].description}
          </p>

          {/* Schedule Interface Display */}
          <div className="relative bg-gray-50 rounded-2xl overflow-hidden shadow-xl">
            <div className="relative">
              {/* Desktop Image */}
              <img
                src={tabs[activeTab].desktopImage}
                alt={`${tabs[activeTab].title} Interface`}
                className="hidden md:block w-full h-auto object-contain"
              />
              {/* Mobile Image */}
              <img
                src={tabs[activeTab].mobileImage}
                alt={`${tabs[activeTab].title} Interface Mobile`}
                className="block md:hidden w-full h-auto object-contain"
              />
            </div>
          </div>
        </div>

      </div>
    </section>
  );
}