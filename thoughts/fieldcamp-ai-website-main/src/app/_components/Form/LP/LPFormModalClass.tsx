'use client';

import { useEffect } from 'react';
import { createPortal } from 'react-dom';
import { useState } from 'react';
import LPForm from './FormHTML';

export default function LPFormModalClass() {
  const [isOpen, setIsOpen] = useState(false);

  useEffect(() => {
    // Modal management functions
    const openModal = () => setIsOpen(true);
    const closeModal = () => setIsOpen(false);

    // Bind trigger buttons
    const bindTriggers = () => {
      const triggers = document.querySelectorAll('.lp-form-trigger:not([data-lp-bound])');
      triggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
          e.preventDefault();
          openModal();
        });
        trigger.setAttribute('data-lp-bound', 'true');
      });
    };

    // Initial binding
    bindTriggers();

    // Re-bind on DOM changes (for dynamic content)
    const observer = new MutationObserver(() => {
      bindTriggers();
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true
    });

    // Escape key handler
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape' && isOpen) {
        closeModal();
      }
    };

    document.addEventListener('keydown', handleEscape);

    // Make functions globally available
    (window as any).lpFormModal = {
      open: openModal,
      close: closeModal
    };

    // Cleanup
    return () => {
      observer.disconnect();
      document.removeEventListener('keydown', handleEscape);
      document.body.style.overflow = '';
    };
  }, [isOpen]);

  // Prevent background scrolling when modal is open
  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }

    return () => {
      document.body.style.overflow = '';
    };
  }, [isOpen]);

  const closeModal = () => setIsOpen(false);

  if (!isOpen) return null;

  return createPortal(
    <div className="lp-modal fixed inset-0 z-50 flex items-center justify-center">
      {/* Backdrop */}
      <div 
        className="lp-modal-backdrop fixed inset-0 bg-black bg-opacity-50 transition-opacity"
        onClick={closeModal}
      />
      
      {/* Modal */}
      <div className="lp-modal-content relative bg-white rounded-lg shadow-xl max-w-[600px] w-full mx-4 max-h-[90vh] overflow-y-auto">
        {/* Close button */}
        <button
          onClick={closeModal}
          className="lp-modal-close absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors z-10"
        >
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        
        {/* Form content */}
        <div className="lp-modal-form-container p-6 pt-12">
          <LPForm />
        </div>
      </div>
    </div>,
    document.body
  );
}