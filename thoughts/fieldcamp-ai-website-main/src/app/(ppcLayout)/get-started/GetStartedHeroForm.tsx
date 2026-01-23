"use client";

import React, { useState, FormEvent } from 'react';

declare const Calendly: any;

const CALENDLY_URL = 'https://calendly.com/jeel-fieldcamp/30min';

export default function GetStartedHeroForm() {
  const [formData, setFormData] = useState({
    fullName: '',
    workEmail: '',
    employees: '',
    industry: ''
  });

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { id, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [id === 'full-name' ? 'fullName' : id === 'work-email' ? 'workEmail' : id]: value
    }));
  };

  const loadCalendlyScript = (): Promise<void> => {
    return new Promise((resolve) => {
      // Load Calendly CSS if not already loaded
      if (!document.querySelector('link[href="https://calendly.com/assets/external/widget.css"]')) {
        const calendlyStylesheet = document.createElement('link');
        calendlyStylesheet.rel = 'stylesheet';
        calendlyStylesheet.href = 'https://calendly.com/assets/external/widget.css';
        document.head.appendChild(calendlyStylesheet);
      }

      // Load Calendly script if not already loaded
      if (!document.querySelector('script[src="https://assets.calendly.com/assets/external/widget.js"]')) {
        const script = document.createElement('script');
        script.src = 'https://assets.calendly.com/assets/external/widget.js';
        script.onload = () => resolve();
        document.head.appendChild(script);
      } else {
        resolve();
      }
    });
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    // Combine employees and industry with labels on separate lines
    const combinedInfo = `No of Employees: ${formData.employees}, Select Industry: ${formData.industry}`;

    // Build URL with all parameters
    const url = new URL(CALENDLY_URL);

    // Prefill name and email
    url.searchParams.set('name', formData.fullName);
    url.searchParams.set('email', formData.workEmail);

    // Prefill second custom question "How many field technicians do you have?"
    url.searchParams.set('a2', combinedInfo);

    // UTM parameters
    url.searchParams.set('utm_source', 'get-started-page');
    url.searchParams.set('utm_medium', window.location.href);
    url.searchParams.set('utm_content', combinedInfo);

    // Load and open Calendly
    await loadCalendlyScript();

    if (typeof Calendly !== 'undefined') {
      // Replace + with %20 to avoid showing + signs in Calendly
      const finalUrl = url.toString().replace(/\+/g, '%20');
      Calendly.showPopupWidget(finalUrl);
    }
  };

  return (
    <form className="hero-form" onSubmit={handleSubmit}>
      <div className="form-group">
        <label className="form-label" htmlFor="full-name">Full Name</label>
        <input
          type="text"
          id="full-name"
          className="form-input"
          placeholder="Enter Full Name"
          value={formData.fullName}
          onChange={handleInputChange}
          required
        />
      </div>
      <div className="form-group">
        <label className="form-label" htmlFor="work-email">Work Email</label>
        <input
          type="email"
          id="work-email"
          className="form-input"
          placeholder="Enter Work Email"
          value={formData.workEmail}
          onChange={handleInputChange}
          required
        />
      </div>
      <div className="form-group">
        <label className="form-label" htmlFor="employees">No of Employees</label>
        <select
          id="employees"
          className="form-select"
          value={formData.employees}
          onChange={handleInputChange}
          required
        >
          <option value="" disabled>Select Team Size</option>
          <option value="1-5">1-5</option>
          <option value="6-10">6-10</option>
          <option value="11-25">11-25</option>
          <option value="26-50">26-50</option>
          <option value="51+">51+</option>
        </select>
      </div>
      <div className="form-group">
        <label className="form-label" htmlFor="industry">Select Industry</label>
        <select
          id="industry"
          className="form-select"
          value={formData.industry}
          onChange={handleInputChange}
          required
        >
          <option value="" disabled>Select Your Industry</option>
          <option value="HVAC">HVAC</option>
          <option value="Plumbing">Plumbing</option>
          <option value="Electrical">Electrical</option>
          <option value="Cleaning">Cleaning</option>
          <option value="Landscaping">Landscaping</option>
          <option value="Pest Control">Pest Control</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <button type="submit" className="form-submit-btn">Book A Demo</button>
    </form>
  );
}
