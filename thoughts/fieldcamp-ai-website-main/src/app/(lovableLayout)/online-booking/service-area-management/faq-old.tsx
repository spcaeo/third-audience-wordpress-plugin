// In faq.tsx
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

export const FAQ: React.FC<{
  tabs: { id: FAQCategory; label: string }[];
  faqs: Record<FAQCategory, FAQItem[]>;
}> = ({ tabs, faqs }) => {
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
};