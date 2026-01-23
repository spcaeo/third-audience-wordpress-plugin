'use client';

import { useEffect, useState, ReactNode } from 'react';
import { createPortal } from 'react-dom';
import Image from 'next/image';
import './modal.scss';

interface ModalProps {
  children?: ReactNode;
}

type ModalContentType = 'client-communication' | 'team-management' | 'smart-dispatch' | 'default';

export default function ServiceModal({ children }: ModalProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [mounted, setMounted] = useState(false);
  const [contentType, setContentType] = useState<ModalContentType>('default');

  useEffect(() => {
    setMounted(true);
  }, []);

  useEffect(() => {
    const openModal = (type: ModalContentType = 'default') => {
      setContentType(type);
      setIsOpen(true);
    };
    const closeModal = () => setIsOpen(false);

    const bindTriggers = () => {
      // Bind client communication triggers
      const clientTriggers = document.querySelectorAll('.client-rel-popup:not([data-modal-bound])');
      clientTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          openModal('client-communication');
        });
        trigger.setAttribute('data-modal-bound', 'true');
      });

      // Bind team management triggers
      const teamTriggers = document.querySelectorAll('.team-mng-popup:not([data-modal-bound])');
      teamTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          openModal('team-management');
        });
        trigger.setAttribute('data-modal-bound', 'true');
      });

      // Bind smart dispatch triggers
      const dispatchTriggers = document.querySelectorAll('.smart-dispatch-popup:not([data-modal-bound])');
      dispatchTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          openModal('smart-dispatch');
        });
        trigger.setAttribute('data-modal-bound', 'true');
      });

      // Keep backward compatibility with click-on-talk
      const defaultTriggers = document.querySelectorAll('.click-on-talk:not([data-modal-bound])');
      defaultTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          openModal('default');
        });
        trigger.setAttribute('data-modal-bound', 'true');
      });
    };

    bindTriggers();

    const observer = new MutationObserver(() => {
      bindTriggers();
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true
    });

    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape' && isOpen) {
        closeModal();
      }
    };

    document.addEventListener('keydown', handleEscape);

    (window as any).serviceModal = {
      open: openModal,
      close: closeModal
    };

    return () => {
      observer.disconnect();
      document.removeEventListener('keydown', handleEscape);
      document.body.style.overflow = '';
      const triggers = document.querySelectorAll(`[data-modal-bound]`);
      triggers.forEach(trigger => {
        trigger.removeAttribute('data-modal-bound');
      });
    };
  }, [isOpen]);

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

  const renderContent = () => {
    switch(contentType) {
      case 'client-communication':
        return (
          <div className="service-popup">
            <div className="popup-img">
              <Image 
                src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/client-communication-imgage.svg"
                alt="Client Communication"
                width={550}
                height={300}
              />
            </div>

            <div className="popup-desc">
              <h2 className="popup-head">Client Communication</h2>
              <p className="popup-subhead">Click any customer. See everything.</p>
              
              <ul className="popup-list-desc">
                <li>
                  <span>-</span>
                  <span>Past jobs and who did them</span>
                </li>
                <li>
                  <span>-</span>
                  <span>Equipment info and notes</span>
                </li>
                <li>
                  <span>-</span>
                  <span>What they paid and when. Your techs see it too.</span>
                </li>
              </ul>

              <p className="italic-text">
                They know what they're walking into. No more "What unit do they have again?"
              </p>
            </div>
          </div>
        );
      
      case 'team-management':
        return (
          <div className="service-popup">
            <div className="popup-img">
              <Image 
                src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/team-mng-pop-image.svg"
                alt="Team Management"
                width={550}
                height={300}
              />
            </div>

            <div className="popup-desc">
              <h2 className="popup-head">Team management</h2>
              <p className="popup-subhead">Tech finishes a job. You see it.</p>
              <p className="popup-subhead">Tech takes photos. They're in the customer file.</p>
              <p className="popup-subhead">Customer signs. It's on the invoice.</p>
              
              <p className="italic-text">
                No paperwork to collect later. No "I'll tell you when I get back." Everything updates as it happens.
              </p>
            </div>
          </div>
        );
      
      case 'smart-dispatch':
        return (
          <div className="service-popup">
            <div className="popup-img">
              <Image 
                src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/dispatch-pop-image.svg"
                alt="Smart Scheduling & Dispatch"
                width={550}
                height={300}
              />
            </div>
            
            <div className="popup-desc">
              <h2 className="popup-head">Smart Scheduling<br />& Dispatch</h2>
              <p className="popup-subhead">Your whole team on one screen. Drag jobs to assign them. Colors show job types. Pins show locations. <br></br> Times show availability. Click any job to see who's qualified and closest. Or let AI optimize all routes at once - it considers traffic, skills, and drive time. </p>
              
              <p className="italic-text">
              Techs see their route on their phone with turn-by-turn directions. You see everyone's location in real-time. 
              </p>
            </div>
          </div>
        );
      
      default:
        return children || (
          <div className="default-popup">
            <h2>Let's Talk</h2>
            <p>Schedule a consultation with our experts to discuss how we can help your business grow.</p>
            <form>
              <div className="form-group">
                <label>Name</label>
                <input 
                  type="text" 
                  placeholder="Your name"
                />
              </div>
              <div className="form-group">
                <label>Email</label>
                <input 
                  type="email" 
                  placeholder="your@email.com"
                />
              </div>
              <div className="form-group">
                <label>Message</label>
                <textarea 
                  rows={4}
                  placeholder="Tell us about your project"
                />
              </div>
              <button type="submit">
                Send Message
              </button>
            </form>
          </div>
        );
    }
  };

  if (!mounted || !isOpen) return null;

  return createPortal(
    <div className="modal-overlay" onClick={closeModal}>
      <div 
        className="modal-container"
        onClick={(e) => e.stopPropagation()}
      >
        <button
          onClick={closeModal}
          className="modal-close"
          aria-label="Close modal"
        >
          ×
        </button>
        
        <div className="service-modal-body">
          {renderContent()}
        </div>
      </div>
    </div>,
    document.body
  );
}