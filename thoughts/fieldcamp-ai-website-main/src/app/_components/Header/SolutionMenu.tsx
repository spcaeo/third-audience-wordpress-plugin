"use client";
import React, { useState } from "react";
import Link from "next/link";

export const SolutionMenu = ({ onLinkClick, isOpen }: { onLinkClick: () => void, isOpen: boolean }) => {
  const [activeTab, setActiveTab] = useState<'team-size' | 'use-case' | 'industry'>('team-size');

  return (
    <div className="pt-0 xl:pt-3 first-child-menu SolutionMenuWrapper">
      <style jsx>{`
        .sf-pro-font {
          font-family: SFPRO, sans-serif;
        }
        .SolutionSubMenu {
          width: 100%;
        }
        @media (min-width: 1023px) {
          .SolutionSubMenu {
            width: min(calc(100vw - 40px), 1200px);
          }
        }
        @media (min-width: 1320px) {
          .SolutionSubMenu {
            width: min(calc(100vw - 60px), 1200px);
          }
        }
        @media (min-width: 1440px) {
          .SolutionSubMenu {
            width: min(calc(100vw - 80px), 1200px);
          }
        }
        .SolutionMenuBoxTeamSize .SolutionMenuBox:hover {
          position: relative;
          background: linear-gradient(white, white) padding-box, 
                      linear-gradient(215deg, #BD5DD0 0%, #9333EA 100%) border-box;
          border: 1px solid transparent;
          transition: all 0.3s ease-in-out;
        }
        .SolutionMenuBoxUseCase .SolutionBoxItem:hover {
          position: relative;
          background: linear-gradient(white, white) padding-box, 
                      linear-gradient(215deg, #BD5DD0 0%, #9333EA 100%) border-box;
          border: 1px solid transparent;
          transition: all 0.3s ease-in-out;
        }
        .SolutionMenuBoxUseCase .SolutionBoxItem:hover .SolutionLink {
          color: #7824B1 !important;
          transition: all 0.3s ease-in-out;
        }
        .SolutionAiFeaturesButton:hover {
          background: linear-gradient(215deg, #BD5DD0 0%, #9333EA 100%) !important;
          color: white;
          transition: all 0.3s ease-in-out;
        }
      `}</style>
      <div className={`SolutionSubMenu sub-menu relative min-[1023px]:fixed min-[1023px]:left-1/2 min-[1023px]:-translate-x-1/2 shadow-[0px_20px_25px_-5px_rgba(0,0,0,0.1),0px_10px_10px_-5px_rgba(0,0,0,0.04)] rounded-none min-[1023px]:rounded-2xl border border-gray-200 bg-white transition-all duration-300 ease-in-out ${isOpen ? 'block opacity-100 translate-y-0' : 'hidden opacity-0 translate-y-2'}`}>
        <div className="SolutionMenuGrid grid grid-cols-1 min-[1023px]:grid-cols-12 gap-0 min-[1023px]:gap-4">
          {/* Left Column - Main Categories */}
          <div className="SolutionTabBoxColumn p-4 min-[1023px]:p-[15px] col-span-1 min-[1023px]:col-span-3 border-b min-[1023px]:border-b-0 border-gray-200">
            <div className="SolutionTabBoxContainer flex flex-col space-y-2 mb-0">
              {/* By Team Size */}
              <button
                onClick={() => setActiveTab('team-size')}
                className={`SolutionTabBox SolutionTabBoxTeamSize flex items-center justify-between text-[14px] font-semibold sf-pro-font w-full px-3 py-2 rounded-lg transition-all duration-200 ${activeTab === 'team-size'
                  ? 'bg-gray-100 text-[#232529]'
                  : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                  }`}
              >
                <svg className="SolutionTabIcon mr-1 h-4 w-5" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.46951 0.73487C1.32663 0.734168 1.189 0.788703 1.08538 0.887083C0.981754 0.985462 0.920153 1.12008 0.913443 1.2628L0.123705 17.8798C0.120052 17.9547 0.131639 18.0295 0.157766 18.0998C0.183893 18.1701 0.224016 18.2343 0.275702 18.2886C0.327389 18.343 0.389565 18.3862 0.458462 18.4158C0.52736 18.4454 0.601545 18.4607 0.676524 18.4607H4.44564H12.8039H15.3473H17.9761C18.0492 18.461 18.1217 18.4468 18.1893 18.419C18.2568 18.3911 18.3183 18.3502 18.3699 18.2985C18.4216 18.2468 18.4626 18.1854 18.4904 18.1178C18.5183 18.0502 18.5325 17.9778 18.5322 17.9047V11.8237V8.38128C18.5322 8.28917 18.5093 8.1985 18.4655 8.11748C18.4216 8.03647 18.3583 7.96766 18.2812 7.91728C18.2041 7.86691 18.1156 7.83656 18.0238 7.82899C17.932 7.82141 17.8398 7.83684 17.7554 7.87389L12.264 10.2821V8.28283C12.2632 8.19305 12.2406 8.10482 12.1981 8.02573C12.1556 7.94664 12.0945 7.87906 12.0201 7.82882C11.9457 7.77858 11.8602 7.74717 11.771 7.73731C11.6817 7.72745 11.5914 7.73942 11.5078 7.7722L4.65659 10.4952L4.22927 1.2628C4.22256 1.12008 4.16096 0.985464 4.05734 0.887085C3.95372 0.788705 3.81609 0.73417 3.67321 0.73487H1.46951ZM1.99636 1.84375H3.14527L3.18316 2.65296H1.95744L1.99636 1.84375ZM1.90441 3.76076H3.23398L3.86144 17.3518H1.25638L1.90441 3.76076ZM11.1562 9.10069V11.1291C11.1562 11.2212 11.1791 11.3119 11.223 11.3929C11.2668 11.4739 11.3301 11.5427 11.4073 11.5931C11.4844 11.6435 11.5728 11.6738 11.6646 11.6814C11.7564 11.689 11.8487 11.6736 11.933 11.6365L17.4244 9.22727V11.8237V17.3518H15.9033V13.7255C15.9028 13.5786 15.8438 13.438 15.7396 13.3345C15.6353 13.2311 15.4942 13.1732 15.3473 13.1738H12.8039C12.657 13.1732 12.5159 13.2311 12.4116 13.3345C12.3073 13.438 12.2484 13.5786 12.2478 13.7255V17.3518H10.3708V13.7255C10.3703 13.5786 10.3113 13.438 10.2071 13.3345C10.1028 13.2311 9.96168 13.1732 9.81478 13.1738H7.27139C7.12449 13.1732 6.98338 13.2311 6.8791 13.3345C6.77482 13.438 6.71591 13.5786 6.71532 13.7255V17.3518H4.97357L4.71068 11.6614L11.1562 9.10069ZM7.82312 14.2816H9.26304V17.3518H7.82312L7.82312 14.2816ZM13.3556 14.2816H14.7945V17.3518H13.3556L13.3556 14.2816Z" fill="#524F51"/>
</svg>

                By Team Size
                <svg className="ml-auto h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>

              {/* By Use Case */}
              <button
                onClick={() => setActiveTab('use-case')}
                className={`SolutionTabBox SolutionTabBoxUseCase flex items-center justify-between text-[14px] font-semibold sf-pro-font w-full px-3 py-2 rounded-lg transition-all duration-200 ${activeTab === 'use-case'
                  ? 'bg-gray-100 text-[#232529]'
                  : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                  }`}
              >
                <svg className="SolutionTabIcon mr-2 h-5 w-5" viewBox="0 0 22 19" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M9.94521 12.15L7.34235 11.597L6.0138 9.29301C5.82801 8.97052 5.41699 8.8601 5.0945 9.04588C4.99109 9.10547 4.90608 9.19047 4.84736 9.29301L3.51793 11.597L0.915069 12.15C0.551382 12.2271 0.320037 12.5847 0.397145 12.9484C0.421684 13.064 0.476016 13.171 0.554887 13.2586L2.33388 15.2356L2.05695 17.8805C2.01839 18.2503 2.28654 18.5807 2.65638 18.6201C2.77381 18.6324 2.89125 18.614 2.99902 18.5658L5.43003 17.4844L7.86014 18.5658C8.20016 18.7165 8.59716 18.5631 8.74875 18.2231C8.79695 18.1153 8.81536 17.997 8.80309 17.8805L8.52616 15.2357L10.306 13.2586C10.5549 12.9826 10.533 12.5567 10.2561 12.3078C10.1684 12.2289 10.0624 12.1746 9.94672 12.15L9.94521 12.15ZM7.32395 14.5572C7.19601 14.6992 7.13466 14.8885 7.15482 15.0778L7.34236 16.862L5.70445 16.132C5.53006 16.054 5.33112 16.054 5.15674 16.132L3.51796 16.862L3.7055 15.0778C3.72565 14.8876 3.66431 14.6992 3.53636 14.5572L2.33575 13.2243L4.0902 12.8519C4.27599 12.8115 4.43637 12.695 4.53189 12.5302L5.42928 10.9765L6.32666 12.5302C6.42218 12.695 6.58255 12.8124 6.76923 12.8519L8.52368 13.2243L7.32395 14.5572ZM21.8862 12.6003C21.8117 12.3716 21.6216 12.1998 21.3858 12.1499L18.783 11.5969L17.4553 9.2929C17.2695 8.9704 16.8585 8.85998 16.536 9.04576C16.4326 9.10535 16.3476 9.19036 16.2889 9.2929L14.9594 11.5969L12.3566 12.1499C11.9929 12.2279 11.7615 12.5854 11.8395 12.9491C11.8641 13.0639 11.9184 13.1708 11.9973 13.2585L13.7754 15.2355L13.4984 17.8803C13.4599 18.2502 13.728 18.5805 14.0979 18.62C14.2153 18.6323 14.3327 18.6139 14.4405 18.5657L16.8715 17.4842L19.3016 18.5657C19.6417 18.7164 20.0387 18.563 20.1903 18.223C20.2385 18.1152 20.2569 17.9969 20.2446 17.8804L19.9677 15.2356L21.7475 13.2585C21.9079 13.0797 21.9605 12.8282 21.886 12.6004L21.8862 12.6003ZM18.7656 14.5572C18.6376 14.6992 18.5763 14.8885 18.5964 15.0778L18.784 16.862L17.1452 16.132C16.9708 16.054 16.7719 16.054 16.5975 16.132L14.9587 16.862L15.1463 15.0778C15.1664 14.8876 15.1051 14.6992 14.9771 14.5572L13.7765 13.2243L15.531 12.8519C15.7176 12.8115 15.878 12.695 15.9735 12.5302L16.8709 10.9765L17.7683 12.5302C17.8638 12.695 18.0242 12.8124 18.2109 12.8519L19.9653 13.2243L18.7656 14.5572ZM14.2506 10.016C14.4452 9.87494 14.5495 9.64008 14.5241 9.40084L14.2471 6.75513L16.027 4.77808C16.2759 4.50202 16.254 4.07612 15.977 3.82724C15.8894 3.74836 15.7825 3.69315 15.6668 3.66949L13.064 3.11652L11.7345 0.813391C11.5487 0.490894 11.1377 0.380471 10.8152 0.566252C10.7118 0.625842 10.6268 0.710849 10.5681 0.813391L9.23866 3.11742L6.6358 3.67039C6.27212 3.74751 6.0399 4.10506 6.11788 4.46875C6.14242 4.58442 6.19675 4.69134 6.27562 4.77897L8.05462 6.75602L7.77768 9.40173C7.73912 9.77154 8.00728 10.1019 8.37711 10.1414C8.49454 10.1536 8.61198 10.1352 8.71976 10.087L11.1508 9.00387L13.5809 10.0853C13.8008 10.1834 14.0561 10.1571 14.2506 10.016ZM11.4243 7.65255C11.2499 7.57456 11.051 7.57456 10.8766 7.65255L9.2387 8.38255L9.42624 6.59831C9.44639 6.40814 9.38505 6.21973 9.2571 6.07776L8.05649 4.74483L9.81094 4.37237C9.99673 4.33206 10.1571 4.21551 10.2526 4.05075L11.15 2.49698L12.0474 4.05075C12.1429 4.21551 12.3033 4.33293 12.49 4.37237L14.2444 4.74483L13.0438 6.07776C12.9159 6.21973 12.8545 6.40903 12.8747 6.59831L13.0622 8.38255L11.4243 7.65255Z" fill="#524F51" stroke="white" stroke-width="0.1"/>
</svg>

                
                By Use Case
                <svg className="ml-auto h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>

              {/* By Industry */}
              <button
                onClick={() => setActiveTab('industry')}
                className={`SolutionTabBox SolutionTabBoxIndustry flex items-center justify-between text-[14px] font-semibold sf-pro-font w-full px-3 py-2 rounded-lg transition-all duration-200 ${activeTab === 'industry'
                  ? 'bg-gray-100 text-[#232529]'
                  : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                  }`}
              >
                <svg className="SolutionTabIcon mr-2 h-5 w-5" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M3.485 8.21722H6.15018C6.85678 8.21641 7.53421 7.93535 8.03385 7.43571C8.5335 6.93607 8.81455 6.25864 8.81536 5.55204V2.88686C8.81455 2.18026 8.5335 1.50283 8.03385 1.00318C7.53421 0.503541 6.85678 0.222487 6.15018 0.22168H3.485C2.7784 0.222487 2.10097 0.503541 1.60133 1.00318C1.10169 1.50283 0.820632 2.18026 0.819824 2.88686V5.55204C0.820632 6.25864 1.10169 6.93607 1.60133 7.43571C2.10097 7.93535 2.7784 8.21641 3.485 8.21722ZM2.15241 2.88686C2.15277 2.53354 2.29328 2.1948 2.54311 1.94497C2.79294 1.69513 3.13169 1.55462 3.485 1.55427H6.15018C6.5035 1.55462 6.84224 1.69513 7.09207 1.94497C7.34191 2.1948 7.48242 2.53354 7.48277 2.88686V5.55204C7.48242 5.90535 7.34191 6.2441 7.09207 6.49393C6.84224 6.74376 6.5035 6.88427 6.15018 6.88463H3.485C3.13169 6.88427 2.79294 6.74376 2.54311 6.49393C2.29328 6.2441 2.15277 5.90535 2.15241 5.55204V2.88686ZM16.8109 0.22168H14.1457C13.4391 0.222487 12.7617 0.503541 12.262 1.00318C11.7624 1.50283 11.4813 2.18026 11.4805 2.88686V5.55204C11.4813 6.25864 11.7624 6.93607 12.262 7.43571C12.7617 7.93535 13.4391 8.21641 14.1457 8.21722H16.8109C17.5175 8.21641 18.1949 7.93535 18.6946 7.43571C19.1942 6.93607 19.4753 6.25864 19.4761 5.55204V2.88686C19.4753 2.18026 19.1942 1.50283 18.6946 1.00318C18.1949 0.503541 17.5175 0.222487 16.8109 0.22168ZM18.1435 5.55204C18.1431 5.90535 18.0026 6.2441 17.7528 6.49393C17.503 6.74376 17.1642 6.88427 16.8109 6.88463H14.1457C13.7924 6.88427 13.4537 6.74376 13.2038 6.49393C12.954 6.2441 12.8135 5.90535 12.8131 5.55204V2.88686C12.8135 2.53354 12.954 2.1948 13.2038 1.94497C13.4537 1.69513 13.7924 1.55462 14.1457 1.55427H16.8109C17.1642 1.55462 17.503 1.69513 17.7528 1.94497C18.0026 2.1948 18.1431 2.53354 18.1435 2.88686V5.55204ZM16.8109 10.8824H14.1457C13.4391 10.8832 12.7617 11.1643 12.262 11.6639C11.7624 12.1635 11.4813 12.841 11.4805 13.5476V16.2128C11.4813 16.9194 11.7624 17.5968 12.262 18.0964C12.7617 18.5961 13.4391 18.8771 14.1457 18.8779H16.8109C17.5175 18.8771 18.1949 18.5961 18.6946 18.0964C19.1942 17.5968 19.4753 16.9194 19.4761 16.2128V13.5476C19.4753 12.841 19.1942 12.1635 18.6946 11.6639C18.1949 11.1643 17.5175 10.8832 16.8109 10.8824ZM18.1435 16.2128C18.1431 16.5661 18.0026 16.9048 17.7528 17.1546C17.503 17.4045 17.1642 17.545 16.8109 17.5453H14.1457C13.7924 17.545 13.4537 17.4045 13.2038 17.1546C12.954 16.9048 12.8135 16.5661 12.8131 16.2128V13.5476C12.8135 13.1943 12.954 12.8555 13.2038 12.6057C13.4537 12.3558 13.7924 12.2153 14.1457 12.215H16.8109C17.1642 12.2153 17.503 12.3558 17.7528 12.6057C18.0026 12.8555 18.1431 13.1943 18.1435 13.5476V16.2128ZM10.1479 18.2116C10.148 18.2991 10.1308 18.3858 10.0973 18.4667C10.0639 18.5475 10.0148 18.621 9.95291 18.6829C9.89103 18.7448 9.81756 18.7938 9.7367 18.8273C9.65583 18.8608 9.56917 18.878 9.48165 18.8779H6.15018C5.44358 18.8771 4.76615 18.5961 4.26651 18.0964C3.76686 17.5968 3.48581 16.9194 3.485 16.2128V13.1571L1.95722 14.685C1.83155 14.8063 1.66324 14.8735 1.48854 14.872C1.31384 14.8705 1.14673 14.8004 1.02319 14.6768C0.899655 14.5533 0.829582 14.3862 0.828063 14.2115C0.826545 14.0368 0.893704 13.8685 1.01508 13.7428L3.68025 11.0776C3.74211 11.0158 3.81555 10.9667 3.89637 10.9332C3.9772 10.8997 4.06383 10.8825 4.15132 10.8825C4.23881 10.8825 4.32545 10.8997 4.40628 10.9332C4.4871 10.9667 4.56054 11.0158 4.62239 11.0776L7.28757 13.7428C7.35121 13.8043 7.40197 13.8778 7.43689 13.9591C7.47181 14.0404 7.49019 14.1278 7.49096 14.2163C7.49173 14.3048 7.47487 14.3925 7.44137 14.4744C7.40787 14.5563 7.35839 14.6307 7.29583 14.6932C7.23327 14.7558 7.15888 14.8053 7.07699 14.8388C6.99511 14.8723 6.90737 14.8891 6.8189 14.8884C6.73043 14.8876 6.643 14.8692 6.56171 14.8343C6.48042 14.7994 6.4069 14.7486 6.34543 14.685L4.81759 13.1571V16.2128C4.81794 16.5661 4.95846 16.9048 5.20829 17.1546C5.45812 17.4045 5.79686 17.545 6.15018 17.5453H9.48165C9.56917 17.5453 9.65583 17.5625 9.7367 17.596C9.81756 17.6294 9.89103 17.6785 9.95291 17.7404C10.0148 17.8023 10.0639 17.8757 10.0973 17.9566C10.1308 18.0375 10.148 18.1241 10.1479 18.2116Z" fill="#524F51"/>
</svg>

                By Industry
                <svg className="ml-auto h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>
            </div>
          </div>

          {/* Middle Columns - Solutions Grid */}
          <div className="SolutionMenuBoxColumn col-span-1 min-[1023px]:col-span-6">
            {activeTab === 'team-size' && (
              <div className="SolutionMenuBoxContainer SolutionMenuBoxTeamSize pt-4 pb-4 grid grid-cols-1 sm:grid-cols-3 min-[1023px]:grid-cols-3 gap-x-4 min-[1023px]:gap-x-4 gap-y-4 min-[1023px]:gap-y-6">
                {/* Solo Operators */}
                <div className="SolutionMenuBox SolutionMenuBoxSolo text-center p-3 border border-gray-200 rounded-xl">
                  <div className="SolutionBoxIcon pb-3 flex justify-center">
                    <svg className="h-8 w-8" viewBox="0 0 33 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <g clip-path="url(#clip0_6388_24341)">
                        <mask id="mask0_6388_24341" maskUnits="userSpaceOnUse" x="0" y="0" width="33" height="32">
                          <path d="M32.0145 0.163086H0.894531V31.2831H32.0145V0.163086Z" fill="white" />
                        </mask>
                        <g mask="url(#mask0_6388_24341)">
                          <path d="M16.4545 15.723C20.0351 15.723 22.9379 12.8203 22.9379 9.23968C22.9379 5.65904 20.0351 2.75635 16.4545 2.75635C12.8739 2.75635 9.97119 5.65904 9.97119 9.23968C9.97119 12.8203 12.8739 15.723 16.4545 15.723Z" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                          <path d="M27.5931 28.6895C27.5931 23.6714 22.601 19.6128 16.4548 19.6128C10.3086 19.6128 5.31641 23.6714 5.31641 28.6895" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                      </g>
                      <defs>
                        <clipPath id="clip0_6388_24341">
                          <rect width="31.12" height="31.12" fill="white" transform="translate(0.894531 0.163086)" />
                        </clipPath>
                      </defs>
                    </svg>


                  </div>
                  <h3 className="SolutionBoxHeader sf-pro-font text-[16px] font-medium text-gray-900" style={{ fontFamily: 'SFPRO, sans-serif' }}>Solo Operators</h3>
                  <p className="SolutionBoxSubtext sf-pro-font text-[14px] text-black-500 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>(1-3 tech)</p>
                  <p className="SolutionBoxDescription sf-pro-font text-[14px] pt-2 pb-0 text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '22px' }}>Help manage your business independently</p>
                </div>

                {/* Small Teams */}
                <div className="SolutionMenuBox SolutionMenuBoxSmall text-center p-3 border border-gray-200 rounded-xl">
                  <div className="SolutionBoxIcon pb-3 flex justify-center">
                    <svg className="h-8 w-8" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <g clip-path="url(#clip0_6388_24322)">
                        <mask id="mask0_6388_24322" maskUnits="userSpaceOnUse" x="0" y="0" width="32" height="32">
                          <path d="M31.9622 0.163086H0.845947V31.2793H31.9622V0.163086Z" fill="white" />
                        </mask>
                        <g mask="url(#mask0_6388_24322)">
                          <path d="M12.722 14.2559C12.5923 14.2429 12.4368 14.2429 12.2941 14.2559C9.20846 14.1522 6.75806 11.624 6.75806 8.51236C6.75806 5.33591 9.32514 2.75586 12.5146 2.75586C15.6911 2.75586 18.2711 5.33591 18.2711 8.51236C18.2581 11.624 15.8077 14.1522 12.722 14.2559Z" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                          <path d="M22.1214 5.34912C24.6367 5.34912 26.6592 7.38464 26.6592 9.8869C26.6592 12.3373 24.7144 14.3339 22.29 14.4247C22.1863 14.4117 22.0696 14.4117 21.9529 14.4247" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                          <path d="M6.23964 19.0402C3.10209 21.1405 3.10209 24.5633 6.23964 26.6507C9.80504 29.0363 15.6523 29.0363 19.2177 26.6507C22.3552 24.5504 22.3552 21.1276 19.2177 19.0402C15.6652 16.6676 9.818 16.6676 6.23964 19.0402Z" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                          <path d="M24.6238 26.0935C25.5573 25.899 26.4389 25.523 27.1649 24.9655C29.1875 23.4486 29.1875 20.9464 27.1649 19.4295C26.4519 18.8849 25.5832 18.5219 24.6627 18.3145" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                      </g>
                      <defs>
                        <clipPath id="clip0_6388_24322">
                          <rect width="31.1162" height="31.1162" fill="white" transform="translate(0.845947 0.163086)" />
                        </clipPath>
                      </defs>
                    </svg>

                  </div>
                  <h3 className="SolutionBoxHeader sf-pro-font text-[16px] font-medium text-gray-900" style={{ fontFamily: 'SFPRO, sans-serif' }}>Small Teams</h3>
                  <p className="SolutionBoxSubtext sf-pro-font text-[14px] text-black-500 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>(5-20 tech)</p>
                  <p className="SolutionBoxDescription sf-pro-font text-[14px] pt-2 pb-0 text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '22px' }}>Help your team divide and conquer effectively</p>
                </div>

                {/* Growing Businesses */}
                <div className="SolutionMenuBox SolutionMenuBoxGrowing text-center p-4 border border-gray-200 rounded-xl">
                  <div className="SolutionBoxIcon pb-3 flex justify-center">
                    <svg className="h-8 w-8" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <g clip-path="url(#clip0_6388_24349)">
                        <mask id="mask0_6388_24349" maskUnits="userSpaceOnUse" x="0" y="0" width="32" height="32">
                          <path d="M31.6097 0.163086H0.489746V31.2831H31.6097V0.163086Z" fill="white" />
                        </mask>
                        <g mask="url(#mask0_6388_24349)">
                          <path d="M3.08301 28.6895H29.0163" stroke="#232529" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                          <path d="M22.5332 2.75635H9.56655C5.67655 2.75635 4.37988 5.07738 4.37988 7.94301V28.6897H27.7199V7.94301C27.7199 5.07738 26.4232 2.75635 22.5332 2.75635Z" stroke="#232529" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                          <path d="M9.56641 21.5581H13.4564" stroke="#232529" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                          <path d="M18.6431 21.5581H22.5331" stroke="#232529" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                          <path d="M9.56641 15.7231H13.4564" stroke="#232529" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                          <path d="M18.6431 15.7231H22.5331" stroke="#232529" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                          <path d="M9.56641 9.88818H13.4564" stroke="#232529" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                          <path d="M18.6431 9.88818H22.5331" stroke="#232529" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                      </g>
                      <defs>
                        <clipPath id="clip0_6388_24349">
                          <rect width="31.12" height="31.12" fill="white" transform="translate(0.489746 0.163086)" />
                        </clipPath>
                      </defs>
                    </svg>

                  </div>
                  <h3 className="SolutionBoxHeader sf-pro-font text-[16px] font-medium text-gray-900" style={{ fontFamily: 'SFPRO, sans-serif' }}>Growing Businesses</h3>
                  <p className="SolutionBoxSubtext sf-pro-font text-[14px] text-black-500 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}></p>
                  <p className="SolutionBoxDescription sf-pro-font text-[14px] pt-2 pb-0 text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '22px' }}>Help your team scale quickly</p>
                </div>
              </div>
            )}

            {activeTab === 'use-case' && (
              <div className="SolutionMenuBoxContainer SolutionMenuBoxUseCase pt-4 pb-4 grid grid-cols-1 sm:grid-cols-2 min-[1023px]:grid-cols-2 gap-3">
                {/* Left Column */}
                <div className="SolutionMenuBox space-y-2">
                  <div className="SolutionBoxItem p-4 border border-gray-200 rounded-lg">
                    <div className="flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_7570_2436)">
                          <mask id="mask0_7570_2436" maskUnits="userSpaceOnUse" x="0" y="0" width="16" height="16">
                            <path d="M15.4189 0.549805H0.418945V15.5498H15.4189V0.549805Z" fill="white" />
                          </mask>
                          <g mask="url(#mask0_7570_2436)">
                            <path d="M7.91895 9.76855C7.6627 9.76855 7.4502 9.55605 7.4502 9.2998V6.1748C7.4502 5.91855 7.6627 5.70605 7.91895 5.70605C8.1752 5.70605 8.3877 5.91855 8.3877 6.1748V9.2998C8.3877 9.55605 8.1752 9.76855 7.91895 9.76855Z" fill="#232529" />
                            <path d="M7.91895 11.7999C7.88145 11.7999 7.8377 11.7937 7.79395 11.7874C7.75645 11.7812 7.71895 11.7687 7.68145 11.7499C7.64395 11.7374 7.60645 11.7187 7.56895 11.6937C7.5377 11.6687 7.50645 11.6437 7.4752 11.6187C7.3627 11.4999 7.29395 11.3374 7.29395 11.1749C7.29395 11.0124 7.3627 10.8499 7.4752 10.7312C7.50645 10.7062 7.5377 10.6812 7.56895 10.6562C7.60645 10.6312 7.64395 10.6124 7.68145 10.5999C7.71895 10.5812 7.75645 10.5687 7.79395 10.5624C7.8752 10.5437 7.9627 10.5437 8.0377 10.5624C8.08145 10.5687 8.11895 10.5812 8.15645 10.5999C8.19395 10.6124 8.23145 10.6312 8.26895 10.6562C8.3002 10.6812 8.33145 10.7062 8.3627 10.7312C8.4752 10.8499 8.54395 11.0124 8.54395 11.1749C8.54395 11.3374 8.4752 11.4999 8.3627 11.6187C8.33145 11.6437 8.3002 11.6687 8.26895 11.6937C8.23145 11.7187 8.19395 11.7374 8.15645 11.7499C8.11895 11.7687 8.08145 11.7812 8.0377 11.7874C8.0002 11.7937 7.95645 11.7999 7.91895 11.7999Z" fill="#232529" />
                            <path d="M11.7065 14.3997H4.13149C2.91274 14.3997 1.98149 13.9559 1.50649 13.1559C1.03774 12.3559 1.10024 11.3247 1.69399 10.2559L5.48149 3.44346C6.10649 2.31846 6.969 1.69971 7.919 1.69971C8.869 1.69971 9.7315 2.31846 10.3565 3.44346L14.144 10.2622C14.7378 11.3309 14.8065 12.3559 14.3315 13.1622C13.8565 13.9559 12.9253 14.3997 11.7065 14.3997ZM7.919 2.63721C7.3315 2.63721 6.7565 3.08721 6.30024 3.89971L2.51899 10.7184C2.09399 11.4809 2.02524 12.1809 2.31899 12.6872C2.61274 13.1934 3.26274 13.4684 4.13774 13.4684H11.7128C12.5878 13.4684 13.2315 13.1934 13.5315 12.6872C13.8315 12.1809 13.7565 11.4872 13.3315 10.7184L9.53776 3.89971C9.0815 3.08721 8.5065 2.63721 7.919 2.63721Z" fill="#232529" />
                          </g>
                        </g>
                        <defs>
                          <clipPath id="clip0_7570_2436">
                            <rect width="15" height="15" fill="white" transform="translate(0.418945 0.549805)" />
                          </clipPath>
                        </defs>
                      </svg>

                      <div className="flex-1">
                        <Link href="#" onClick={onLinkClick} className="SolutionLink block text-[14px] font-medium text-[#232529] hover:text-[#7824B1] mb-1" style={{ fontFamily: 'SFPRO, sans-serif' }}>Emergency Response Services</Link>
                        <p className="text-[14px] text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '18px' }}>Empower teams to achieve goals with efficient, clear project.</p>
                      </div>
                    </div>
                  </div>

                  <div className="SolutionBoxItem p-4 border border-gray-200 rounded-lg">
                    <div className="flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 15 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.375 8.0498H6.25" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M9.375 5.5498H6.25" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M11.875 11.1748V3.6748C11.875 3.34328 11.7433 3.02534 11.5089 2.79092C11.2745 2.5565 10.9565 2.4248 10.625 2.4248H2.5" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M5 13.6748H12.5C12.8315 13.6748 13.1495 13.5431 13.3839 13.3087C13.6183 13.0743 13.75 12.7563 13.75 12.4248V11.7998C13.75 11.634 13.6842 11.4751 13.5669 11.3579C13.4497 11.2407 13.2908 11.1748 13.125 11.1748H6.875C6.70924 11.1748 6.55027 11.2407 6.43306 11.3579C6.31585 11.4751 6.25 11.634 6.25 11.7998V12.4248C6.25 12.7563 6.1183 13.0743 5.88388 13.3087C5.64946 13.5431 5.33152 13.6748 5 13.6748ZM5 13.6748C4.66848 13.6748 4.35054 13.5431 4.11612 13.3087C3.8817 13.0743 3.75 12.7563 3.75 12.4248V3.6748C3.75 3.34328 3.6183 3.02534 3.38388 2.79092C3.14946 2.5565 2.83152 2.4248 2.5 2.4248C2.16848 2.4248 1.85054 2.5565 1.61612 2.79092C1.3817 3.02534 1.25 3.34328 1.25 3.6748V4.9248C1.25 5.09056 1.31585 5.24954 1.43306 5.36675C1.55027 5.48396 1.70924 5.5498 1.875 5.5498H3.75" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>

                      <div className="flex-1">
                        <Link href="#" onClick={onLinkClick} className="SolutionLink block text-[14px] font-medium text-[#232529] hover:text-[#7824B1] mb-1" style={{ fontFamily: 'SFPRO, sans-serif' }}>Recurring Maintenance Contracts</Link>
                        <p className="text-[14px] text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '18px' }}>Empower teams to achieve goals with efficient, clear project.</p>
                      </div>
                    </div>
                  </div>

                  <div className="SolutionBoxItem p-4 border border-gray-200 rounded-lg">
                    <div className="flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_7570_2486)">
                          <mask id="mask0_7570_2486" maskUnits="userSpaceOnUse" x="0" y="0" width="16" height="16">
                            <path d="M15.4189 0.549805H0.418945V15.5498H15.4189V0.549805Z" fill="white" />
                          </mask>
                          <g mask="url(#mask0_7570_2486)">
                            <path d="M7.91895 9.76855C7.6627 9.76855 7.4502 9.55605 7.4502 9.2998V6.1748C7.4502 5.91855 7.6627 5.70605 7.91895 5.70605C8.1752 5.70605 8.3877 5.91855 8.3877 6.1748V9.2998C8.3877 9.55605 8.1752 9.76855 7.91895 9.76855Z" fill="#232529" />
                            <path d="M7.91895 11.7999C7.88145 11.7999 7.8377 11.7937 7.79395 11.7874C7.75645 11.7812 7.71895 11.7687 7.68145 11.7499C7.64395 11.7374 7.60645 11.7187 7.56895 11.6937C7.5377 11.6687 7.50645 11.6437 7.4752 11.6187C7.3627 11.4999 7.29395 11.3374 7.29395 11.1749C7.29395 11.0124 7.3627 10.8499 7.4752 10.7312C7.50645 10.7062 7.5377 10.6812 7.56895 10.6562C7.60645 10.6312 7.64395 10.6124 7.68145 10.5999C7.71895 10.5812 7.75645 10.5687 7.79395 10.5624C7.8752 10.5437 7.9627 10.5437 8.0377 10.5624C8.08145 10.5687 8.11895 10.5812 8.15645 10.5999C8.19395 10.6124 8.23145 10.6312 8.26895 10.6562C8.3002 10.6812 8.33145 10.7062 8.3627 10.7312C8.4752 10.8499 8.54395 11.0124 8.54395 11.1749C8.54395 11.3374 8.4752 11.4999 8.3627 11.6187C8.33145 11.6437 8.3002 11.6687 8.26895 11.6937C8.23145 11.7187 8.19395 11.7374 8.15645 11.7499C8.11895 11.7687 8.08145 11.7812 8.0377 11.7874C8.0002 11.7937 7.95645 11.7999 7.91895 11.7999Z" fill="#232529" />
                            <path d="M11.7065 14.3997H4.13149C2.91274 14.3997 1.98149 13.9559 1.50649 13.1559C1.03774 12.3559 1.10024 11.3247 1.69399 10.2559L5.48149 3.44346C6.10649 2.31846 6.969 1.69971 7.919 1.69971C8.869 1.69971 9.7315 2.31846 10.3565 3.44346L14.144 10.2622C14.7378 11.3309 14.8065 12.3559 14.3315 13.1622C13.8565 13.9559 12.9253 14.3997 11.7065 14.3997ZM7.919 2.63721C7.3315 2.63721 6.7565 3.08721 6.30024 3.89971L2.51899 10.7184C2.09399 11.4809 2.02524 12.1809 2.31899 12.6872C2.61274 13.1934 3.26274 13.4684 4.13774 13.4684H11.7128C12.5878 13.4684 13.2315 13.1934 13.5315 12.6872C13.8315 12.1809 13.7565 11.4872 13.3315 10.7184L9.53776 3.89971C9.0815 3.08721 8.5065 2.63721 7.919 2.63721Z" fill="#232529" />
                          </g>
                        </g>
                        <defs>
                          <clipPath id="clip0_7570_2486">
                            <rect width="15" height="15" fill="white" transform="translate(0.418945 0.549805)" />
                          </clipPath>
                        </defs>
                      </svg>

                      <div className="flex-1">
                        <Link href="#" onClick={onLinkClick} className="SolutionLink block text-[14px] font-medium text-[#232529] hover:text-[#7824B1] mb-1" style={{ fontFamily: 'SFPRO, sans-serif' }}>Installation & Projects</Link>
                        <p className="text-[14px] text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '18px' }}>Empower teams to achieve goals with efficient, clear project.</p>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Right Column */}
                <div className="SolutionMenuBox space-y-2">
                  <div className="SolutionBoxItem p-4 border border-gray-200 rounded-lg">
                    <div className="flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_7570_2512)">
                          <mask id="mask0_7570_2512" maskUnits="userSpaceOnUse" x="0" y="0" width="16" height="16">
                            <path d="M15.2344 0.549805H0.234375V15.5498H15.2344V0.549805Z" fill="white" />
                          </mask>
                          <g mask="url(#mask0_7570_2512)">
                            <path d="M13.9844 14.7686H1.48438C1.22813 14.7686 1.01562 14.5561 1.01562 14.2998C1.01562 14.0436 1.22813 13.8311 1.48438 13.8311H13.9844C14.2406 13.8311 14.4531 14.0436 14.4531 14.2998C14.4531 14.5561 14.2406 14.7686 13.9844 14.7686Z" fill="#232529" />
                            <path d="M2.54688 14.3H1.60938L1.64062 6.78124C1.64062 6.24999 1.88437 5.75626 2.30312 5.43126L6.67812 2.025C7.29688 1.54375 8.16562 1.54375 8.79062 2.025L13.1656 5.42501C13.5781 5.75001 13.8281 6.25624 13.8281 6.78124V14.3H12.8906V6.7875C12.8906 6.55 12.7781 6.31876 12.5906 6.16876L8.21562 2.76876C7.93437 2.55001 7.54062 2.55001 7.25312 2.76876L2.87812 6.17501C2.69062 6.31876 2.57812 6.55 2.57812 6.7875L2.54688 14.3Z" fill="#232529" />
                            <path d="M9.29688 14.7686H6.17188C5.91563 14.7686 5.70312 14.5561 5.70312 14.2998V12.1123C5.70312 11.3373 6.33438 10.7061 7.10938 10.7061H8.35938C9.13438 10.7061 9.76562 11.3373 9.76562 12.1123V14.2998C9.76562 14.5561 9.55312 14.7686 9.29688 14.7686ZM6.64062 13.8311H8.82812V12.1123C8.82812 11.8561 8.61562 11.6436 8.35938 11.6436H7.10938C6.85312 11.6436 6.64062 11.8561 6.64062 12.1123V13.8311Z" fill="#232529" />
                            <path d="M6.17188 9.6123H4.92188C4.32188 9.6123 3.82812 9.11855 3.82812 8.51855V7.58105C3.82812 6.98105 4.32188 6.4873 4.92188 6.4873H6.17188C6.77187 6.4873 7.26562 6.98105 7.26562 7.58105V8.51855C7.26562 9.11855 6.77187 9.6123 6.17188 9.6123ZM4.92188 7.4248C4.83438 7.4248 4.76562 7.49355 4.76562 7.58105V8.51855C4.76562 8.60605 4.83438 8.6748 4.92188 8.6748H6.17188C6.25938 8.6748 6.32812 8.60605 6.32812 8.51855V7.58105C6.32812 7.49355 6.25938 7.4248 6.17188 7.4248H4.92188Z" fill="#232529" />
                            <path d="M10.5469 9.6123H9.29688C8.69688 9.6123 8.20312 9.11855 8.20312 8.51855V7.58105C8.20312 6.98105 8.69688 6.4873 9.29688 6.4873H10.5469C11.1469 6.4873 11.6406 6.98105 11.6406 7.58105V8.51855C11.6406 9.11855 11.1469 9.6123 10.5469 9.6123ZM9.29688 7.4248C9.20938 7.4248 9.14062 7.49355 9.14062 7.58105V8.51855C9.14062 8.60605 9.20938 8.6748 9.29688 8.6748H10.5469C10.6344 8.6748 10.7031 8.60605 10.7031 8.51855V7.58105C10.7031 7.49355 10.6344 7.4248 10.5469 7.4248H9.29688Z" fill="#232529" />
                            <path d="M12.1097 5.39355C11.8534 5.39355 11.6409 5.18731 11.6409 4.93106L11.6284 3.51855H9.34717C9.09092 3.51855 8.87842 3.30605 8.87842 3.0498C8.87842 2.79355 9.09092 2.58105 9.34717 2.58105H12.0972C12.3534 2.58105 12.5659 2.7873 12.5659 3.04355L12.5847 4.91855C12.5784 5.18105 12.3722 5.39355 12.1097 5.39355Z" fill="#232529" />
                          </g>
                        </g>
                        <defs>
                          <clipPath id="clip0_7570_2512">
                            <rect width="15" height="15" fill="white" transform="translate(0.234375 0.549805)" />
                          </clipPath>
                        </defs>
                      </svg>

                      <div className="flex-1">
                        <Link href="#" onClick={onLinkClick} className="SolutionLink block text-[14px] font-medium text-[#232529] hover:text-[#7824B1] mb-1" style={{ fontFamily: 'SFPRO, sans-serif' }}>Residential Services</Link>
                        <p className="text-[14px] text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '18px' }}>Empower teams to achieve goals with efficient, clear project.</p>
                      </div>
                    </div>
                  </div>

                  <div className="SolutionBoxItem p-4 border border-gray-200 rounded-lg">
                    <div className="flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 1H14V15H2V1Z" stroke="#232529" strokeLinecap="round" strokeLinejoin="round" />
                        <path d="M5 4H11" stroke="#232529" strokeLinecap="round" strokeLinejoin="round" />
                        <path d="M5 7H11" stroke="#232529" strokeLinecap="round" strokeLinejoin="round" />
                        <path d="M5 10H9" stroke="#232529" strokeLinecap="round" strokeLinejoin="round" />
                      </svg>
                      <div className="flex-1">
                        <Link href="#" onClick={onLinkClick} className="SolutionLink block text-[14px] font-medium text-[#232529] hover:text-[#7824B1] mb-1" style={{ fontFamily: 'SFPRO, sans-serif' }}>Commercial Services</Link>
                        <p className="text-[14px] text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '18px' }}>Empower teams to achieve goals with efficient, clear project.</p>
                      </div>
                    </div>
                  </div>

                  <div className="SolutionBoxItem p-4 border border-gray-200 rounded-lg">
                    <div className="flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_7570_109)">
                          <mask id="mask0_7570_109" maskUnits="userSpaceOnUse" x="0" y="0" width="16" height="16">
                            <path d="M15.2344 0.549805H0.234375V15.5498H15.2344V0.549805Z" fill="white" />
                          </mask>
                          <g mask="url(#mask0_7570_109)">
                            <path d="M7.73419 9.4055C6.40293 9.4055 5.31543 8.32425 5.31543 6.98675C5.31543 5.64922 6.40293 4.57422 7.73419 4.57422C9.06544 4.57422 10.1529 5.65547 10.1529 6.993C10.1529 8.3305 9.06544 9.4055 7.73419 9.4055ZM7.73419 5.51172C6.92169 5.51172 6.25293 6.17422 6.25293 6.993C6.25293 7.81175 6.91544 8.47425 7.73419 8.47425C8.55294 8.47425 9.21544 7.81175 9.21544 6.993C9.21544 6.17422 8.54669 5.51172 7.73419 5.51172Z" fill="#232529" />
                            <path d="M7.73441 14.7748C6.80941 14.7748 5.87817 14.4248 5.15317 13.7311C3.30942 11.9561 1.27192 9.1248 2.04067 5.75605C2.73442 2.6998 5.40317 1.33105 7.73441 1.33105C7.73441 1.33105 7.73441 1.33105 7.74066 1.33105C10.0719 1.33105 12.7407 2.6998 13.4344 5.7623C14.1969 9.13105 12.1594 11.9561 10.3157 13.7311C9.59066 14.4248 8.65941 14.7748 7.73441 14.7748ZM7.73441 2.26855C5.91567 2.26855 3.57817 3.2373 2.95942 5.9623C2.28442 8.90605 4.13442 11.4436 5.80942 13.0498C6.89066 14.0936 8.58441 14.0936 9.66566 13.0498C11.3344 11.4436 13.1844 8.90605 12.5219 5.9623C11.8969 3.2373 9.55316 2.26855 7.73441 2.26855Z" fill="#232529" />
                          </g>
                        </g>
                        <defs>
                          <clipPath id="clip0_7570_109">
                            <rect width="15" height="15" fill="white" transform="translate(0.234375 0.549805)" />
                          </clipPath>
                        </defs>
                      </svg>

                      <div className="flex-1">
                        <Link href="#" onClick={onLinkClick} className="SolutionLink block text-[14px] font-medium text-[#232529] hover:text-[#7824B1] mb-1" style={{ fontFamily: 'SFPRO, sans-serif' }}>Multi-Location Management</Link>
                        <p className="text-[14px] text-gray-600" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '18px' }}>Empower teams to achieve goals with efficient, clear project.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {activeTab === 'industry' && (
              <div className="SolutionMenuBoxContainer SolutionMenuBoxIndustry pt-4 pb-4 grid grid-cols-1 sm:grid-cols-2 min-[1023px]:grid-cols-2 gap-x-4 min-[1023px]:gap-x-8 gap-y-4 min-[1023px]:gap-y-6">
                {/* Left Column */}
                <div className="SolutionMenuBox">
                  <ul className="SolutionBoxList space-y-2">
                    <li className="SolutionBoxItem flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 2H14V6H2V2Z" stroke="#232529" strokeLinecap="round" strokeLinejoin="round" />
                        <path d="M4 6V14" stroke="#232529" strokeLinecap="round" strokeLinejoin="round" />
                        <path d="M8 6V14" stroke="#232529" strokeLinecap="round" strokeLinejoin="round" />
                        <path d="M12 6V14" stroke="#232529" strokeLinecap="round" strokeLinejoin="round" />
                      </svg>
                      <Link href="/industries/hvac/" onClick={onLinkClick} className="SolutionLink text-[13px] hover:text-[#7824B1] font-medium text-[#232529] hover:text-[#232529]" style={{ fontFamily: 'SFPRO, sans-serif' }}>HVAC</Link>
                    </li>
                    <li className="SolutionBoxItem flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_6262_9035)">
                          <path d="M4.6582 2.02637H6.54139C9.8551 2.02637 12.5414 4.71266 12.5414 8.02637V10.3462" stroke="#232529" stroke-linecap="round" />
                          <path d="M4.53467 6.77246H6.14734C7.25191 6.77246 8.14734 7.66789 8.14734 8.77246V10.3525" stroke="#232529" stroke-linecap="round" />
                          <rect x="1.28027" y="1.30273" width="3.19141" height="6.88745" rx="1" stroke="#232529" />
                          <rect x="13.7192" y="10.5054" width="3.19141" height="6.88745" rx="1" transform="rotate(90 13.7192 10.5054)" stroke="#232529" />
                        </g>
                        <defs>
                          <clipPath id="clip0_6262_9035">
                            <rect width="15" height="15" fill="white" />
                          </clipPath>
                        </defs>
                      </svg>

                      <Link href="/industries/plumbers/" onClick={onLinkClick} className="SolutionLink text-[13px] hover:text-[#7824B1] font-medium text-[#232529] hover:text-[#232529]" style={{ fontFamily: 'SFPRO, sans-serif' }}>Plumbing</Link>
                    </li>
                    <li className="SolutionBoxItem flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_6254_8911)">
                          <mask id="mask0_6254_8911" maskUnits="userSpaceOnUse" x="0" y="0" width="15" height="15">
                            <path d="M15 0H0V15H15V0Z" fill="white" />
                          </mask>
                          <g mask="url(#mask0_6254_8911)">
                            <path d="M8.4375 10.4688H6.5625C4.84375 10.4688 3.59375 9.21875 3.59375 7.5V4.325C3.59375 3.40625 4.34376 2.65625 5.26251 2.65625H9.74375C10.6625 2.65625 11.4125 3.40625 11.4125 4.325V7.5C11.4062 9.21875 10.1562 10.4688 8.4375 10.4688ZM5.26251 3.59375C4.86251 3.59375 4.53125 3.91875 4.53125 4.325V7.5C4.53125 8.5125 5.15625 9.53125 6.5625 9.53125H8.4375C9.84375 9.53125 10.4688 8.5125 10.4688 7.5V4.325C10.4688 3.925 10.1437 3.59375 9.7375 3.59375H5.26251Z" fill="#232529" />
                            <path d="M5.9375 3.59375C5.68125 3.59375 5.46875 3.38125 5.46875 3.125V1.25C5.46875 0.99375 5.68125 0.78125 5.9375 0.78125C6.19375 0.78125 6.40625 0.99375 6.40625 1.25V3.125C6.40625 3.38125 6.19375 3.59375 5.9375 3.59375Z" fill="#232529" />
                            <path d="M9.0625 3.59375C8.80625 3.59375 8.59375 3.38125 8.59375 3.125V1.25C8.59375 0.99375 8.80625 0.78125 9.0625 0.78125C9.31875 0.78125 9.53125 0.99375 9.53125 1.25V3.125C9.53125 3.38125 9.31875 3.59375 9.0625 3.59375Z" fill="#232529" />
                            <path d="M7.5 14.2188C7.24375 14.2188 7.03125 14.0062 7.03125 13.75V10C7.03125 9.74375 7.24375 9.53125 7.5 9.53125C7.75625 9.53125 7.96875 9.74375 7.96875 10V13.75C7.96875 14.0062 7.75625 14.2188 7.5 14.2188Z" fill="#232529" />
                          </g>
                        </g>
                        <defs>
                          <clipPath id="clip0_6254_8911">
                            <rect width="15" height="15" fill="white" />
                          </clipPath>
                        </defs>
                      </svg>

                      <Link href="/industries/electrician/" onClick={onLinkClick} className="SolutionLink text-[13px] hover:text-[#7824B1] font-medium text-[#232529] hover:text-[#232529]" style={{ fontFamily: 'SFPRO, sans-serif' }}>Electrical</Link>
                    </li>
                    <li className="SolutionBoxItem flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_6254_8939)">
                          <mask id="mask0_6254_8939" maskUnits="userSpaceOnUse" x="0" y="0" width="15" height="15">
                            <path d="M15 0H0V15H15V0Z" fill="white" />
                          </mask>
                          <g mask="url(#mask0_6254_8939)">
                            <path d="M11.475 14.2189H3.49379C2.54379 14.2189 1.67504 13.7376 1.17504 12.9251C0.675039 12.1126 0.631289 11.1251 1.05629 10.2689L2.13129 8.11263C2.48129 7.41263 3.04379 6.97513 3.67504 6.90638C4.30629 6.83763 4.95004 7.15013 5.43754 7.75638L5.57504 7.93138C5.85004 8.26888 6.16879 8.45013 6.48128 8.41888C6.79378 8.39388 7.08128 8.16888 7.29378 7.78763L8.47503 5.65639C8.96253 4.77514 9.61253 4.31889 10.3188 4.35014C11.0188 4.38764 11.6188 4.91264 12.0188 5.83764L13.975 10.4064C14.3375 11.2501 14.25 12.2126 13.7438 12.9814C13.2438 13.7626 12.3938 14.2189 11.475 14.2189ZM3.85004 7.84388C3.82504 7.84388 3.80004 7.84388 3.77504 7.85013C3.46254 7.88138 3.17504 8.13138 2.96879 8.53763L1.89379 10.6939C1.61254 11.2501 1.64379 11.9064 1.96879 12.4376C2.29379 12.9689 2.86879 13.2876 3.49379 13.2876H11.4688C12.0813 13.2876 12.625 12.9939 12.9625 12.4814C13.3 11.9689 13.3563 11.3564 13.1125 10.7939L11.1563 6.22514C10.9188 5.66264 10.5875 5.31889 10.2688 5.30639C9.97503 5.28764 9.59378 5.60014 9.29378 6.13139L8.11253 8.26263C7.75003 8.91263 7.18128 9.31888 6.56253 9.37513C5.94379 9.42513 5.31254 9.12513 4.84379 8.53763L4.70629 8.36263C4.44379 8.01888 4.14379 7.84388 3.85004 7.84388Z" fill="#232529" />
                            <path d="M4.35596 5.46875C3.06846 5.46875 2.01221 4.41875 2.01221 3.125C2.01221 1.83125 3.06221 0.78125 4.35596 0.78125C5.64971 0.78125 6.69973 1.83125 6.69973 3.125C6.69973 4.41875 5.64971 5.46875 4.35596 5.46875ZM4.35596 1.71875C3.58096 1.71875 2.94971 2.35 2.94971 3.125C2.94971 3.9 3.58096 4.53125 4.35596 4.53125C5.13096 4.53125 5.76221 3.9 5.76221 3.125C5.76221 2.35 5.13096 1.71875 4.35596 1.71875Z" fill="#232529" />
                          </g>
                        </g>
                        <defs>
                          <clipPath id="clip0_6254_8939">
                            <rect width="15" height="15" fill="white" />
                          </clipPath>
                        </defs>
                      </svg>

                      <Link href="/industries/landscaping/" onClick={onLinkClick} className="SolutionLink text-[13px] hover:text-[#7824B1] font-medium text-[#232529] hover:text-[#232529]" style={{ fontFamily: 'SFPRO, sans-serif' }}>Landscaping</Link>
                    </li>
                  </ul>
                </div>

                {/* Right Column */}
                <div className="SolutionMenuBox">
                  <ul className="SolutionBoxList space-y-2">
                    <li className="SolutionBoxItem flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_6254_8971)">
                          <mask id="mask0_6254_8971" maskUnits="userSpaceOnUse" x="0" y="0" width="15" height="15">
                            <path d="M15 0H0V15H15V0Z" fill="white" />
                          </mask>
                          <g mask="url(#mask0_6254_8971)">
                            <path d="M6.07863 3.32509L3.82353 4.69663L2.79489 3.0086C2.41904 2.38877 2.61685 1.57113 3.23667 1.19528C3.8565 0.81943 4.67414 1.01724 5.04999 1.63706L6.07863 3.32509Z" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M7.36455 5.62667L5.28089 6.8927C4.06761 7.63121 3.69835 9.12143 4.2852 10.3083L5.63695 13.0646C6.07214 13.9547 7.12717 14.2647 7.97119 13.7437L12.211 11.1655C13.0617 10.6512 13.2661 9.57641 12.6792 8.77855L10.8527 6.31244C10.0614 5.24426 8.57782 4.88816 7.36455 5.62667Z" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M6.66329 2.94873L3.28418 5.00642L4.65597 7.25912L8.03508 5.20146L6.66329 2.94873Z" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M9.00684 10.6719L10.0948 12.4589" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M7.31836 11.6992L8.40635 13.4862" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M10.6943 9.64307L11.7823 11.43" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                          </g>
                        </g>
                        <defs>
                          <clipPath id="clip0_6254_8971">
                            <rect width="15" height="15" fill="white" />
                          </clipPath>
                        </defs>
                      </svg>

                      <Link href="/industries/cleaning-business/" onClick={onLinkClick} className="SolutionLink text-[13px] hover:text-[#7824B1] font-medium text-[#232529] hover:text-[#232529]" style={{ fontFamily: 'SFPRO, sans-serif' }}>Cleaning Services</Link>
                    </li>
                    <li className="SolutionBoxItem flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_6262_9075)">
                          <mask id="mask0_6262_9075" maskUnits="userSpaceOnUse" x="0" y="0" width="15" height="15">
                            <path d="M15 0H0V15H15V0Z" fill="white" />
                          </mask>
                          <g mask="url(#mask0_6262_9075)">
                            <path d="M7.49954 14.8047C7.33649 14.8047 7.1735 14.7843 7.01723 14.7367C3.4911 13.7652 0.936523 10.4701 0.936523 6.8964V3.91379C0.936523 3.15285 1.48685 2.33081 2.19343 2.03866L5.97772 0.489601C6.96288 0.0887527 8.04307 0.0887527 9.02141 0.489601L12.8057 2.03866C13.5123 2.33081 14.0627 3.15285 14.0627 3.91379V6.8964C14.0627 10.4633 11.5012 13.7584 7.98192 14.7367C7.82566 14.7843 7.6626 14.8047 7.49954 14.8047ZM7.49954 1.21657C7.11228 1.21657 6.73182 1.29132 6.36494 1.44079L2.5807 2.9898C2.25458 3.12568 1.95563 3.5673 1.95563 3.92059V6.90319C1.95563 10.0217 4.19767 12.9023 7.28893 13.7584C7.42481 13.7991 7.57428 13.7991 7.71016 13.7584C10.8014 12.9023 13.0436 10.0217 13.0436 6.90319V3.92059C13.0436 3.5673 12.7445 3.12568 12.4184 2.9898L8.63422 1.44079C8.26734 1.29132 7.8868 1.21657 7.49954 1.21657Z" fill="#232529" />
                            <path d="M8.9873 6.51256L10.1556 5.92264C10.219 5.89064 10.2589 5.8257 10.2589 5.75471V3.93213" stroke="#232529" stroke-width="0.62709" stroke-linecap="round" />
                            <path d="M6.01172 6.51256L4.84342 5.92264C4.78005 5.89064 4.74009 5.8257 4.74009 5.75471V3.93213" stroke="#232529" stroke-width="0.62709" stroke-linecap="round" />
                            <path d="M9.16016 7.49316L10.6576 8.07604C10.7299 8.10417 10.7775 8.17379 10.7775 8.25135V9.30798" stroke="#232529" stroke-width="0.62709" stroke-linecap="round" />
                            <path d="M5.83887 7.49316L4.34142 8.07604C4.26914 8.10417 4.22154 8.17379 4.22154 8.25135V9.30798" stroke="#232529" stroke-width="0.62709" stroke-linecap="round" />
                            <path d="M9 9.25879L9.93537 9.79881C9.99357 9.83241 10.0294 9.89452 10.0294 9.96173V11.0612" stroke="#232529" stroke-width="0.62709" stroke-linecap="round" />
                            <path d="M5.99902 9.25879L5.06366 9.79881C5.00545 9.83241 4.96959 9.89452 4.96959 9.96173V11.0612" stroke="#232529" stroke-width="0.62709" stroke-linecap="round" />
                            <path d="M5.84375 6.60753C5.84375 6.55192 5.88883 6.50684 5.94444 6.50684H9.00313C9.05874 6.50684 9.10382 6.55192 9.10382 6.60753V8.60245C9.10382 9.50269 8.37403 10.2325 7.47379 10.2325V10.2325C6.57354 10.2325 5.84375 9.50269 5.84375 8.60244V6.60753Z" stroke="#232529" stroke-width="0.62709" />
                            <path d="M6.37012 5.97035C6.37012 5.36082 6.86424 4.8667 7.47376 4.8667V4.8667C8.08329 4.8667 8.57741 5.36082 8.57741 5.97035V6.63325H6.37012L6.37012 5.97035Z" stroke="#232529" stroke-width="0.62709" />
                          </g>
                        </g>
                        <defs>
                          <clipPath id="clip0_6262_9075">
                            <rect width="15" height="15" fill="white" />
                          </clipPath>
                        </defs>
                      </svg>

                      <Link href="/industries/pest-control/" onClick={onLinkClick} className="SolutionLink text-[14px] font-medium hover:text-[#7824B1] text-[#232529] hover:text-[#232529]" style={{ fontFamily: 'SFPRO, sans-serif' }}>Pest Control</Link>
                    </li>
                    <li className="SolutionBoxItem flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.91915 10.0423C0.792307 11.1565 1.80089 13.2784 7.27845 13.2784C9.99813 13.2784 15.3612 12.2871 12.9974 9.94873" stroke="#232529" stroke-linecap="round" />
                        <path d="M7.12598 10.5586C8.74848 10.5502 12.179 10.4392 12.9212 10.063C13.8489 9.59264 12.8957 3.70853 9.21022 2.74268" stroke="#232529" stroke-linecap="round" />
                        <path d="M7.85156 10.5586C6.22906 10.5502 2.79852 10.4392 2.05637 10.063C1.12867 9.59264 2.08182 3.70853 5.76732 2.74268" stroke="#232529" stroke-linecap="round" />
                        <path d="M6.44738 6.02633L5.7993 2.2365C5.76827 2.05501 5.90654 1.88865 6.09064 1.88596L8.86324 1.84557C9.05031 1.84284 9.19422 2.01015 9.16355 2.19471L8.52702 6.02495C8.50297 6.16967 8.37779 6.27576 8.23108 6.27576H6.74309C6.59692 6.27576 6.47202 6.17041 6.44738 6.02633Z" stroke="#232529" stroke-linecap="round" />
                      </svg>

                      <Link href="#" onClick={onLinkClick} className="SolutionLink text-[14px] font-medium hover:text-[#7824B1] text-[#232529] hover:text-[#232529]" style={{ fontFamily: 'SFPRO, sans-serif' }}>General Contractors</Link>
                    </li>
                    <li className="SolutionBoxItem flex items-start">
                      <svg className="SolutionBoxIcon mr-3 h-4 w-4 text-[#232529] mt-0.5" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_6262_9060)">
                          <mask id="mask0_6262_9060" maskUnits="userSpaceOnUse" x="0" y="0" width="15" height="15">
                            <path d="M15 0H0V15H15V0Z" fill="white" />
                          </mask>
                          <g mask="url(#mask0_6262_9060)">
                            <path d="M5.625 13.75H9.375C12.5 13.75 13.75 12.5 13.75 9.375V5.625C13.75 2.5 12.5 1.25 9.375 1.25H5.625C2.5 1.25 1.25 2.5 1.25 5.625V9.375C1.25 12.5 2.5 13.75 5.625 13.75Z" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M9.99805 7.5H10.0036" stroke="#232529" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M7.49707 7.5H7.5027" stroke="#232529" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M4.99609 7.5H5.00171" stroke="#232529" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                          </g>
                        </g>
                        <defs>
                          <clipPath id="clip0_6262_9060">
                            <rect width="15" height="15" fill="white" />
                          </clipPath>
                        </defs>
                      </svg>

                      <Link href="#" onClick={onLinkClick} className="SolutionLink text-[14px] font-medium hover:text-[#7824B1] text-[#232529] hover:text-[#232529]" style={{ fontFamily: 'SFPRO, sans-serif' }}>More Industry</Link>
                    </li>
                  </ul>
                </div>
              </div>
            )}
          </div>

          {/* Right Column - Customer Stories */}
          <div className="SolutionAiFeaturesColumn min-[1023px]:rounded-2xl  hidden min-[1023px]:block p-4 min-[1023px]:p-[15px] col-span-1 min-[1023px]:col-span-3 border-l border-dotted border-gray-300" style={{ background: 'linear-gradient(43deg, #ffffff 70%, #BD5DD0 138%)' }}>
            <div className="SolutionAiFeaturesBox relative rounded-xl p-6 border-l-2 border-l-dotted border-l-purple-200 border-t border-r border-b border-purple-100 flex flex-col justify-between h-full" style={{ background: 'linear-gradient(215deg, #ffffff 80%, #BD5DD0 149%)' }}>
              <div className="SolutionAiFeaturesTop">
                <h3 className="SolutionAiFeaturesHeader sf-pro-font text-[14px] font-semibold text-[#000000] mb-4" style={{ fontFamily: 'SFPRO, sans-serif' }}>Customer Stories</h3>
                <div className="SolutionStoryContent mb-4">
                  <p className="SolutionStoryText sf-pro-font text-[14px] text-gray-700 italic mb-4" style={{ fontFamily: 'SFPRO, sans-serif', lineHeight: '20px' }}>
                    FieldCamp transformed how we schedule jobs—now everything runs smoother and faster!
                  </p>
                  {activeTab === 'use-case' && (
                    <div className="SolutionStoryAuthor items-center">
                      <div className="SolutionAuthorAvatar w-12 h-12 rounded-full bg-gray-300 items-center justify-center overflow-hidden">
                        <img 
                          src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/testimonial-elips.png" 
                          alt="James W." 
                          className="w-full h-full object-cover"
                        />
                      </div>
                      <div className="SolutionAuthorInfo">
                        <p className="SolutionAuthorName sf-pro-font text-[14px] pb-0 font-medium text-gray-900 mb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>James W.,</p>
                        <p className="SolutionAuthorTitle sf-pro-font leading-[1.2] text-[13px] text-gray-600 pb-0" style={{ fontFamily: 'SFPRO, sans-serif' }}>Operations Manager at HomeFix Pros</p>
                      </div>
                    </div>
                  )}
                </div>
              </div>

              <button className="SolutionAiFeaturesButton sf-pro-font w-full py-2.5 px-4 rounded-lg transition-all duration-200 text-[14px] font-medium text-gray-700" style={{ backgroundColor: '#EAEDFB', fontFamily: 'SFPRO, sans-serif' }}>
                See More
              </button>
            </div>
          </div>
        </div>

        {/* Bottom Links - Right Aligned */}
        <div className="SolutionBottomLinksContainer hidden min-[1023px]:flex p-4 border-t border-gray-200 justify-end space-x-8">
          <Link href="#" onClick={onLinkClick} className="SolutionBottomLink sf-pro-font flex items-center text-[13px] text-black-600 hover:text-gray-900">
            <svg className="SolutionBottomLinkIcon mr-2 h-4 w-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g clip-path="url(#clip0_7552_2350)">
                <mask id="mask0_7552_2350" maskUnits="userSpaceOnUse" x="0" y="0" width="16" height="16">
                  <path d="M15.5 0.75H0.5V15.75H15.5V0.75Z" fill="white" />
                </mask>
                <g mask="url(#mask0_7552_2350)">
                  <path d="M11.7375 7.49375V9.99375C11.7375 10.1562 11.7312 10.3125 11.7125 10.4625C11.5687 12.15 10.575 12.9875 8.74375 12.9875H8.49375C8.3375 12.9875 8.1875 13.0625 8.09375 13.1875L7.34375 14.1875C7.0125 14.6312 6.475 14.6312 6.14375 14.1875L5.39374 13.1875C5.31249 13.0812 5.13125 12.9875 4.99375 12.9875H4.74376C2.75001 12.9875 1.75 12.4937 1.75 9.99375V7.49375C1.75 5.66251 2.59376 4.66876 4.27501 4.52501C4.42501 4.50626 4.58126 4.5 4.74376 4.5H8.74375C10.7375 4.5 11.7375 5.50001 11.7375 7.49375Z" stroke="#232529" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M14.2376 4.99376V7.49375C14.2376 9.33125 13.3939 10.3187 11.7126 10.4625C11.7314 10.3125 11.7376 10.1562 11.7376 9.99375V7.49375C11.7376 5.50001 10.7376 4.5 8.74387 4.5H4.7439C4.5814 4.5 4.42515 4.50626 4.27515 4.52501C4.4189 2.84376 5.41265 2 7.24387 2H11.2439C13.2376 2 14.2376 3.00001 14.2376 4.99376Z" stroke="#232529" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M8.93457 9.03125H8.9402" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M6.74707 9.03125H6.7527" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M4.55957 9.03125H4.5652" stroke="#232529" stroke-linecap="round" stroke-linejoin="round" />
                </g>
              </g>
              <defs>
                <clipPath id="clip0_7552_2350">
                  <rect width="15" height="15" fill="white" transform="translate(0.5 0.75)" />
                </clipPath>
              </defs>
            </svg>

            Contact sales
          </Link>
          <Link href="#" onClick={onLinkClick} className="SolutionBottomLink sf-pro-font flex items-center text-[13px] text-black-600 hover:text-gray-900">
            <svg className="SolutionBottomLinkIcon mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Watch demo
          </Link>
          <Link href="#" onClick={onLinkClick} className="SolutionBottomLink sf-pro-font flex items-center text-[13px] text-black-600 hover:text-gray-900">
            <svg className="SolutionBottomLinkIcon mr-2 h-4 w-4" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g clip-path="url(#clip0_7552_511)">
                <mask id="mask0_7552_511" maskUnits="userSpaceOnUse" x="0" y="0" width="16" height="16">
                  <path d="M15.8765 0.75H0.876465V15.75H15.8765V0.75Z" fill="white" />
                </mask>
                <g mask="url(#mask0_7552_511)">
                  <path d="M10.2515 14.9688H6.50146C3.74521 14.9688 2.90771 14.1313 2.90771 11.375V5.125C2.90771 2.36875 3.74521 1.53125 6.50146 1.53125H10.2515C13.0077 1.53125 13.8452 2.36875 13.8452 5.125V11.375C13.8452 14.1313 13.0077 14.9688 10.2515 14.9688ZM6.50146 2.46875C4.26396 2.46875 3.84521 2.89375 3.84521 5.125V11.375C3.84521 13.6062 4.26396 14.0312 6.50146 14.0312H10.2515C12.489 14.0312 12.9077 13.6062 12.9077 11.375V5.125C12.9077 2.89375 12.489 2.46875 10.2515 2.46875H6.50146Z" fill="#232529" />
                  <path d="M9.62646 4.65625H7.12646C6.87021 4.65625 6.65771 4.44375 6.65771 4.1875C6.65771 3.93125 6.87021 3.71875 7.12646 3.71875H9.62646C9.88271 3.71875 10.0952 3.93125 10.0952 4.1875C10.0952 4.44375 9.88271 4.65625 9.62646 4.65625Z" fill="#232529" />
                  <path d="M8.3765 13.1626C7.58275 13.1626 6.93896 12.5188 6.93896 11.7251C6.93896 10.9313 7.58275 10.2876 8.3765 10.2876C9.17025 10.2876 9.814 10.9313 9.814 11.7251C9.814 12.5188 9.17025 13.1626 8.3765 13.1626ZM8.3765 11.2188C8.1015 11.2188 7.8765 11.4438 7.8765 11.7188C7.8765 11.9938 8.1015 12.2188 8.3765 12.2188C8.6515 12.2188 8.8765 11.9938 8.8765 11.7188C8.8765 11.4438 8.6515 11.2188 8.3765 11.2188Z" fill="#232529" />
                </g>
              </g>
              <defs>
                <clipPath id="clip0_7552_511">
                  <rect width="15" height="15" fill="white" transform="translate(0.876465 0.75)" />
                </clipPath>
              </defs>
            </svg>

            Download apps
          </Link>
        </div>
      </div>
    </div>
  );
};