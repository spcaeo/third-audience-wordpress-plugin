'use client';

import React, { useEffect } from 'react';

/**
 * StepImageSwitcher - Handles click-to-change image functionality
 * Uses index-based matching (no data attributes needed)
 *
 * WordPress Gutenberg HTML Structure Required:
 *
 * <div class="step-image-section">
 *   <div class="step-list">
 *     <div class="step-item active">...</div>  <!-- Index 0 -->
 *     <div class="step-item">...</div>         <!-- Index 1 -->
 *     <div class="step-item">...</div>         <!-- Index 2 -->
 *     <div class="step-item">...</div>         <!-- Index 3 -->
 *   </div>
 *   <div class="step-image-wrapper">
 *     <figure class="step-image-fig active">...</figure>  <!-- Index 0 -->
 *     <figure class="step-image-fig">...</figure>         <!-- Index 1 -->
 *     <figure class="step-image-fig">...</figure>         <!-- Index 2 -->
 *     <figure class="step-image-fig">...</figure>         <!-- Index 3 -->
 *   </div>
 * </div>
 */

const StepImageSwitcher: React.FC = () => {
  useEffect(() => {
    // Function to set active step by index
    const setActiveStep = (activeIndex: number) => {
      const stepSection = document.querySelector('.step-image-section');
      if (!stepSection) return;

      const stepItems = stepSection.querySelectorAll('.step-item');
      const stepImages = stepSection.querySelectorAll('.step-image-fig');

      // Update step items
      stepItems.forEach((item, index) => {
        if (index === activeIndex) {
          item.classList.add('active');
        } else {
          item.classList.remove('active');
        }
      });

      // Update images
      stepImages.forEach((img, index) => {
        if (index === activeIndex) {
          img.classList.add('active');
        } else {
          img.classList.remove('active');
        }
      });
    };

    // Handle step item click
    const handleStepClick = (e: Event) => {
      const target = e.target as Element;
      const stepItem = target.closest('.step-item');
      const stepSection = document.querySelector('.step-image-section');

      if (!stepItem || !stepSection || !stepSection.contains(stepItem)) return;

      const stepItems = stepSection.querySelectorAll('.step-item');

      // Find the index of the clicked step
      const stepIndex = Array.from(stepItems).indexOf(stepItem);
      if (stepIndex !== -1) {
        setActiveStep(stepIndex);
      }
    };

    // Initialize - set first step as active
    const initializeSteps = () => {
      const stepSection = document.querySelector('.step-image-section');
      if (!stepSection) return;

      const stepItems = stepSection.querySelectorAll('.step-item');
      const stepImages = stepSection.querySelectorAll('.step-image-fig');

      if (stepItems.length === 0 || stepImages.length === 0) return;

      // Remove active from all images first
      stepImages.forEach(img => img.classList.remove('active'));

      // Find if any step is already marked active
      let activeIndex = Array.from(stepItems).findIndex(
        item => item.classList.contains('active')
      );

      // If no active step, default to first
      if (activeIndex === -1) {
        activeIndex = 0;
      }

      setActiveStep(activeIndex);
    };

    // Wait for DOM to be fully ready (after hydration)
    const timeoutId = setTimeout(() => {
      initializeSteps();
    }, 100);

    // Use event delegation on document for click handling
    document.addEventListener('click', handleStepClick);

    // Cleanup
    return () => {
      clearTimeout(timeoutId);
      document.removeEventListener('click', handleStepClick);
    };
  }, []);

  // Automation Image Toggle
  useEffect(() => {
    const setAutomationActive = (isWithAutomation: boolean) => {
      const withImg = document.querySelector('.with-automation-img');
      const withoutImg = document.querySelector('.without-automation-img');
      const withBtn = document.querySelector('.with-automtion-btn');
      const withoutBtn = document.querySelector('.without-automtion-btn');

      if (isWithAutomation) {
        withImg?.classList.add('active');
        withoutImg?.classList.remove('active');
        withBtn?.classList.add('active');
        withoutBtn?.classList.remove('active');
      } else {
        withImg?.classList.remove('active');
        withoutImg?.classList.add('active');
        withBtn?.classList.remove('active');
        withoutBtn?.classList.add('active');
      }
    };

    const handleAutomationClick = (e: Event) => {
      const target = e.target as Element;
      if (target.closest('.with-automtion-btn')) {
        setAutomationActive(true);
      } else if (target.closest('.without-automtion-btn')) {
        setAutomationActive(false);
      }
    };

    // Initialize with "with automation" as default
    const automationTimeoutId = setTimeout(() => {
      setAutomationActive(true);
    }, 100);

    document.addEventListener('click', handleAutomationClick);

    return () => {
      clearTimeout(automationTimeoutId);
      document.removeEventListener('click', handleAutomationClick);
    };
  }, []);

  return null;
};

export default StepImageSwitcher;
