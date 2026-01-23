import React from 'react';
import "./module.scss";
import { Metadata } from 'next';
import GetStartedHeroForm from './GetStartedHeroForm';
import dynamic from 'next/dynamic';

const ThumbnailSlider = dynamic(() => import('./ThumbnailSlider'), {
  ssr: false
});

export const metadata: Metadata = {
  title: 'Get Started | FieldCamp',
  description: 'Turn Your Field Ops Into Self-Running Workflows. FieldCamp is an intelligent, AI-enabled field service software that runs on your commands.',
  robots: 'noindex, nofollow',
  alternates: {
    canonical: 'https://fieldcamp.ai/get-started/'
  }
};

export default function GetStartedPage() {
  return (
    <div className="get-started-page">
      {/* Hero Section */}
      <section className="hero-section pt-[170px] pb-[130px]">
        <div className="container max-w-[1245px] mx-auto px-[15px] lg:px-[15px]">
          <div className="hero-content">
            <div className="hero-text">
              <h1 className="hero-heading">
                Turn Your Field Ops Into <span className="hero-heading-highlight">Self-Running Workflows</span>
              </h1>
              <p className="hero-description">
                FieldCamp is an intelligent, AI-enabled field service software that runs on your commands, chats like a friend and operates on its own.
              </p>
              <div className="hero-tags">
                <span className="hero-tag"><a href='https://fieldcamp.ai/features/ai-command-center/'>AI Command Center</a></span>
                <span className="hero-tag"><a href='https://fieldcamp.ai/ai-receptionist/'>AI Receptionist </a></span>
                <span className="hero-tag"><a href='https://fieldcamp.ai/features/ai-dispatch-scheduling/'>Auto-Dispatch</a></span>
                <span className="hero-tag"><a href='https://fieldcamp.ai/features/ai-workflow-builder/'>Smart Workflows</a></span>
                <span className="hero-tag"><a href='https://fieldcamp.ai/online-booking'>24/7 Online Booking </a></span>
              </div>
            </div>
            <div className="hero-form-wrapper">
              <div className="hero-form-card">
                <h2 className="hero-form-title">Let's Connect on a Call</h2>
                <p className="hero-form-subtitle">One short demo is all it takes to see why teams switch to FieldCamp.</p>
<GetStartedHeroForm />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Autopilot Section */}
      <section className="autopilot-section">
        <div className="autopilot-container">
          <h2 className="autopilot-heading">
            See How FieldCamp's<br />Autopilot Mode Works for Your Field Ops
          </h2>
          <div className="autopilot-steps">
            {/* Step 1 */}
            <div className="autopilot-step">
              <h3 className="autopilot-step-title">Instant Job Intake</h3>
              <div className="autopilot-step-icon">
                <img src='https://cms.fieldcamp.ai/wp-content/uploads/2025/12/schdule-add-ai.svg' alt='Instant Job Intake' />
              </div>
              <p className="autopilot-step-description">
                Jobs automatically flow into FieldCamp and get captured in your system — no missed opportunities, no manual entry, instantly organized.
              </p>
              <div className="autopilot-arrow">
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/12/arrow-get-started.png" alt="arrow" />
              </div>
            </div>

            {/* Step 2 */}
            <div className="autopilot-step">
              <h3 className="autopilot-step-title">AI Scheduling Engine</h3>
              <div className="autopilot-step-icon">
                <img src='https://cms.fieldcamp.ai/wp-content/uploads/2025/12/instant-job-maker.svg' alt='Instant Job Intake' />
              </div>
              <p className="autopilot-step-description">
                FieldCamp analyzes multiple constraints and builds the smartest schedule for your technicians directly inside your calendar.
              </p>
              <div className="autopilot-arrow">
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/12/arrow-get-started.png" alt="arrow" />
              </div>
            </div>

            {/* Step 3 */}
            <div className="autopilot-step">
              <h3 className="autopilot-step-title">Auto-Dispatch</h3>
              <div className="autopilot-step-icon">
                <img src='https://cms.fieldcamp.ai/wp-content/uploads/2025/12/perfect-wrappedup.svg' alt='Instant Job Intake' />
              </div>
              <p className="autopilot-step-description">
                Technicians get assigned instantly based on skills, location, availability, and priority - and routes re-optimize automatically as plans change.
              </p>
              <div className="autopilot-arrow">
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/12/arrow-get-started.png" alt="arrow" />
              </div>
            </div>

            {/* Step 4 */}
            <div className="autopilot-step">
              <h3 className="autopilot-step-title">Daily Workflows Automated</h3>
              <div className="autopilot-step-icon">
                <img src='https://cms.fieldcamp.ai/wp-content/uploads/2025/12/schdule-add-ai.svg' alt='Instant Job Intake' />
              </div>
              <p className="autopilot-step-description">
                FieldCamp sends reminders, confirmations, ETAs, follow-ups, recurring tasks, and notifications automatically the moment conditions are triggered.
              </p>
              <div className="autopilot-arrow">
                <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/12/arrow-get-started.png" alt="arrow" />
              </div>
            </div>

            {/* Step 5 */}
            <div className="autopilot-step">
              <h3 className="autopilot-step-title">A Perfect Wrap-up</h3>
              <div className="autopilot-step-icon">
                <img src='https://cms.fieldcamp.ai/wp-content/uploads/2025/12/daily-workflow-ai.svg' alt='Instant Job Intake' />
              </div>
              <p className="autopilot-step-description">
                Jobs finish, invoices go out, payments get logged, and FieldCamp updates your revenue - instantly, accurately, and without any admin work.
              </p>
            </div>
          </div>
        </div>
      </section>

      <section className="feature-secton">
        <div className="autopilot-container">
          <div className='heading-section'>
              <h2 className="autopilot-heading">
            One AI Brain Running Your Field Operations
          </h2>
          <p>From planning schedules to assigning techs and answering customers, FieldCamp coordinates everything automatically in the background.</p>
          </div>
          <ThumbnailSlider />
        </div>
      </section>

      {/* Features Section */}
      <section className="features-section">
        <div className="features-container">
          <div className="features-content">
            <div className="features-text">
              <h2 className="features-heading">Your Entire Field Operations. Fully Automated.</h2>
              <p className="features-description">
                Replace scattered tools with one AI backed system that manages your entire field operation automatically.
              </p>
            </div>
            <div className="features-tags-wrapper">
              <div className="features-tags-grid">
                <div className="features-tags-row">
                  <a href='https://fieldcamp.ai/features/ai-job-scheduling/'><span className="features-tag">Smart Scheduling</span></a>
                  <a href='https://fieldcamp.ai/features/ai-dispatch-scheduling/'><span className="features-tag">Auto-Dispatching</span></a>
                  <a href='https://fieldcamp.ai/features/ai-command-center/'><span className="features-tag">AI Command Center</span></a>
                </div>
                <div className="features-tags-row">
                  <a href='https://fieldcamp.ai/features/ai-route-optimization/'><span className="features-tag">Route Optimization</span></a>
                  <a href='https://fieldcamp.ai/features/quotes/'><span className="features-tag">Estimates</span></a>
                  <a href='https://fieldcamp.ai/customers/'><span className="features-tag">Client Communication</span></a>
                </div>
                <div className="features-tags-row">
                  <a href='https://fieldcamp.ai/features/field-service-invoicing-software/'><span className="features-tag">Invoicing</span></a>
                  <a href='https://fieldcamp.ai/features/work-order-management/'><span className="features-tag">Job Management</span></a>
                  <a href='https://fieldcamp.ai/features/ai-workflow-builder/'><span className="features-tag">Automated Workflows</span></a>
                  
                </div>
                <div className="features-tags-row">
                  <a href='https://fieldcamp.ai/features/ai-crm/'><span className="features-tag">CRM</span></a>
                  <a href='https://fieldcamp.ai/features/file-management/'><span className="features-tag">Document Management</span></a>
                  <a href='https://fieldcamp.ai/features/field-service-reporting-software/'><span className="features-tag">Reporting & Analytics</span></a>
                </div>
                <div className="features-tags-row">
                  <a href='https://fieldcamp.ai/features/inventory-management/'><span className="features-tag">Inventory Management</span></a>
                  <a href='https://fieldcamp.ai/features/team-management/'><span className="features-tag">Team Management</span></a>
                  <a href='https://fieldcamp.ai/online-booking/service-area-management'><span className="features-tag">Service Area Validation</span></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Evolves You Section */}
      <section className="evolves-you-section">
        <div className="max-w-1245">
          <div className="evolves-columns">
            {/* Left Column - 65% */}
            <div className="evolves-left-column">
              <div className="ctaboxfiulldesc">
                <div className="evolves-text-wrapper">
                  <h2>Experience Hands-Off Field Operations</h2>
                  <p>
                    See how FieldCamp can streamline your day from intake to invoicing; jobs flow in, techs get assigned, customers get updated, and invoices go out, all without you lifting a finger.
                  </p>
                </div>
                <div className="evolves-image-wrapper">
                  <figure>
                    <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/11/cta-flower-path.png" alt="FieldCamp Evolves You" />
                  </figure>
                </div>
              </div>
            </div>
            {/* Right Column - boxctafullhome */}
            <div className="evolves-right-column boxctafullhome">
              <div className="black-cta-fieldcamp">
                <a href="https://calendly.com/jeel-fieldcamp/30min" className="btn-book-demo calendly-open">Book a Demo</a>
              </div>
            </div>
          </div>
        </div>
      </section>

    {/* FAQ Section */}
      <section className="common-faq-section faq-feartire">
        <div className="max-w-1245">
          <div className="faq-flex">
            {/* Left Column - 40% */}
            <div className="faq-left-column">
              <div className="heading-column">
                <h2>Frequently Asked Questions</h2>
              </div>
            </div>

            {/* Right Column - 60% */}
            <div className="faq-right-column">
              <div className="common-faq-wrapper">
                <details className="wp-block-details">
                  <summary>What makes FieldCamp different from other field service software?</summary>
                  <p>FieldCamp isn’t traditional field service software — it’s an AI-powered platform that actually does the operational work for you. Instead of just storing data or giving you tools, FieldCamp automates scheduling, dispatching, customer communication, invoicing, and daily workflows. You’re not clicking through screens all day; you’re letting AI run your operations in the background. That’s what makes FieldCamp fundamentally different.</p>
                </details>

                <details className="wp-block-details">
                  <summary>Will FieldCamp work for my specific industry or service business?</summary>
                  <p>Yes. FieldCamp is designed for any business that performs work on-site. Whether you’re in HVAC, plumbing, electrical, cleaning, lawn care, pest control, appliance repair, or general home services, the platform adapts to your workflow. If your team travels to job locations, FieldCamp can automate your operations and streamline how your technicians, office staff, and customers interact.</p>
                </details>

                <details className="wp-block-details">
                  <summary>How much of my daily work can FieldCamp actually automate?</summary>
                  <p>A large portion of your day-to-day tasks can be automated. FieldCamp handles job intake, smart scheduling, dispatching, reminders, ETAs, customer communication, follow-ups, recurring tasks, invoicing, payments, and updates — all without manual effort. Instead of jumping between tools or managing the chaos yourself, FieldCamp keeps everything running automatically so your team can stay focused on real work in the field.</p>
                </details>

                <details className="wp-block-details">
                  <summary>Do I need to change my existing tools or systems to use FieldCamp?</summary>
                  <p>Not at all. FieldCamp connects seamlessly with the tools you already rely on. With integrations like QuickBooks, Stripe, Google Calendar, and Slack, your data flows automatically without requiring you to rebuild your processes. You can keep your existing systems in place while FieldCamp automates the coordination around them, reducing manual entry and eliminating duplicate work across platforms.</p>
                </details>

                <details className="wp-block-details">
                  <summary>Is FieldCamp easy for my technicians to use?</summary>
                  <p>Yes. FieldCamp uses a chat-based and command-centric interface designed for technicians who want speed and simplicity. They can type or speak instructions like “schedule a job,” “add a client,” or “show today’s tasks,” and the AI handles the rest instantly. There’s no complex training or learning curve — if your techs can text, they can use FieldCamp from day one.</p>
                </details>

                <details className="wp-block-details">
                  <summary>Can FieldCamp really handle after-hours calls and bookings?</summary>
                  <p>Yes. FieldCamp includes a 24/7 online booking portal and an AI receptionist that work together to manage customer requests even when your office is closed. New leads can book appointments, get questions answered, verify service areas, and choose available time slots automatically. Your team wakes up to a schedule that’s already filled, organized, and ready for the day — without missing a single call.</p>
                </details>

                <details className="wp-block-details">
                  <summary>How does pricing work? Do I have to pay for features I don’t need?</summary>
                  <p>FieldCamp uses flexible, customizable plans based on your team size, workflow needs, and level of automation. You never pay for features you won’t use. Solo operators don’t need enterprise tools, and larger teams shouldn’t be stuck with basic limitations — so we tailor the plan to your business. You only pay for what actually supports your operations and growth.</p>
                </details>

                 <details className="wp-block-details">
                  <summary>What do I see in the demo?</summary>
                  <p>The demo shows you exactly how FieldCamp automates your operations end-to-end. You’ll see jobs getting captured automatically, schedules optimized by AI, technicians dispatched instantly, customers receiving updates, and invoices sent without manual work. It’s a walkthrough of how your current workflow transforms into a hands-off, self-running process powered by AI — personalized to your business.</p>
                </details>

                <details className="wp-block-details">
                  <summary>Is there any free trial?</summary>
                  <p>Yes. FieldCamp offers a 14-day free trial with full access to its core AI features. You can test how automation affects your scheduling, dispatching, customer communication, and overall workflow without paying anything upfront. It’s the easiest way to experience what hands-off field operations feel like before making a decision.</p>
                </details>

              </div>
            </div>
          </div>
        </div>
      </section>

    </div>
  );
}
