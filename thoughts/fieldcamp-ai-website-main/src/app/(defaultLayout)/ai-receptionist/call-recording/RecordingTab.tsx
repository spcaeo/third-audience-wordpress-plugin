'use client';

import React, { useState } from 'react';

interface Tab {
  name: string;
  desktopImage: string;
  mobileImage: string;
  description: string;
  metric: string;
}

export default function RecordingTab() {
  const [activeTab, setActiveTab] = useState(0);

  const tabs: Tab[] = [
    {
      name: 'Email Summaries',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/email-summaries-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/email-summaries-mb.png',
      description: 'Receive detailed summaries in your inbox with complete transcripts and call recordings attached.',
      metric: 'Daily digests or instant notifications'
    },
    {
      name: 'SMS Alerts',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/sms-alert-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/sms-alert-mb.png',
      description: 'Get instant text message alerts for urgent calls, emergencies, or specific keywords mentioned during calls.',
      metric: 'Respond 3x faster to urgent calls'
    },
    {
      name: 'Team Notifications',
      desktopImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/team-notification-scaled.webp',
      mobileImage: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/10/team-notification-mb.png',
      description: 'Keep your team informed with real-time notifications through Slack, Microsoft Teams, or email when important calls happen.',
      metric: 'Entire team stays synchronized'
    }
  ];

  return (
    <section className="py-8 lg:py-8 bg-white">
      <div className="container mx-auto max-w-[1245px] px-[15px] lg:px-0">
        
        {/* Big Rectangle Container */}
        <div className="p-8 md:p-12 lg:p-16">
          
          {/* Center-aligned Header */}
          <div className="text-center mb-12">
            <h2 className="text-[24px] md:text-[36px] lg:text-[42px] font-bold text-gray-900 leading-[1.2] mb-4 md:mb-6" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Instant <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Notifications</span>
            </h2>
            <p className="text-[16px] md:text-[18px] lg:text-[20px] text-gray-600 leading-relaxed max-w-2xl mx-auto" style={{ fontFamily: 'SF Pro Text, -apple-system, BlinkMacSystemFont, sans-serif' }}>
              Stay Informed Without Listening to Every Call
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