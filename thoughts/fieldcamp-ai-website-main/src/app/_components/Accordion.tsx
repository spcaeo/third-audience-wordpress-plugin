"use client";
import React, { useState } from "react";

interface AccordionItem {
  title: string;
  content: string | string[];
}

interface AccordionProps {
  items: AccordionItem[];
}

const Accordion: React.FC<AccordionProps> = ({ items }) => {
  const [activeIndex, setActiveIndex] = useState<number | null>(null);

  const toggleAccordion = (index: number) => {
    setActiveIndex(index === activeIndex ? null : index);
  };

  return (
    <div className="space-y-4">
      {items.map((item, index) => (
        <div
          key={index}
          className="border border-neutral-200 rounded-lg bg-white faq-accordion-main"
        >
          <button
            className="w-full px-6 py-4 text-left flex justify-between items-center faq-accordion-title"
            onClick={() => toggleAccordion(index)}
          >
            <span className="font-medium faq-accordion-title">{item.title}</span>
            <span className="transform transition-transform duration-200 faq-icons">
              {activeIndex === index ? (
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16"
                  height="16"
                  viewBox="0 0 24 24"
                  fill="none"
                >
                  <rect x="4" y="11" width="16" height="2" fill="#232529" />
                </svg>
              ) : (
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="15"
                  height="16"
                  viewBox="0 0 22 23"
                  fill="none"
                >
                  <path
                    d="M21.9375 11.627C21.9375 11.975 21.7992 12.3089 21.5531 12.555C21.3069 12.8012 20.9731 12.9395 20.625 12.9395H12.3125V21.252C12.3125 21.6 12.1742 21.9339 11.9281 22.18C11.6819 22.4262 11.3481 22.5645 11 22.5645C10.6519 22.5645 10.3181 22.4262 10.0719 22.18C9.82578 21.9339 9.6875 21.6 9.6875 21.252V12.9395H1.375C1.0269 12.9395 0.693064 12.8012 0.446922 12.555C0.200781 12.3089 0.0625 11.975 0.0625 11.627C0.0625 11.2789 0.200781 10.945 0.446922 10.6989C0.693064 10.4527 1.0269 10.3145 1.375 10.3145H9.6875V2.00195C9.6875 1.65386 9.82578 1.32002 10.0719 1.07388C10.3181 0.827734 10.6519 0.689453 11 0.689453C11.3481 0.689453 11.6819 0.827734 11.9281 1.07388C12.1742 1.32002 12.3125 1.65386 12.3125 2.00195V10.3145H20.625C20.9731 10.3145 21.3069 10.4527 21.5531 10.6989C21.7992 10.945 21.9375 11.2789 21.9375 11.627Z"
                    fill="#232529"
                  />
                </svg>
              )}
            </span>
          </button>
          {activeIndex === index && (
            <div className="px-6 py-4 border-t border-neutral-200 faq-accordion-content">
              {Array.isArray(item.content) ? (
                <div className="space-y-2">
                  {item.content.map((paragraph, i) => (
                    <p key={i} className="text-neutral-600">
                      {paragraph}
                    </p>
                  ))}
                </div>
              ) : (
                <p className="text-neutral-600">{item.content}</p>
              )}
            </div>
          )}
        </div>
      ))}
    </div>
  );
};

export default Accordion;
