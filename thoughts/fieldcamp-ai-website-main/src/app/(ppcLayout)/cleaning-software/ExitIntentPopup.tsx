'use client';

import React, { useState, useEffect } from 'react';

export default function ExitIntentPopup() {
  const [showPopup, setShowPopup] = useState(false);
  const [currentStep, setCurrentStep] = useState(1);
  const [hasShown, setHasShown] = useState(false);

  useEffect(() => {
    let exitTimer: NodeJS.Timeout;

    const handleMouseLeave = (e: MouseEvent) => {
      // Detect if mouse is moving towards the top of the screen (exit intent)
      if (!hasShown && e.clientY <= 5 && e.relatedTarget === null) {
        setShowPopup(true);
        setHasShown(true);
      }
    };

    const handleKeyDown = (e: KeyboardEvent) => {
      // Detect Alt+Left (back button) or Alt+Right (forward button)
      if (!hasShown && (e.altKey && (e.key === 'ArrowLeft' || e.key === 'ArrowRight'))) {
        setShowPopup(true);
        setHasShown(true);
      }
    };

    // Mobile exit intent detection
    const handleVisibilityChange = () => {
      if (!hasShown && document.visibilityState === 'hidden') {
        exitTimer = setTimeout(() => {
          setShowPopup(true);
          setHasShown(true);
        }, 100);
      }
    };

    // Add event listeners
    document.addEventListener('mouseleave', handleMouseLeave);
    document.addEventListener('keydown', handleKeyDown);
    document.addEventListener('visibilitychange', handleVisibilityChange);

    return () => {
      document.removeEventListener('mouseleave', handleMouseLeave);
      document.removeEventListener('keydown', handleKeyDown);
      document.removeEventListener('visibilitychange', handleVisibilityChange);
      if (exitTimer) clearTimeout(exitTimer);
    };
  }, [hasShown]);

  const closePopup = () => {
    setShowPopup(false);
  };

  const goToStep2 = () => {
    setCurrentStep(2);
  };

  const lossBreakdown = [
    { label: 'Missed calls after hours', amount: 1840 },
    { label: 'Inefficient routing', amount: 1220 },
    { label: 'Manual scheduling errors', amount: 980 },
    { label: 'Late invoices', amount: 680 }
  ];

  if (!showPopup) return null;

  return (
    <div className="fixed inset-0 z-[9999] bg-black bg-opacity-60 flex items-center justify-center p-4">
      {/* Close button */}
      <button
        onClick={closePopup}
        className="absolute top-6 right-6 text-white hover:text-gray-400 transition-colors z-10"
      >
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      {/* Step 1: The Hook */}
      {currentStep === 1 && (
        <div className="bg-white rounded-2xl p-12 max-w-2xl w-full text-center border border-gray-200">
          <h2 className="text-[26px] md:text-[30px] lg:text-[32px] font-bold leading-[1.3] text-gray-900 mb-6">
            Your Cleaning Business is Losing
          </h2>
          <div className="mb-6">
            <div className="text-[48px] md:text-[60px] font-bold text-gray-900 leading-none">
              $4,720
            </div>
            <div className="text-[18px] text-gray-600 mt-4">
              every month
            </div>
          </div>
          <p className="text-[18px] md:text-[18px] leading-relaxed text-gray-600 mb-6">
            Want to see the breakdown?
          </p>
          
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <button
              onClick={goToStep2}
              className="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity"
            >
              Yes, Show Me How
            </button>
            <button
              onClick={closePopup}
              className="border-2 border-gray-300 hover:border-gray-400 px-8 py-3 rounded-xl font-medium transition-colors text-gray-700 hover:text-gray-900"
            >
              No, I'm Good With Less Money
            </button>
          </div>
        </div>
      )}

      {/* Step 2: The Proof */}
      {currentStep === 2 && (
        <div className="bg-white rounded-2xl p-12 max-w-4xl w-full text-center border border-gray-200">
          <h2 className="text-[26px] md:text-[30px] lg:text-[32px] font-bold leading-[1.3] text-gray-900 mb-12">
            Here's Where Your Money Is Going:
          </h2>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
            {lossBreakdown.map((item, index) => (
              <div
                key={index}
                className="bg-gray-50 border border-gray-200 rounded-2xl p-6 animate-fadeIn"
                style={{ animationDelay: `${index * 0.3}s` }}
              >
                <div className="text-[24px] font-bold text-gray-900 mb-3">
                  ${item.amount.toLocaleString()}
                </div>
                <div className="text-[16px] text-gray-600 leading-relaxed">
                  {item.label}
                </div>
              </div>
            ))}
          </div>

          <div className="bg-gray-50 border border-gray-200 rounded-2xl p-8 mb-12">
            <div className="text-[18px] text-gray-600 mb-2">Total Monthly Loss:</div>
            <div className="text-[36px] md:text-[42px] font-bold text-gray-900">
              $4,720
            </div>
          </div>

          <div className="mb-12">
            <p className="text-[18px] md:text-[18px] leading-relaxed text-gray-600 max-w-[500px] mx-auto">
              FieldCamp fixes all of these problems automatically
            </p>
          </div>

          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <a
              href="https://calendly.com/jeel-fieldcamp/30min"
              className="calendly-open utm-medium-signup bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity shadow-lg"
              onClick={closePopup}
            >
              Book Free Demo - Stop the Loss
            </a>
            <button
              onClick={closePopup}
              className="border-2 border-gray-300 hover:border-gray-400 px-8 py-3 rounded-xl font-medium transition-colors text-gray-700 hover:text-gray-900"
            >
              Maybe Later
            </button>
          </div>
        </div>
      )}

      <style jsx>{`
        @keyframes fadeIn {
          from {
            opacity: 0;
            transform: translateY(20px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }
        
        .animate-fadeIn {
          animation: fadeIn 0.6s ease-out forwards;
          opacity: 0;
        }
      `}</style>
    </div>
  );
}