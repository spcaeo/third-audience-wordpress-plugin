import React from "react";
import Link from "next/link";

export default async function PPCHeaderContainerNavigation() {
//   const menuItems = await getMenu("PRIMARY");
//   const CUSTOMER_LOGIN = await getMenu("CUSTOMER_LOGIN");
  return (
    // <div className="ppc-header-nav-container flex items-center gap-6">
    //   <Link 
    //     href="" 
    //     className="lp-form-trigger gradient-button-ppc header-new-ppc px-5 py-2 rounded-md text-base font-medium text-gray-700 transition-colors"
    //   >
    //     Contact Us
    //   </Link>
    //   <Link 
    //     href="https://app.fieldcamp.ai/signup" 
    //     className="ppc-signup-btn utm-medium-signup bg-black text-white px-5 py-2 rounded-md text-base font-medium transition-colors"
    //   >
    //     Sign Up
    //   </Link>
    // </div>
    <div className="ppc-header-nav-container flex items-center gap-6">
      {/* Phone Number */}
      <a 
        href="tel:+18564602850" 
        className="ppc-phone-number flex items-center text-black hover:text-gray-700 transition-colors font-medium text-base"
      >
        <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" strokeWidth="2">
          <path fillRule="evenodd" d="M1.5 4.5a3 3 0 013-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 01-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 006.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 011.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 01-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5z" clipRule="evenodd" />
        </svg>
        +1 856-460-2850
      </a>
      
      {/* Book Live Demo Button */}
      <a 
        href="https://calendly.com/jeel-fieldcamp/30min" 
        className="calendly-open inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity shadow-lg" style={{ height: "48px" }}
      >
        Book a Demo
      </a>
    </div>
  );
}