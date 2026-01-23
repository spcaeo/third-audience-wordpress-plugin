'use client';

import React, { useEffect, useState } from 'react';

const Tabbing: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'monthly' | 'annual'>('monthly');

  useEffect(() => {
    const monthlyBtn = document.querySelector('.tab-month-btn');
    const annualBtn = document.querySelector('.tab-annual-btn');
    const monthlyContent = document.querySelector('.pricing-tab-monthly');
    const annualContent = document.querySelector('.pricing-tab-annual');

    const updateActiveClasses = () => {
      if (activeTab === 'monthly') {
        monthlyBtn?.classList.add('active');
        annualBtn?.classList.remove('active');
        monthlyContent?.classList.add('active');
        annualContent?.classList.remove('active');
      } else {
        monthlyBtn?.classList.remove('active');
        annualBtn?.classList.add('active');
        monthlyContent?.classList.remove('active');
        annualContent?.classList.add('active');
      }
    };

    updateActiveClasses();

    const handleMonthlyClick = (e: Event) => {
      e.preventDefault();
      setActiveTab('monthly');
    };

    const handleAnnualClick = (e: Event) => {
      e.preventDefault();
      setActiveTab('annual');
    };

    const monthlyButton = document.querySelector('.tab-month-btn');
    const annualButton = document.querySelector('.tab-annual-btn');

    if (monthlyButton) {
      monthlyButton.addEventListener('click', handleMonthlyClick);
    }
    if (annualButton) {
      annualButton.addEventListener('click', handleAnnualClick);
    }

    return () => {
      if (monthlyButton) {
        monthlyButton.removeEventListener('click', handleMonthlyClick);
      }
      if (annualButton) {
        annualButton.removeEventListener('click', handleAnnualClick);
      }
    };
  }, [activeTab]);

  return (
    <div></div>
  );
};

export default Tabbing;