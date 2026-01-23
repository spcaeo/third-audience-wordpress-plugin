'use client';

import { useEffect, useState } from 'react';
import { createPortal } from 'react-dom';
import './TeamMemberPopup.scss';

export default function TeamMemberPopup() {
  const [isOpen, setIsOpen] = useState(false);
  const [mounted, setMounted] = useState(false);
  const [popupContent, setPopupContent] = useState<string>('');

  useEffect(() => {
    setMounted(true);

    const openModal = (content: string) => {
      setPopupContent(content);
      setIsOpen(true);
    };

    const closeModal = () => {
      setIsOpen(false);
      setPopupContent('');
    };

    // Bind click handlers to .teams-content-bx elements
    const bindTriggers = () => {
      const triggers = document.querySelectorAll('.teams-content-bx:not([data-popup-bound])');
      triggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();

          // Find parent .team-member-card and get .teamd-popup-desc content
          const card = trigger.closest('.team-member-card');
          const popupDesc = card?.querySelector('.teamd-popup-desc');

          if (popupDesc) {
            openModal(popupDesc.innerHTML);
          }
        });
        trigger.setAttribute('data-popup-bound', 'true');
      });
    };

    bindTriggers();

    // MutationObserver for dynamically loaded content
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

    // Expose API on window for programmatic control
    (window as any).teamMemberPopup = {
      open: openModal,
      close: closeModal
    };

    return () => {
      observer.disconnect();
      document.removeEventListener('keydown', handleEscape);
      document.body.style.overflow = '';

      // Clean up bound attributes
      const triggers = document.querySelectorAll('[data-popup-bound]');
      triggers.forEach(trigger => {
        trigger.removeAttribute('data-popup-bound');
      });
    };
  }, [isOpen]);

  // Body scroll lock
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

  const handleClose = () => {
    setIsOpen(false);
    setPopupContent('');
  };

  if (!mounted || !isOpen) return null;

  return createPortal(
    <div className="team-popup-overlay" onClick={handleClose}>
      <div
        className="team-popup-container"
        onClick={(e) => e.stopPropagation()}
      >
        <button
          onClick={handleClose}
          className="team-popup-close"
          aria-label="Close popup"
        >
          Close
        </button>

        <div
          className="team-popup-body"
          dangerouslySetInnerHTML={{ __html: popupContent }}
        />
      </div>
    </div>,
    document.body
  );
}
