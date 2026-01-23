'use client';
import { useState } from "react";

export default function ClickFeaturepage() {
    const [activeStep, setActiveStep] = useState<number>(1);
    const getStepClasses = (stepNum: number) => {
        const baseClasses = "booking-step cursor-pointer transition-all duration-300 p-6 rounded-xl border-2 border-gray-200";
        
        if (stepNum === activeStep) {
           switch (stepNum) {
              case 1:
                 return `${baseClasses} ring-2 ring-offset-2 ring-green-400 bg-gradient-to-r from-green-100 to-green-50 border-green-400`;
              case 2:
                 return `${baseClasses} ring-2 ring-offset-2 ring-blue-400 bg-gradient-to-r from-blue-100 to-blue-50 border-blue-400`;
              case 3:
                 return `${baseClasses} ring-2 ring-offset-2 ring-purple-400 bg-gradient-to-r from-purple-100 to-purple-50 border-purple-400`;
              case 4:
                 return `${baseClasses} ring-2 ring-offset-2 ring-yellow-400 bg-gradient-to-r from-yellow-100 to-yellow-50 border-yellow-400`;
              case 5:
                 return `${baseClasses} ring-2 ring-offset-2 ring-red-400 bg-gradient-to-r from-red-100 to-red-50 border-red-400`;
              default:
                 return baseClasses;
           }
        } else {
           const hoverClasses = {
              1: 'hover:border-green-400',
              2: 'hover:border-blue-400',
              3: 'hover:border-purple-400',
              4: 'hover:border-yellow-400',
              5: 'hover:border-red-400'
           };
           return `${baseClasses} ${hoverClasses[stepNum as keyof typeof hoverClasses] || ''} bg-gradient-to-r ${stepNum === 1 ? 'from-green-50 to-transparent' : stepNum === 2 ? 'from-blue-50 to-transparent' : stepNum === 3 ? 'from-purple-50 to-transparent' : stepNum === 4 ? 'from-yellow-50 to-transparent' : 'from-red-50 to-transparent'}`;
        }
     };
     const showBookingStep = (stepNumber: number) => {
        setActiveStep(stepNumber);
     };

     const getStepNumberClasses = (stepNum: number) => {
        const baseClasses = "w-10 h-10 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0";
        
        if (stepNum === activeStep) {
           switch (stepNum) {
              case 1:
                 return `${baseClasses} bg-green-500`;
              case 2:
                 return `${baseClasses} bg-blue-500`;
              case 3:
                 return `${baseClasses} bg-purple-500`;
              case 4:
                 return `${baseClasses} bg-yellow-500`;
              case 5:
                 return `${baseClasses} bg-red-500`;
              default:
                 return `${baseClasses} bg-gray-400`;
           }
        }
        return `${baseClasses} bg-gray-400`;
     };
    return (
        <section className="bg-white py-20 px-4">
        <div className="max-w-7xl mx-auto">
            <div className="text-center mb-16">
                <h2 className="text-3xl lg:text-4xl xl:text-5xl font-bold text-gray-900 mb-6">
                    So What Exactly Is Service Area Management for Field Service?
                </h2>
                <p className="text-lg lg:text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                    Think of it as your 24/7 geographic intelligence system that never sleeps, never gets
                    overwhelmed, and never forgets to check if you actually serve that area before accepting the
                    job.
                </p>
            </div>

            <div className="grid lg:grid-cols-2 gap-12 items-start">
                <div className="space-y-4">
                    <div 
                        className={getStepClasses(1)}
                        onClick={() => showBookingStep(1)}
                        data-step="1">
                        <div className="flex items-start gap-4">
                            <div
                                className={getStepNumberClasses(1)}>
                                1</div>
                            <div>
                                <h3 className="text-xl font-bold mb-2 text-gray-900">Define Your Work Areas</h3>
                                <p className="text-gray-600">You tell the system where you provide service - could
                                    be "20 miles from our shop" or specific neighborhoods you choose on a map.
                                </p>
                            </div>
                        </div>
                    </div>

                    
                    <div 
                        className={getStepClasses(2)}
                        onClick={() => showBookingStep(2)}
                        data-step="2">
                        <div className="flex items-start gap-4">
                            <div
                                className={getStepNumberClasses(2)}>
                                2</div>
                            <div>
                                <h3 className="text-xl font-bold mb-2 text-gray-900">Customer Enters Address</h3>
                                <p className="text-gray-600">When someone wants to book a service, they type in
                                    their address just like ordering pizza online.</p>
                            </div>
                        </div>
                    </div>

                    
                    <div 
                        className={getStepClasses(3)}
                        onClick={() => showBookingStep(3)}
                        data-step="3">
                        <div className="flex items-start gap-4">
                            <div
                                className={getStepNumberClasses(3)}>
                                3</div>
                            <div>
                                <h3 className="text-xl font-bold mb-2 text-gray-900">Instant Yes or No Answer</h3>
                                <p className="text-gray-600">The system immediately says "Great! We serve your area"
                                    or "Sorry, we don't cover that area yet" - no waiting, no callbacks.</p>
                            </div>
                        </div>
                    </div>

                    
                    <div 
                        className={getStepClasses(4)}
                        onClick={() => showBookingStep(4)}
                        data-step="4">
                        <div className="flex items-start gap-4">
                            <div
                                className={getStepNumberClasses(4)}>
                                4</div>
                            <div>
                                <h3 className="text-xl font-bold mb-2 text-gray-900">Smart Job Assignment</h3>
                                <p className="text-gray-600">If it's a "yes," the system automatically gives the job
                                    to the right technician who's closest and has the right skills.</p>
                            </div>
                        </div>
                    </div>

                    
                    {/* <div 
                        className={getStepClasses(5)}
                        onClick={() => showBookingStep(5)}
                        data-step="5">
                        <div className="flex items-start gap-4">
                            <div
                                className={getStepNumberClasses(5)}>
                                5</div>
                            <div>
                                <h3 className="text-xl font-bold mb-2 text-gray-900">Easy Changes When Needed</h3>
                                <p className="text-gray-600">You can easily change your service areas anytime - add
                                    new neighborhoods, remove problem areas, or expand when you grow.</p>
                            </div>
                        </div>
                    </div> */}

                </div>

                
                <div className="sticky top-8">
                
                    <div className="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-200">
                
                        <div className="bg-gray-100 px-4 py-3 flex items-center gap-2 border-b border-gray-200">
                            <div className="flex gap-2">
                                <div className="w-3 h-3 rounded-full bg-red-400"></div>
                                <div className="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div className="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <div className="flex-1 mx-4">
                                <div className="bg-white rounded-md px-3 py-1 text-sm text-gray-600">
                                    https://yourbusiness.com/book-online
                                </div>
                            </div>
                        </div>

                        
                        <div className="relative h-[500px] bg-gradient-to-br from-gray-50 to-white">
                        
                            <div id="mockup-step-1"
                                className={`mockup-screen absolute inset-0 p-8 transition-opacity duration-300 ${activeStep === 1 ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}>
                                <div className="aspect-[4/3] rounded-2xl overflow-hidden">
                                    <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/define-your-work-areas.svg" alt="Define Your Work Areas"
                                        className="w-full h-full object-cover" />
                                </div>
                            </div>

                        
                            <div id="mockup-step-2"
                                className={`mockup-screen absolute inset-0 p-8 transition-opacity duration-300 ${activeStep === 2 ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}>
                                <div className="aspect-[4/3] rounded-2xl overflow-hidden">
                                    <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/customer-enters-address.svg" alt="Customer Enters Address"
                                        className="w-full h-full object-cover" />
                                </div>
                            </div>


                            <div id="mockup-step-3"
                                className={`mockup-screen absolute inset-0 p-8 transition-opacity duration-300 ${activeStep === 3 ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}>
                                <div className="aspect-[4/3] rounded-2xl overflow-hidden">
                                    <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/instant-yes-or-no-answer.svg" alt="Instant Yes or No Answer"
                                        className="w-full h-full object-cover" />
                                </div>
                            </div>

                            
                            <div id="mockup-step-4"
                                className={`mockup-screen absolute inset-0 p-8 transition-opacity duration-300 ${activeStep === 4 ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}>
                                <div className="aspect-[4/3] rounded-2xl overflow-hidden">
                                    <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/smart-job-assignment.svg" alt="Smart Job Assignment"
                                        className="w-full h-full object-cover" />
                                </div>
                            </div>

                            
                            <div id="mockup-step-5"
                                className={`mockup-screen absolute inset-0 p-8 transition-opacity duration-300 ${activeStep === 5 ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}>
                                <div className="aspect-[4/3] rounded-2xl overflow-hidden">
                                    <img src="https://cms.fieldcamp.ai/wp-content/uploads/2025/08/easy-changes-when-needed.svg" alt="Easy Changes When Needed"
                                        className="w-full h-full object-cover" />
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
       </section>
    )
}