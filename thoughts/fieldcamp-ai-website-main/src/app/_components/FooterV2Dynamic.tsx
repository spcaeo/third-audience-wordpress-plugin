import React from "react";
import Image from "next/image";
import Link from "next/link";
import { getMenu } from "@/lib/api";

export default async function FooterV2Dynamic() {
  // Fetch footer menu items from WordPress - same as footer.tsx
  const footerMenuItems = await getMenu("FOOTER");

  return (
    <>
      {/* Top features bar - Keep this static as per original design */}
      <div className="footer-features-section bg-gray-50 border-t border-b border-gray-200">
        <div className="footer-features-container max-w-[1246px] mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-6 py-3 sm:py-4">
          <div className="footer-features-grid flex flex-wrap justify-center lg:justify-between gap-2 sm:gap-4 md:gap-6 lg:gap-8 xl:gap-12 text-center">
            <div className="footer-feature-item flex items-center gap-1 sm:gap-2 pr-4 sm:pr-8 md:pr-10 lg:pr-12 xl:pr-14 relative">
              <svg className="footer-feature-icon w-3 h-3 sm:w-4 sm:h-4 text-gray-600 flex-shrink-0" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.11646 6V3H6.11646" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M13.6165 6H4.61646C3.78803 6 3.11646 6.67157 3.11646 7.5V13.5C3.11646 14.3284 3.78803 15 4.61646 15H13.6165C14.4449 15 15.1165 14.3284 15.1165 13.5V7.5C15.1165 6.67157 14.4449 6 13.6165 6Z" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M1.61646 10.5H3.11646" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M15.1165 10.5H16.6165" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M11.3665 9.75V11.25" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M6.86646 9.75V11.25" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
              <span className="footer-feature-text text-xs sm:text-sm text-gray-700 whitespace-nowrap">AI-Powered Field Service</span>
              <div className="absolute right-0 top-1/2 transform -translate-y-1/2 h-4 sm:h-6 w-px bg-gray-300 hidden lg:block"></div>
            </div>
            <div className="footer-feature-item flex items-center gap-1 sm:gap-2 pr-4 sm:pr-8 md:pr-10 lg:pr-12 xl:pr-14 relative">
              <svg className="footer-feature-icon w-3 h-3 sm:w-4 sm:h-4 text-gray-600 flex-shrink-0" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.61496 15.3637C6.29924 14.8982 5.16017 14.0363 4.35455 12.8967C3.54894 11.757 3.11639 10.3956 3.11646 9C3.11646 8.11358 3.29105 7.23583 3.63027 6.41689C3.96949 5.59794 4.46669 4.85382 5.09348 4.22703C5.72028 3.60023 6.46439 3.10303 7.28334 2.76381C8.10229 2.42459 8.98003 2.25 9.86646 2.25C10.7529 2.25 11.6306 2.42459 12.4496 2.76381C13.2685 3.10303 14.0126 3.60023 14.6394 4.22703C15.2662 4.85382 15.7634 5.59794 16.1026 6.41689C16.4419 7.23583 16.6165 8.11358 16.6165 9" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M9.86646 5.25V9" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M9.86646 11.25H11.3665C11.5654 11.25 11.7561 11.329 11.8968 11.4697C12.0374 11.6103 12.1165 11.8011 12.1165 12V12.75C12.1165 12.9489 12.0374 13.1397 11.8968 13.2803C11.7561 13.421 11.5654 13.5 11.3665 13.5H10.6165C10.4175 13.5 10.2268 13.579 10.0861 13.7197C9.94547 13.8603 9.86646 14.0511 9.86646 14.25V15C9.86646 15.1989 9.94547 15.3897 10.0861 15.5303C10.2268 15.671 10.4175 15.75 10.6165 15.75H12.1165" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M14.3665 11.25V12.75C14.3665 12.9489 14.4455 13.1397 14.5861 13.2803C14.7268 13.421 14.9175 13.5 15.1165 13.5H15.8665" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M16.6165 11.25V15.75" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
              <span className="footer-feature-text text-xs sm:text-sm text-gray-700 whitespace-nowrap">24/7 support</span>
              <div className="absolute right-0 top-1/2 transform -translate-y-1/2 h-4 sm:h-6 w-px bg-gray-300 hidden lg:block"></div>
            </div>
            <div className="footer-feature-item flex items-center gap-1 sm:gap-2 pr-4 sm:pr-8 md:pr-10 lg:pr-12 xl:pr-14 relative">
              <svg className="footer-feature-icon w-3 h-3 sm:w-4 sm:h-4 text-gray-600 flex-shrink-0" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clipPath="url(#clip0_6202_9863)">
                  <mask id="mask0_6202_9863" maskUnits="userSpaceOnUse" x="0" y="0" width="19" height="18">
                    <path d="M18.6165 0H0.616455V18H18.6165V0Z" fill="white" />
                  </mask>
                  <g mask="url(#mask0_6202_9863)">
                    <path d="M5.18403 9.96068H7.50153V15.3607C7.50153 16.6207 8.18403 16.8757 9.01653 15.9307L14.694 9.48068C15.3915 8.69318 15.099 8.04068 14.0415 8.04068H11.724V2.64066C11.724 1.38066 11.0415 1.12566 10.209 2.07066L4.53153 8.52068C3.84153 9.31568 4.13403 9.96068 5.18403 9.96068Z" stroke="black" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
                  </g>
                </g>
                <defs>
                  <clipPath id="clip0_6202_9863">
                    <rect width="18" height="18" fill="white" transform="translate(0.616455)" />
                  </clipPath>
                </defs>
              </svg>
              <span className="footer-feature-text text-xs sm:text-sm text-gray-700 whitespace-nowrap">Weekly updates</span>
              <div className="absolute right-0 top-1/2 transform -translate-y-1/2 h-4 sm:h-6 w-px bg-gray-300 hidden lg:block"></div>
            </div>
            <div className="footer-feature-item flex items-center gap-1 sm:gap-2 pr-4 sm:pr-8 md:pr-10 lg:pr-12 xl:pr-14 relative">
              <svg className="footer-feature-icon w-3 h-3 sm:w-4 sm:h-4 text-gray-600 flex-shrink-0" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clipPath="url(#clip0_6202_9872)">
                  <mask id="mask0_6202_9872" maskUnits="userSpaceOnUse" x="0" y="0" width="19" height="18">
                    <path d="M18.3662 0H0.366211V18H18.3662V0Z" fill="white" />
                  </mask>
                  <g mask="url(#mask0_6202_9872)">
                    <path d="M4.86621 7.5V6C4.86621 3.5175 5.61621 1.5 9.36621 1.5C13.1162 1.5 13.8662 3.5175 13.8662 6V7.5" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                    <path d="M9.36621 13.875C10.4017 13.875 11.2412 13.0355 11.2412 12C11.2412 10.9645 10.4017 10.125 9.36621 10.125C8.33069 10.125 7.49121 10.9645 7.49121 12C7.49121 13.0355 8.33069 13.875 9.36621 13.875Z" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                    <path d="M13.1162 16.5H5.61621C2.61621 16.5 1.86621 15.75 1.86621 12.75V11.25C1.86621 8.25 2.61621 7.5 5.61621 7.5H13.1162C16.1162 7.5 16.8662 8.25 16.8662 11.25V12.75C16.8662 15.75 16.1162 16.5 13.1162 16.5Z" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                  </g>
                </g>
                <defs>
                  <clipPath id="clip0_6202_9872">
                    <rect width="18" height="18" fill="white" transform="translate(0.366211)" />
                  </clipPath>
                </defs>
              </svg>
              <span className="footer-feature-text text-xs sm:text-sm text-gray-700 whitespace-nowrap">Secure and compliant</span>
              <div className="absolute right-0 top-1/2 transform -translate-y-1/2 h-4 sm:h-6 w-px bg-gray-300 hidden lg:block"></div>
            </div>
            <div className="footer-feature-item flex items-center gap-1 sm:gap-2">
              <svg className="footer-feature-icon w-3 h-3 sm:w-4 sm:h-4 text-gray-600 flex-shrink-0" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.11621 10.5L12.1162 7.5" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M2.62121 14.2503C1.96287 13.1102 1.61626 11.8168 1.61621 10.5003C1.61616 9.18373 1.96268 7.89035 2.62093 6.75016C3.27917 5.60998 4.22596 4.66315 5.36613 4.00486C6.50629 3.34656 7.79965 3 9.11621 3C10.4328 3 11.7261 3.34656 12.8663 4.00486C14.0065 4.66315 14.9532 5.60998 15.6115 6.75016C16.2697 7.89035 16.6163 9.18373 16.6162 10.5003C16.6162 11.8168 16.2695 13.1102 15.6112 14.2503" stroke="black" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
              <span className="footer-feature-text text-xs sm:text-sm text-gray-700 whitespace-nowrap">99.9% uptime</span>
            </div>
          </div>
        </div>
      </div>

      {/* Main footer with dynamic content from WordPress */}
      <footer className="footer-main-section bg-white">
        <div className="footer-main-container max-w-[1246px] mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-6 py-6 sm:py-8 md:py-10 lg:py-12 xl:py-16">
          <div className="footer-columns-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-6 md:gap-8 lg:gap-6 xl:gap-8">
            
            {/* Dynamic columns from WordPress menu - using same pattern as footer.tsx */}
            {footerMenuItems?.menuItems?.nodes && footerMenuItems.menuItems.nodes.length > 0 ? (
              footerMenuItems.menuItems.nodes.map((item: any, index: number) => 
                item.parentId == null && (
                  <div 
                    key={item.id} 
                    className={`footer-column footer-column-${item.label.toLowerCase().replace(/\s+/g, '-')} space-y-2 sm:space-y-3 lg:space-y-4 ${
                      index === 5 ? 'lg:col-span-1' : ''
                    }`}
                  >
                    <h3 className="footer-column-title text-[14px] font-medium text-[#000000]">
                      {item.label}
                    </h3>
                    {item.childItems?.nodes && item.childItems.nodes.length > 0 && (
                      <div className="footer-column-links space-y-1 sm:space-y-2 lg:space-y-3 text-sm">
                        {item.childItems.nodes.map((subItem: any) => (
                          <Link 
                            key={subItem.id}
                            href={subItem.url || '#'} 
                            className="footer-link block text-gray-600 hover:text-gray-900"
                          >
                            {subItem.label}
                          </Link>
                        ))}
                      </div>
                    )}
                    
                    {/* Add Connect section for the last column (Learn) */}
                    {item.label === 'Learn' && (
                      <div className="footer-connect-section space-y-2 sm:space-y-3 lg:space-y-3 mt-4 sm:mt-5 lg:mt-6">
                        <h3 className="footer-column-title text-[14px] font-medium text-[#000000]">Connect</h3>
                        <div className="footer-social-links flex space-x-2 sm:space-x-3 lg:space-x-3">
                          <Link href="https://www.linkedin.com/company/fieldcamp/" className="footer-social-link text-gray-400 hover:text-gray-500">
                            <svg className="footer-social-icon w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g clipPath="url(#clip0_6202_9937)">
                                <path d="M17.1162 2C18.4423 2 19.7141 2.52678 20.6517 3.46447C21.5894 4.40215 22.1162 5.67392 22.1162 7V17C22.1162 18.3261 21.5894 19.5979 20.6517 20.5355C19.7141 21.4732 18.4423 22 17.1162 22H7.11621C5.79013 22 4.51836 21.4732 3.58068 20.5355C2.643 19.5979 2.11621 18.3261 2.11621 17V7C2.11621 5.67392 2.643 4.40215 3.58068 3.46447C4.51836 2.52678 5.79013 2 7.11621 2H17.1162ZM8.11621 10C7.85099 10 7.59664 10.1054 7.4091 10.2929C7.22157 10.4804 7.11621 10.7348 7.11621 11V16C7.11621 16.2652 7.22157 16.5196 7.4091 16.7071C7.59664 16.8946 7.85099 17 8.11621 17C8.38143 17 8.63578 16.8946 8.82332 16.7071C9.01085 16.5196 9.11621 16.2652 9.11621 16V11C9.11621 10.7348 9.01085 10.4804 8.82332 10.2929C8.63578 10.1054 8.38143 10 8.11621 10ZM14.1162 10C13.715 9.99978 13.3178 10.08 12.9482 10.236L12.8232 10.293C12.6834 10.1532 12.5052 10.058 12.3112 10.0194C12.1173 9.98085 11.9163 10.0007 11.7336 10.0763C11.5509 10.152 11.3947 10.2801 11.2848 10.4445C11.1749 10.609 11.1163 10.8022 11.1162 11V16C11.1162 16.2652 11.2216 16.5196 11.4091 16.7071C11.5966 16.8946 11.851 17 12.1162 17C12.3814 17 12.6358 16.8946 12.8233 16.7071C13.0109 16.5196 13.1162 16.2652 13.1162 16V13C13.1162 12.7348 13.2216 12.4804 13.4091 12.2929C13.5966 12.1054 13.851 12 14.1162 12C14.3814 12 14.6358 12.1054 14.8233 12.2929C15.0109 12.4804 15.1162 12.7348 15.1162 13V16C15.1162 16.2652 15.2216 16.5196 15.4091 16.7071C15.5966 16.8946 15.851 17 16.1162 17C16.3814 17 16.6358 16.8946 16.8233 16.7071C17.0109 16.5196 17.1162 16.2652 17.1162 16V13C17.1162 12.2044 16.8001 11.4413 16.2375 10.8787C15.6749 10.3161 14.9119 10 14.1162 10ZM8.11621 7C7.87128 7.00003 7.63487 7.08996 7.45184 7.25272C7.2688 7.41547 7.15187 7.63975 7.12321 7.883L7.11621 8.01C7.11649 8.26488 7.21409 8.51003 7.38906 8.69537C7.56403 8.8807 7.80316 8.99223 8.05761 9.00717C8.31205 9.02211 8.56259 8.93933 8.75804 8.77573C8.9535 8.61214 9.07911 8.3801 9.10921 8.127L9.11621 8C9.11621 7.73478 9.01085 7.48043 8.82332 7.29289C8.63578 7.10536 8.38143 7 8.11621 7Z" fill="#232529" />
                              </g>
                              <defs>
                                <clipPath id="clip0_6202_9937">
                                  <rect width="24" height="24" fill="white" transform="translate(0.116211)" />
                                </clipPath>
                              </defs>
                            </svg>
                          </Link>
                          <Link href="https://www.facebook.com/getfieldcamp" className="footer-social-link text-gray-400 hover:text-gray-500">
                            <svg className="footer-social-icon w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g clipPath="url(#clip0_6202_9940)">
                                <path d="M16.5605 2C18.034 2 19.4474 2.58508 20.4893 3.62695C21.5311 4.66882 22.1162 6.08224 22.1162 7.55566V16.4443C22.1162 17.9178 21.5311 19.3312 20.4893 20.373C19.4474 21.4149 18.034 22 16.5605 22H7.67188C6.19845 22 4.78503 21.4149 3.74316 20.373C2.7013 19.3312 2.11621 17.9178 2.11621 16.4443V7.55566C2.11621 6.08224 2.7013 4.66882 3.74316 3.62695C4.78503 2.58508 6.19845 2 7.67188 2H16.5605ZM13.5889 6.69922C12.808 6.69922 12.0591 7.00946 11.5068 7.56152C10.9546 8.1138 10.6436 8.8635 10.6436 9.64453V10.6221C10.6435 10.7324 10.5546 10.822 10.4443 10.8223H9.07715C8.96669 10.8223 8.87695 10.912 8.87695 11.0225V12.9775C8.87695 13.088 8.96669 13.1777 9.07715 13.1777H10.4443C10.5546 13.178 10.6435 13.2676 10.6436 13.3779V17.1006C10.6436 17.211 10.7334 17.3007 10.8438 17.3008H12.7998C12.9103 17.3008 13 17.211 13 17.1006V13.3779C13 13.2675 13.0897 13.1777 13.2002 13.1777H14.6104C14.7021 13.1777 14.7824 13.1154 14.8047 13.0264L15.2939 11.0703C15.3253 10.9442 15.2296 10.8223 15.0996 10.8223H13.2002C13.0897 10.8223 13 10.7325 13 10.6221V9.64453C13 9.4884 13.0615 9.33798 13.1719 9.22754C13.2823 9.11708 13.4327 9.05469 13.5889 9.05469H15.1553C15.2656 9.05469 15.3553 8.96577 15.3555 8.85547V6.89941C15.3555 6.78896 15.2657 6.69922 15.1553 6.69922H13.5889Z" fill="#232529" />
                              </g>
                              <defs>
                                <clipPath id="clip0_6202_9940">
                                  <rect width="24" height="24" fill="white" transform="translate(0.116211)" />
                                </clipPath>
                              </defs>
                            </svg>
                          </Link>
                          <Link href="https://www.instagram.com/fieldcamp.ai/" className="footer-social-link text-gray-400 hover:text-gray-500">
                            <svg className="footer-social-icon w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g clipPath="url(#clip0_6202_9947)">
                                <path d="M16.5607 2C18.0341 2 19.4472 2.58532 20.489 3.62718C21.5309 4.66905 22.1162 6.08213 22.1162 7.55556V16.4444C22.1162 17.9179 21.5309 19.3309 20.489 20.3728C19.4472 21.4147 18.0341 22 16.5607 22H7.67177C6.19834 22 4.78526 21.4147 3.7434 20.3728C2.70153 19.3309 2.11621 17.9179 2.11621 16.4444V7.55556C2.11621 6.08213 2.70153 4.66905 3.7434 3.62718C4.78526 2.58532 6.19834 2 7.67177 2H16.5607ZM12.1162 7.55556C10.9759 7.55555 9.87929 7.99382 9.05309 8.77969C8.22688 9.56557 7.73434 10.6389 7.67732 11.7778L7.67177 12C7.67177 12.879 7.93243 13.7383 8.42079 14.4692C8.90915 15.2001 9.60328 15.7697 10.4154 16.1061C11.2275 16.4425 12.1211 16.5305 12.9833 16.359C13.8454 16.1876 14.6373 15.7643 15.2589 15.1427C15.8805 14.5211 16.3038 13.7292 16.4753 12.8671C16.6467 12.0049 16.5587 11.1113 16.2223 10.2992C15.886 9.48707 15.3163 8.79294 14.5854 8.30458C13.8545 7.81622 12.9952 7.55556 12.1162 7.55556ZM17.1162 5.88889C16.8441 5.88893 16.5814 5.98884 16.378 6.16968C16.1746 6.35053 16.0447 6.59972 16.0129 6.87L16.0051 7.01111C16.0054 7.29431 16.1139 7.5667 16.3083 7.77263C16.5027 7.97856 16.7684 8.10248 17.0511 8.11908C17.3338 8.13568 17.6122 8.04369 17.8294 7.86193C18.0465 7.68016 18.1861 7.42233 18.2195 7.14111L18.2273 7C18.2273 6.70532 18.1103 6.4227 17.9019 6.21433C17.6935 6.00595 17.4109 5.88889 17.1162 5.88889Z" fill="#232529" />
                                <circle cx="12.1472" cy="12.0315" r="4.26196" stroke="#232529" strokeWidth="1.5" />
                              </g>
                              <defs>
                                <clipPath id="clip0_6202_9947">
                                  <rect width="24" height="24" fill="white" transform="translate(0.116211)" />
                                </clipPath>
                              </defs>
                            </svg>
                          </Link>
                          <Link href="https://www.youtube.com/@fieldcamp_ai" className="footer-social-link text-gray-400 hover:text-gray-500">
                            <svg className="footer-social-icon w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g clipPath="url(#clip0_6202_9951)">
                                <path d="M16.5605 2C18.034 2 19.4474 2.58508 20.4893 3.62695C21.5311 4.66882 22.1162 6.08224 22.1162 7.55566V16.4443C22.1162 17.9178 21.5311 19.3312 20.4893 20.373C19.4474 21.4149 18.034 22 16.5605 22H7.67188C6.19845 22 4.78503 21.4149 3.74316 20.373C2.7013 19.3312 2.11621 17.9178 2.11621 16.4443V7.55566C2.11621 6.08224 2.7013 4.66882 3.74316 3.62695C4.78503 2.58508 6.19845 2 7.67188 2H16.5605ZM9.94238 7.70215C9.8575 7.7004 9.77333 7.72126 9.69922 7.7627C9.62515 7.80415 9.56345 7.86525 9.52051 7.93848C9.47768 8.01165 9.45507 8.09489 9.45508 8.17969V15.8193C9.45505 15.9043 9.47753 15.9882 9.52051 16.0615C9.56341 16.1347 9.62525 16.1949 9.69922 16.2363C9.77333 16.2778 9.85749 16.2986 9.94238 16.2969C10.0273 16.2951 10.1103 16.271 10.1826 16.2266L16.3906 12.4062C16.4599 12.3635 16.5169 12.3035 16.5566 12.2324C16.5964 12.1613 16.6181 12.0814 16.6182 12C16.6182 11.9184 16.5965 11.8378 16.5566 11.7666C16.5169 11.6955 16.46 11.6354 16.3906 11.5928L10.1826 7.77344C10.1103 7.72892 10.0273 7.70392 9.94238 7.70215Z" fill="#232529" />
                              </g>
                              <defs>
                                <clipPath id="clip0_6202_9951">
                                  <rect width="24" height="24" fill="white" transform="translate(0.116211)" />
                                </clipPath>
                              </defs>
                            </svg>
                          </Link>
                          <Link href="https://x.com/FieldCamp_ai" className="footer-social-link text-gray-400 hover:text-gray-500">
                            <svg className="footer-social-icon w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g clipPath="url(#clip0_6202_9956)">
                                <path d="M16.5605 2C18.034 2 19.4474 2.58508 20.4893 3.62695C21.5311 4.66882 22.1162 6.08224 22.1162 7.55566V16.4443C22.1162 17.9178 21.5311 19.3312 20.4893 20.373C19.4474 21.4149 18.034 22 16.5605 22H7.67188C6.19845 22 4.78503 21.4149 3.74316 20.373C2.7013 19.3312 2.11621 17.9178 2.11621 16.4443V7.55566C2.11621 6.08224 2.7013 4.66882 3.74316 3.62695C4.78503 2.58508 6.19845 2 7.67188 2H16.5605ZM8.15137 7.24219C8.08049 7.24223 8.01122 7.26179 7.9502 7.29785C7.88901 7.33405 7.83791 7.38592 7.80371 7.44824C7.7697 7.51038 7.75262 7.58057 7.75488 7.65137C7.75721 7.72238 7.77921 7.79164 7.81738 7.85156L10.9199 12.7246L7.8584 16.0947C7.78924 16.1727 7.75322 16.2748 7.75879 16.3789C7.76439 16.4831 7.81146 16.5812 7.88867 16.6514C7.96584 16.7214 8.06777 16.7583 8.17188 16.7539C8.27599 16.7495 8.3743 16.7042 8.44531 16.6279L11.3613 13.4199L13.3682 16.5742C13.4039 16.6304 13.4534 16.6769 13.5117 16.709C13.57 16.741 13.6356 16.7577 13.7021 16.7578H16.0811C16.152 16.7577 16.2222 16.7383 16.2832 16.7021C16.3441 16.666 16.3946 16.6139 16.4287 16.5518C16.4628 16.4897 16.4797 16.4194 16.4775 16.3486C16.4754 16.2777 16.454 16.2084 16.416 16.1484L13.3135 11.2725L16.375 7.90527C16.4441 7.82729 16.4792 7.72515 16.4736 7.62109C16.468 7.51691 16.4219 7.4188 16.3447 7.34863C16.2675 7.27854 16.1657 7.24174 16.0615 7.24609C15.9573 7.25048 15.8591 7.29575 15.7881 7.37207L12.8721 10.5791L10.8652 7.42578C10.8295 7.36955 10.7801 7.32312 10.7217 7.29102C10.6632 7.25887 10.597 7.24221 10.5303 7.24219H8.15137Z" fill="#232529" />
                              </g>
                              <defs>
                                <clipPath id="clip0_6202_9956">
                                  <rect width="24" height="24" fill="white" transform="translate(0.116211)" />
                                </clipPath>
                              </defs>
                            </svg>
                          </Link>
                        </div>
                      </div>
                    )}
                  </div>
                )
              )
            ) : (
              /* Fallback static content if no menu data */
              <>
                <div className="footer-column footer-column-fieldcamp space-y-2 sm:space-y-3 lg:space-y-4">
                  <h3 className="footer-column-title text-[14px] font-medium text-[#000000]">FieldCamp</h3>
                  <div className="footer-column-links space-y-1 sm:space-y-2 lg:space-y-3 text-sm">
                    <Link href="/download" className="footer-link block text-gray-600 hover:text-gray-900">Download Apps</Link>
                    <Link href="/about" className="footer-link block text-gray-600 hover:text-gray-900">About Us</Link>
                    <Link href="/reviews" className="footer-link block text-gray-600 hover:text-gray-900">Reviews</Link>
                    <Link href="/updates" className="footer-link block text-gray-600 hover:text-gray-900">Product Updates</Link>
                    <Link href="/how-it-works" className="footer-link block text-gray-600 hover:text-gray-900">How it Works</Link>
                    <Link href="/discord" className="footer-link block text-gray-600 hover:text-gray-900">Discord</Link>
                  </div>
                </div>
                {/* Add other fallback columns as needed */}
              </>
            )}
          </div>

          {/* App Store Badges & Bottom Section - Keep this static */}
          <div className="footer-app-badges border-t border-gray-200 mt-8 sm:mt-12 lg:mt-16 pt-4 sm:pt-6">
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-6">
              <div className="flex flex-col sm:flex-row gap-3 sm:gap-4 items-start">
                <Link href="https://apps.apple.com/us/app/fieldcamp/id6737540187" target="_blank" className="footer-app-store-link">
                  <Image src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/ios-app-store.png" alt="Download on App Store" width={120} height={36} className="footer-app-store-badge sm:w-[130px] sm:h-[39px] lg:w-[140px] lg:h-[42px]" />
                </Link>
                <Link href="https://play.google.com/store/apps/details?id=com.fieldcampai.app" target="_blank" className="footer-google-play-link">
                  <Image src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/google-play-store.png" alt="Get it on Google Play" width={120} height={36} className="footer-google-play-badge sm:w-[130px] sm:h-[39px] lg:w-[140px] lg:h-[42px]" />
                </Link>
              </div>
              <div className="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 lg:gap-8 text-sm text-gray-600">
                <div className="footer-copyright">© {new Date().getFullYear()} FieldCamp</div>
                <div className="footer-bottom-links flex gap-4 sm:gap-6">
                  <Link href="https://fieldcamp.ai/privacy-policy/" className="footer-bottom-link hover:text-gray-900">Privacy</Link>
                  <Link href="https://fieldcamp.ai/terms-of-use/" className="footer-bottom-link hover:text-gray-900">Terms</Link>
                  <Link href="https://fieldcamp.ai/sitemap/" className="footer-bottom-link hover:text-gray-900">Sitemap</Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </footer>
    </>
  );
}