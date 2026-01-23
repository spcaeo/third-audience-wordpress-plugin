'use client';

import React, { useState } from 'react';

const slides = [
  {
    image: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/12/field-operation-img-1.png',
    title: 'Schedules that Builds Itself',
    description: 'FieldCamp suggests the best possible schedules automatically, reflects them instantly in your calendar, and lets you schedule or reschedule jobs using simple commands — no manual planning required.'
  },
  {
    image: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/12/field-operation-img-2.png',
    title: 'Dispatch that Runs on Autopilot',
    description: 'FieldCamp understands job requirements, technician skills, location, and priority to assign the best tech instantly. When plans change, routes and assignments re-optimize automatically and updates go out without effort.'
  },
  {
    image: 'https://cms.fieldcamp.ai/wp-content/uploads/2025/12/field-operation-img-3.png',
    title: 'AI Receptionist That’s Always-On',
    description: "FieldCamp’s AI receptionist answers customer questions, captures job details, checks availability, and books appointments automatically — keeping your business responsive day and night."
  }
];

export default function ThumbnailSlider() {
  const [activeSlide, setActiveSlide] = useState(0);

  const goToPrev = () => {
    setActiveSlide((prev) => (prev === 0 ? slides.length - 1 : prev - 1));
  };

  const goToNext = () => {
    setActiveSlide((prev) => (prev === slides.length - 1 ? 0 : prev + 1));
  };

  return (
    <section className="thumbnail-slider-section">
      <div className="thumbnail-slider-container">
        {/* Main Content Slider */}
        <div className="main-content-slider">
          {slides.map((slide, index) => (
            <div
              key={index}
              className={`slider-slide ${index === activeSlide ? 'active' : ''}`}
            >
              <div className="main-content">
                <img src={slide.image} alt={slide.title} />
              </div>
            </div>
          ))}

          {/* Navigation Arrows */}
          <div className="nav-arrows">
            <button className="nav-arrow nav-arrow-prev" onClick={goToPrev} aria-label="Previous slide">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 18L9 12L15 6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </button>
            <button className="nav-arrow nav-arrow-next" onClick={goToNext} aria-label="Next slide">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 18L15 12L9 6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </button>
          </div>
        </div>

        {/* Thumbnail Navigation */}
        <div className="thumbnail-navigation">
          {slides.map((slide, index) => (
            <div
              key={index}
              className={`thumbnail-slide ${index === activeSlide ? 'active' : ''}`}
              onClick={() => setActiveSlide(index)}
            >
              <div className="thumbnail-content">
                <h3>{slide.title}</h3>
                <p>{slide.description}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
