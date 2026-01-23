import React from 'react';
import { ArrowUpRight } from 'lucide-react';
import { AppendUTMToAnchor } from './Custom';

const FieldServiceCTA = () => {
  return (
    <div className="h-fit sidebar-cta-wraper min-w-[250px] bg-purple-100 p-6 rounded-3xl w-96 flex flex-col justify-between transform transition-all hover:scale-[1.02] sticky top-[90px] mb-8">
      <div className="space-y-6">
        <p className="overwrite text-2xl font-bold text-gray-800 leading-tight">
          Still stuck with 
          one-size-fits-all
          field service 
          software?
        </p>
        
        <div className="h-px mx-auto xl:mx-0 w-16 bg-purple-300 my-8"></div>
        
        <p className="text-gray-600 text-xl font-medium">
          AI-powered field service
          software that adapts
          to you.
        </p>
      </div>
      <AppendUTMToAnchor/>
      <a href="https://calendly.com/jeel-fieldcamp/30min" target="_blank" rel="noopener noreferrer" className="no-underline utm-medium-signup calendly-open" data-medium="sidebar-cta">
        <button className="cta-button group flex items-center space-x-2 bg-gray-900 px-5 py-3 rounded-full text-white font-medium text-sm transition-all hover:bg-gray-800 w-fit justify-center mt-5">
          <span>Explore Fieldcamp</span>
          <ArrowUpRight className="w-4 h-4 transition-transform group-hover:translate-x-1 group-hover:-translate-y-1" />
        </button>
      </a>
    </div>
  );
};

export default FieldServiceCTA;