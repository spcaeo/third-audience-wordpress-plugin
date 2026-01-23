'use client';

import { useState, useEffect, Suspense } from 'react';
import { useSearchParams } from 'next/navigation';

type FormErrors = {
  fullName?: string;
  email?: string;
  companyName?: string;
  phone?: string;
  teamSize?: string;
  biggestChallenge?: string;
};

function LPFormContent() {
  const searchParams = useSearchParams();
  const [errors, setErrors] = useState<FormErrors>({});
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [adsByGroup, setAdsByGroup] = useState<string>('');
  const [keyword, setKeyword] = useState<string>('');

  // Capture URL parameters on component mount
  useEffect(() => {
    setAdsByGroup(searchParams.get('AdsByGroup') || '');
    setKeyword(searchParams.get('Keyword') || '');
  }, [searchParams]);

  const validateForm = (formData: FormData): boolean => {
    const newErrors: FormErrors = {};
    
    // Validate name
    if (!formData.get('fullName')) {
      newErrors.fullName = 'Your name is required';
    }

    // Validate email
    const email = formData.get('email') as string;
    if (!email) {
      newErrors.email = 'Work email is required';
    } else if (!/\S+@\S+\.\S+/.test(email)) {
      newErrors.email = 'Please enter a valid email address';
    }

    // Validate company name
    if (!formData.get('companyName')) {
      newErrors.companyName = 'Company name is required';
    }

    // Validate phone number
    if (!formData.get('phone')) {
      newErrors.phone = 'Phone number is required';
    }

    // Validate team size
    if (!formData.get('teamSize')) {
      newErrors.teamSize = 'Please select team size';
    }

    // Validate biggest challenge
    if (!formData.get('biggestChallenge')) {
      newErrors.biggestChallenge = 'Please select your biggest challenge';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const formData = new FormData(e.target as HTMLFormElement);
    
    if (validateForm(formData)) {
      setIsSubmitting(true);
      try {
        const formPayload = new FormData();
        
        // Add regular form fields - using existing field names
        formPayload.append('fullName', formData.get('fullName') as string);
        formPayload.append('email', formData.get('email') as string);
        formPayload.append('phone', formData.get('phone') as string);
        
        
        // Create message_text field with all additional data
        const messageText = `Company Name : ${formData.get('companyName')} | Team Size: ${formData.get('teamSize')} | Biggest Challenge: ${formData.get('biggestChallenge')} | Ads Group: ${adsByGroup || 'N/A'} | Keyword: ${keyword || 'N/A'}`;
        formPayload.append('headache', messageText);

        const basePath = process.env.NEXT_PUBLIC_BASE_PATH || '';
        const response = await fetch(`${basePath}/api/lp-contact`, {
          method: 'POST',
          body: formPayload,
          // Don't set Content-Type header, let the browser set it with the correct boundary
        });
        const data = await response.json();
        
        if (!response.ok) {
          throw new Error(data.error || 'Failed to submit form');
        }

        console.log('Form submitted successfully:', data);
        
        // Reset form on success
        (e.target as HTMLFormElement).reset();
        
        // Redirect to thank you page
        window.location.href = '/thank-you/';
        
      } catch (error) {
        console.error('Error submitting form:', error);
        alert(error instanceof Error ? error.message : 'There was an error submitting the form. Please try again.');
      } finally {
        setIsSubmitting(false);
      }
    }
  };

  return (
    <div className="">
      {isSubmitted ? (
        <div className="text-center py-12">
          <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
            <svg
              className="h-6 w-6 text-green-600"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M5 13l4 4L19 7"
              />
            </svg>
          </div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Thank You!</h2>
          <p className="text-gray-600">
            Your request has been received. We&apos;ll show you how to see your better workday soon.
          </p>
        </div>
      ) : (
        <form onSubmit={handleSubmit} className="max-w-[500px] mx-auto p-4 space-y-6" noValidate>
          <div className="text-center mb-8">
            <h2 className="text-3xl font-bold text-gray-900 mb-2">See Your Better Workday</h2>
          </div>

          <div className='text-left'>
            <label htmlFor="fullName" className="block text-sm font-medium text-gray-900 mb-1">
              Your Name <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="fullName"
              name="fullName"
              required
              placeholder="John Doe"
              className={`w-full px-4 py-2 border ${errors.fullName ? 'border-red-500' : 'border-gray-300'} rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm`}
            />
            {errors.fullName && <p className="mt-1 text-sm text-red-600">{errors.fullName}</p>}
          </div>

          <div className='text-left'>
            <label htmlFor="email" className="block text-sm font-medium text-gray-900 mb-1">
              Work Email <span className="text-red-500">*</span>
            </label>
            <input
              type="email"
              id="email"
              name="email"
              required
              placeholder="john@company.com"
              className={`w-full px-4 py-2 border ${errors.email ? 'border-red-500' : 'border-gray-300'} rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm`}
            />
            {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
          </div>

          <div className='text-left'>
            <label htmlFor="companyName" className="block text-sm font-medium text-gray-900 mb-1">
              Company Name <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="companyName"
              name="companyName"
              required
              placeholder="Acme Corp"
              className={`w-full px-4 py-2 border ${errors.companyName ? 'border-red-500' : 'border-gray-300'} rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm`}
            />
            {errors.companyName && <p className="mt-1 text-sm text-red-600">{errors.companyName}</p>}
          </div>

          <div className='text-left'>
            <label htmlFor="phone" className="block text-sm font-medium text-gray-900 mb-1">
              Phone Number <span className="text-red-500">*</span>
            </label>
            <input
              type="tel"
              id="phone"
              name="phone"
              required
              placeholder="(555) 123-4567"
              className={`w-full px-4 py-2 border ${errors.phone ? 'border-red-500' : 'border-gray-300'} rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm`}
            />
            {errors.phone && <p className="mt-1 text-sm text-red-600">{errors.phone}</p>}
          </div>

          <div className='text-left'>
            <label htmlFor="teamSize" className="block text-sm font-medium text-gray-900 mb-1">
              Team Size <span className="text-red-500">*</span>
            </label>
            <select
              id="teamSize"
              name="teamSize"
              required
              className={`w-full px-4 py-2 border ${errors.teamSize ? 'border-red-500' : 'border-gray-300'} rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm`}
            >
              <option value="">Select team size</option>
              <option value="1-5">1-5</option>
              <option value="6-15">6-15</option>
              <option value="16-30">16-30</option>
              <option value="30+">30+</option>
            </select>
            {errors.teamSize && <p className="mt-1 text-sm text-red-600">{errors.teamSize}</p>}
          </div>

          <div className='text-left'>
            <label htmlFor="biggestChallenge" className="block text-sm font-medium text-gray-900 mb-1">
              Biggest Challenge <span className="text-red-500">*</span>
            </label>
            <select
              id="biggestChallenge"
              name="biggestChallenge"
              required
              className={`w-full px-4 py-2 border ${errors.biggestChallenge ? 'border-red-500' : 'border-gray-300'} rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm`}
            >
              <option value="">Select your biggest challenge</option>
              <option value="Morning scheduling chaos">Morning scheduling chaos</option>
              <option value="Too much windshield time">Too much windshield time</option>
              <option value="Can't find customer history">Can&apos;t find customer history</option>
              <option value="Techs hate our current software">Techs hate our current software</option>
              <option value="Manual everything">Manual everything</option>
              <option value="Other">Other</option>
            </select>
            {errors.biggestChallenge && <p className="mt-1 text-sm text-red-600">{errors.biggestChallenge}</p>}
          </div>

          <div className='text-left'>
          </div>

          <div className="flex justify-center">
            <button
              type="submit"
              disabled={isSubmitting}
              className="px-6 py-2 bg-black text-white font-medium rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black w-full disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isSubmitting ? 'Submitting...' : 'See the difference'}
            </button>
          </div>
        </form>
      )}
    </div>
  );
}

export default function LPForm() {
  return (
    <Suspense fallback={<div className="max-w-[500px] mx-auto p-4">Loading form...</div>}>
      <LPFormContent />
    </Suspense>
  );
}