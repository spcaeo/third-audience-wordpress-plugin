'use client';

import { useState } from 'react';

type FormErrors = {
  email?: string;
};

export default function DemoForm() {
  const [errors, setErrors] = useState<FormErrors>({});
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const validateForm = (formData: FormData): boolean => {
    const newErrors: FormErrors = {};
    
    // Validate email
    const email = formData.get('email') as string;
    if (!email) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(email)) {
      newErrors.email = 'Please enter a valid email address';
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
        
        // Add form fields
        formPayload.append('email', formData.get('email') as string);
        formPayload.append('formType', 'demo');
        formPayload.append('message', 'Demo request from comparison page');

        const basePath = process.env.NEXT_PUBLIC_BASE_PATH || '';
        const response = await fetch(`${basePath}/api/forms?type=demo`, {
          method: 'POST',
          body: formPayload,
        });
        const data = await response.json();
        
        if (!response.ok) {
          throw new Error(data.error || 'Failed to submit form');
        }

        console.log('Demo form submitted successfully:', data);
        
        // Reset form on success
        (e.target as HTMLFormElement).reset();
        
        // Redirect to thank you page
        window.location.href = '/thank-you/';
        
      } catch (error) {
        console.error('Error submitting demo form:', error);
        alert(error instanceof Error ? error.message : 'There was an error submitting the form. Please try again.');
      } finally {
        setIsSubmitting(false);
      }
    }
  };

  return (
    <div className="flex flex-col sm:flex-row gap-4 w-full md:hidden">
      {isSubmitted ? (
        <div className="text-center py-4">
          <div className="text-green-600 font-semibold">Thank you! We'll be in touch soon.</div>
        </div>
      ) : (
        <form onSubmit={handleSubmit} className="flex flex-col sm:flex-row gap-4 w-full" noValidate>
          <div className="flex-1">
            <input
              type="email"
              name="email"
              required
              placeholder="Your email address"
              className={`block w-full rounded-[10px] px-2 sm:px-8 py-2 sm:py-4 bg-white-100 p-[10px_13px] outline-hidden transition-all duration-300 ease-out text-secondary-foreground placeholder:text-black-700 border border-[#D3D8DF] hover:border-greyscale-light-08 hover:shadow-[0px_1px_4px_rgba(56,_62,_71,_0.1)] focus:ring-[0px] placeholder:max-w-full placeholder:text-base placeholder-shown:truncate ${
                errors.email ? 'border-red-500' : 'border-[#D3D8DF]'
              } hover:border-greyscale-light-08 hover:shadow-[0px_1px_4px_rgba(56,_62,_71,_0.1)] focus:ring-[0px] placeholder:max-w-full placeholder:text-base placeholder-shown:truncate`}
            />
            {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
          </div>
          <button
            type="submit"
            disabled={isSubmitting}
            className="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-2 sm:px-8 py-2 sm:py-4 rounded-xl text-base sm:text-lg font-semibold hover:shadow-xl transition-all duration-300 hover:scale-105 min-w-[220px] disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {isSubmitting ? 'Sending...' : 'Send me a demo'}
          </button>
        </form>
  )}
    </div>
  );
}