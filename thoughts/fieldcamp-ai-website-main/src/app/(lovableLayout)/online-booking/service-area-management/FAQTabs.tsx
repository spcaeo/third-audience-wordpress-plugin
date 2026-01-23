'use client';

import { useState } from "react";
import { Users, Settings, Zap, TrendingUp, ChevronDown } from "lucide-react";

type FAQItem = {
  question: string;
  answer: string;
};

type FAQCategory = 'customers' | 'setup' | 'smartFeatures' | 'results';

const iconMap = {
  'customers': Users,
  'setup': Settings,
  'smartFeatures': Zap,
  'results': TrendingUp
};

const tabs = [
  { id: 'customers' as const, label: 'Getting Started' },
  { id: 'setup' as const, label: 'Setup' },
  { id: 'smartFeatures' as const, label: 'Smart Features' },
  { id: 'results' as const, label: 'Results' }
];

const faqs: Record<FAQCategory, FAQItem[]> = {
  customers: [
    {
      question: "What is service area management in FieldCamp?",
      answer: "Service area management defines the geographic boundaries where your business provides services. The system checks customer addresses against these boundaries before allowing bookings."
    },
    {
      question: "Can I customize my service area boundaries?",
      answer: "Yes. You can set up clear zones to match your coverage area and adjust them anytime as your business expands or contracts."
    },
    {
      question: "Can I set different service zones for different services?",
      answer: "Yes. You can create separate zones for different services. For example, a larger zone for general maintenance and a smaller one for specialized jobs."
    }
  ],
  setup: [
    {
      question: "How do I configure service areas in FieldCamp?",
      answer: " During setup, you define your service boundaries in the system. Customers are required to enter their address, which is validated against these boundaries before booking."
    },
    {
      question: "How often should I review service area boundaries?",
      answer: "It's recommended to review them regularly based on technician availability, travel time, operational costs, and demand patterns."
    }
  ],
  smartFeatures: [
    {
      question: "How does address validation work?",
      answer: "When customers enter their address in the booking widget or chat, FieldCamp verifies if it's within your service zone. Inside zone: They see \"Good news! We service your area.\" Outside zone: The system blocks the booking and redirects them."
    },
    {
      question: "Do service area rules apply across all booking methods?",
      answer: "Yes. Whether customers book through the widget, live chat, or customer portal, the same service area validation applies."
    }
  ],
  results: [
    {
      question: "Why is service area validation important?",
      answer: "Service area validation helps your business avoid unprofitable or distant jobs, optimize technician travel routes, prevent scheduling conflicts, and deliver faster, more reliable service to customers."
    },
    {
      question: "How does service area management affect customer experience?",
      answer: "Customers get instant clarity if you serve their location. This reduces booking frustration and builds trust in your business."
    }
  ]
};

export const faqItems = Object.values(faqs).flat();

export default function FAQTabs() {
  const [openIndex, setOpenIndex] = useState<number | null>(null);
  const [activeTab, setActiveTab] = useState<FAQCategory>('customers');

  const toggleFAQ = (index: number) => {
    setOpenIndex(openIndex === index ? null : index);
  };

  return (
    <div className="max-w-4xl mx-auto bg-white">
      <div className="text-center mb-8 md:mb-16">
        <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">Frequently Asked Questions</h2>
        <p className="mt-3 md:mt-6 text-base md:text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">Everything you need to know about optimizing your service coverage and profitability</p>
      </div>

      <div className="h-10 items-center justify-center rounded-md bg-[#f1f5f9] p-1 text-[#64748b] grid w-full grid-cols-4 mb-8">
        {tabs.map((tab) => {
          const Icon = iconMap[tab.id];
          return (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`flex items-center md:gap-2 gap-1 transition-all justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ${
                activeTab === tab.id
                  ? 'bg-white text-[#000] shadow-sm'
                  : 'text-[#64748b] hover:text-gray-700'
              }`}
            >
              <Icon size={16} className="min-w-4 min-h-4" />
              <span className="md:block hidden">{tab.label}</span>
            </button>
          );
        })}
      </div>

      <div className="space-y-3">
        {faqs[activeTab]?.map((faq, index) => (
          <div
            key={index}
            className="border border-gray-200 rounded-lg bg-white hover:shadow-sm transition-shadow"
          >
            <button
              className="flex w-full items-center justify-between p-4 text-left"
              onClick={() => toggleFAQ(index)}
            >
              <span className="font-medium text-gray-900">{faq.question}</span>
              <ChevronDown
                className={`h-5 w-5 text-gray-500 transition-transform ${
                  openIndex === index ? 'transform rotate-180' : ''
                }`}
              />
            </button>
            {openIndex === index && (
              <div className="p-4 pt-0 text-gray-600">
                <p>{faq.answer}</p>
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}