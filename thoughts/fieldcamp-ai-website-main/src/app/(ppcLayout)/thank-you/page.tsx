import React from 'react';
import "./module.scss"
import { Metadata } from 'next';
import Link from 'next/link';

export const metadata: Metadata = {
  title: 'Thank You | FieldCamp',
  description: 'Thank you for your interest in FieldCamp. Your AI Copilot just got the signal.',
  robots: 'noindex, nofollow',
};

export default function ThankYouPage() {
  return (
    <div className="thank-you-page">
      {/* Confetti Animation Container */}
      <div className="confetti-container">
        {[...Array(100)].map((_, i) => (
          <div key={i} className={`confetti confetti-${(i % 20) + 1}`}></div>
        ))}
      </div>
      
      <div className="thank-you-container">
        {/* Main Content Section */}
        <div className="thank-you-content">
          <h1 className="thank-you-title">Thank You</h1>
          
          <div className="thank-you-divider"></div>
          
          <h2 className="thank-you-subtitle">
            Boom! Your AI Copilot Just Got the Signal.
          </h2>
          
          <p className="thank-you-message">
            You're done. Ball's in our court now.
          </p>
          
          <p className="thank-you-description">
            FieldCamp's smart systems are already lining up your next moves—no clicks, no chaos.<br />
            Sit tight while our team gears up to show you the future of field service.
          </p>
          
          <h3 className="explore-title">Till then, Explore What FieldCamp is</h3>
          
          {/* YouTube Video Embed */}
          <div className="video-container">
            <iframe
              width="100%"
              height="300"
              src="https://www.youtube.com/embed/qDIE6DaIAWU?si=GvhY10-ok7J5psvP"
              title="FieldCamp Demo"
              frameBorder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowFullScreen
              className="youtube-embed"
            ></iframe>
          </div>
          
          {/* Bottom Links */}
          <div className="thank-you-footer">
            <p className="footer-text">
              Need help right now? We've got your back 24/7.
            </p>
            <Link href="mailto:support@fieldcamp.ai" className="support-link">
              support@fieldcamp.ai
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}