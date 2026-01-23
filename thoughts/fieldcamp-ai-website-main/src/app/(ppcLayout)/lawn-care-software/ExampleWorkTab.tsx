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
      name: 'Schedule a week',
      title: 'Schedule a week',
      description: 'Drag your recurring customers into Monday through Friday routes. FieldCamp groups them by neighborhood and shows drive times between stops. What used to take 2 hours of Excel juggling now takes 20 minutes of dragging and dropping. See your whole week on one screen, with every crew\'s daily route already optimized.',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lawn-example-1-tab-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lawn-example-1-tab-scaled.webp'
    },
    {
      name: 'Invoice automatically',
      title: 'Invoice automatically',
      description: 'Set up automatic invoicing for your recurring customers. FieldCamp generates and sends invoices based on completed jobs, tracking payments and sending reminders. No more Sunday nights spent creating invoices - the system handles it while you sleep.',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lawn-invoice-tab-img-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lawn-invoice-tab-img-scaled.webp'
    },
    {
      name: 'Track customer details',
      title: 'Track customer details',
      description: 'Keep all customer information in one place - gate codes, dog warnings, payment preferences, service history. Your crew sees what they need on their phones. No more lost sticky notes or forgotten instructions. Every detail travels with the job.',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lawn-example-3-tab-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/09/lawn-example-3-tab-scaled.webp'
    }
  ];

  return (
    <section className='examplework-tab-section bg-white'>
      <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-[15px]'>
        
        {/* Section Header */}
        <div className="text-center mb-12">
          <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2] mb-8">
            What could you do with FieldCamp? See examples
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