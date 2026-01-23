'use client';

import { useState, useRef, useCallback } from 'react';

type FormErrors = {
  fullName?: string;
  email?: string;
  companySize?: string;
  file?: string;
  url?: string;
  headache?: string;
};

export default function DataDropForm() {
  const [activeTab, setActiveTab] = useState<'upload' | 'url'>('upload');
  const [file, setFile] = useState<File | null>(null);
  const [url, setUrl] = useState('');
  const [isDragging, setIsDragging] = useState(false);
  const [errors, setErrors] = useState<FormErrors>({});
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isSubmitted, setIsSubmitted] = useState(false);


  const validateForm = (formData: FormData): boolean => {
    const newErrors: FormErrors = {};
    
    // Validate full name
    if (!formData.get('fullName')) {
      newErrors.fullName = 'Full name is required';
    }

    // Validate email
    const email = formData.get('email') as string;
    if (!email) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(email)) {
      newErrors.email = 'Please enter a valid email address';
    }

    // Validate company size
    if (!formData.get('companySize')) {
      newErrors.companySize = 'Please select company size';
    }

    // Validate file or URL based on active tab
    if (activeTab === 'upload' && !file) {
      newErrors.file = 'Please upload a file';
    } else if (activeTab === 'url' && !url) {
      newErrors.url = 'Please enter a valid URL';
    } else if (activeTab === 'url' && url && !/^https?:\/\//.test(url)) {
      newErrors.url = 'Please enter a valid URL starting with http:// or https://';
    }

    // Validate headache text
    if (!formData.get('headache')) {
      newErrors.headache = 'This field is required';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleDragOver = useCallback((e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === 'dragenter' || e.type === 'dragover') {
      setIsDragging(true);
    } else if (e.type === 'dragleave') {
      setIsDragging(false);
    }
  }, []);

  const handleDrop = useCallback((e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
    
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      setFile(e.dataTransfer.files[0]);
      setErrors(prev => ({ ...prev, file: undefined }));
    }
  }, []);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setFile(e.target.files[0]);
      setErrors(prev => ({ ...prev, file: undefined }));
    }
  };

  const handleRemoveFile = (e: React.MouseEvent) => {
    e.stopPropagation();
    setFile(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const formData = new FormData(e.target as HTMLFormElement);
    
    if (validateForm(formData)) {
      try {
        const formPayload = new FormData();
        
        // Add regular form fields
        formPayload.append('fullName', formData.get('fullName') as string);
        formPayload.append('email', formData.get('email') as string);
        formPayload.append('phone', formData.get('phone') as string || '');
        formPayload.append('companySize', formData.get('companySize') as string);
        formPayload.append('headache', formData.get('headache') as string);
        
        // Add file or URL based on active tab
        if (activeTab === 'upload' && file) {
          formPayload.append('file', file);
        } else if (activeTab === 'url') {
          formPayload.append('url', url);
        }

        const response = await fetch('/api/contact', {
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
        setFile(null);
        setUrl('');
        
        // Show success message to user
        setIsSubmitted(true);
        
      } catch (error) {
        console.error('Error submitting form:', error);
        alert(error instanceof Error ? error.message : 'There was an error submitting the form. Please try again.');
      }
    }
  };

  return (
    <div className=" ">
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
          Your submission has been received. We&apos;ll get back to you soon.
        </p>
      </div>
    ) : (
    <form onSubmit={handleSubmit} className="max-w-[500px] mx-auto p-4 space-y-6" noValidate>
      <div>
        <label htmlFor="fullName" className="block text-sm font-medium text-gray-900 mb-1">
          Full Name <span className="text-red-500">*</span>
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

      <div>
        <label htmlFor="email" className="block text-sm font-medium text-gray-900 mb-1">
          Email Address <span className="text-red-500">*</span>
        </label>
        <input
          type="email"
          id="email"
          name="email"
          required
          placeholder="john@example.com"
          className={`w-full px-4 py-2 border ${errors.email ? 'border-red-500' : 'border-gray-300'} rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm`}
        />
        {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
      </div>

      <div>
        <label htmlFor="phone" className="block text-sm font-medium text-gray-900 mb-1">
          Phone Number
        </label>
        <input
          type="tel"
          id="phone"
          name="phone"
          placeholder="(555) 123-4567"
          className="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        />
      </div>

      <div>
        <label htmlFor="companySize" className="block text-sm font-medium text-gray-900 mb-1">
          Company Size <span className="text-red-500">*</span>
        </label>
        <select
          id="companySize"
          name="companySize"
          required
          className={`w-full px-4 py-2 border ${errors.companySize ? 'border-red-500' : 'border-gray-300'} rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm`}
        >
          <option value="">Select company size</option>
          <option value="1-10">1-10 employees</option>
          <option value="11-50">11-50 employees</option>
          <option value="51-200">51-200 employees</option>
          <option value="201-500">201-500 employees</option>
          <option value="501-1000">501-1000 employees</option>
          <option value="1000+">1000+ employees</option>
        </select>
        {errors.companySize && <p className="mt-1 text-sm text-red-600">{errors.companySize}</p>}
      </div>

      <div>
        <p className="block text-sm font-medium text-gray-900 p-0">Customer Data</p>
        <div className="grid grid-cols-2 border border-gray-300 rounded-lg overflow-hidden text-sm font-medium">
          <button
            type="button"
            className={`px-4 py-2 ${
              activeTab === 'upload'
                ? 'bg-white text-black '
                : 'bg-gray-100 text-gray-500 hover:text-black'
            }`}
            onClick={() => setActiveTab('upload')}
          >
            Upload File
          </button>
          <button
            type="button"
            className={`px-4 py-2 ${
              activeTab === 'url'
                ? 'bg-white text-black '
                : 'bg-gray-100 text-gray-500 hover:text-black'
            }`}
            onClick={() => setActiveTab('url')}
          >
            Website URL
          </button>
        </div>

        {activeTab === 'upload' ? (
          <div 
          className={`mt-2 border-2 border-dashed rounded-lg p-6 text-center transition-colors duration-200 ${isDragging ? 'border-blue-500 bg-blue-50' : errors.file ? 'border-red-500' : 'border-gray-300'}`}
          onDragEnter={handleDragOver}
          onDragLeave={handleDragOver}
          onDragOver={handleDragOver}
          onDrop={handleDrop}
          onClick={() => !file && fileInputRef.current?.click()}
        >
          <input
            type="file"
            ref={fileInputRef}
            className="hidden"
            onChange={handleFileChange}
          />
          <div className="space-y-3">
            <svg className="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <div className="text-sm text-gray-600">
              {file ? (
                <div className="flex items-center justify-center gap-2">
                  <span>{file.name}</span>
                  <button
                    type="button"
                    onClick={handleRemoveFile}
                    className="text-red-500 hover:text-red-700 focus:outline-none"
                    title="Remove file"
                  >
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              ) : (
                <>
                  <span className="font-medium text-blue-600 hover:text-blue-500 cursor-pointer">Click to upload</span> or drag and drop<br />
                  <span className="text-xs text-gray-500 block">Supports CSV, Excel (.xlsx) files up to 10MB</span>
                </>
              )}
            </div>
          </div>
      
          <div className="mt-4 text-[12px] text-gray-500 italic">
            Please remove sensitive personal data (SSNs, card numbers, medical info).<br />
            Data auto-deleted after 14 days unless you subscribe.
          </div>
      
          {/* <div className="mt-2">
            <a href="/path/to/sample-template.xlsx" download className="text-xs text-blue-600 hover:underline">
              Download sample template
            </a>
          </div> */}
        </div>
        ) : (
          <div className="mt-2">
            <input
              type="url"
              name="url"
              value={url}
              onChange={(e) => setUrl(e.target.value)}
              placeholder="https://example.com"
              className={`w-full px-4 py-2 border ${errors.url ? 'border-red-500' : 'border-gray-300'} rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm`}
            />
            {errors.url && <p className="mt-1 text-sm text-red-600">{errors.url}</p>}
          </div>
        )}
        {errors.file && <p className="mt-1 text-sm text-red-600">{errors.file}</p>}
      </div>

      <div>
        <label htmlFor="headache" className="block text-sm font-medium text-gray-900 mb-1">
          What&apos;s your biggest headache today? <span className="text-red-500">*</span>
        </label>
        <textarea
          id="headache"
          name="headache"
          rows={4}
          required
          placeholder="Tell us what challenges you're facing"
          className={`w-full px-4 py-2 border ${errors.headache ? 'border-red-500' : 'border-gray-300'} rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm`}
        ></textarea>
        {errors.headache && <p className="mt-1 text-sm text-red-600">{errors.headache}</p>}
      </div>

      <div className="flex justify-center">
        <button
          type="submit"
          className="px-6 py-2 bg-black text-white font-medium rounded-md hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black w-full "
        >
          Submit Request
        </button>
      </div>
    </form>
     )}
     </div>
   );
}