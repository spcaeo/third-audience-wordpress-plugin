'use client';

import { useState } from "react";

export default function HoverFeaturepage() {
    const [imageOpacity, setImageOpacity] = useState<number>(1);
    const [featureImage, setFeatureImage] = useState<string>("https://cms.fieldcamp.ai/wp-content/uploads/2025/08/service-area-management-banner-image.svg");

    const handleFeatureHover = (imgUrl: string) => {
        // Add smooth fade effect
        setImageOpacity(0);
        setTimeout(() => {
           setFeatureImage(imgUrl);
           setImageOpacity(1);
        }, 200);
     };
    return (
        <section className="py-20 px-4 automate-task">
        <div className="max-w-7xl mx-auto">
            <div className="text-center mb-16">
                <h2 className="text-3xl lg:text-4xl xl:text-5xl font-bold text-gray-900 mb-6">
                    Validated Service Zones for<br></br>
                    Optimal Efficiency
                </h2>
                <p className="text-lg lg:text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                    Instant geographic validation between booking requests and defined boundaries prevents
                    unprofitable jobs for field teams.
                </p>
            </div>

            <div className="grid lg:grid-cols-2 gap-12 items-center">
                <div className="space-y-8">
                    <div 
                        className="feature-card cursor-pointer" 
                        onMouseEnter={() => handleFeatureHover("https://cms.fieldcamp.ai/wp-content/uploads/2025/08/radius-coverage.svg")}>
                        <h3 className="text-xl font-bold mb-3 text-gray-900">Set Radius Coverage Instantly</h3>
                        <p className="text-gray-600 leading-relaxed">
                            Define precise distance boundaries from your central location with visual map
                            confirmation. System instantly checks customer addresses against your radius zones
                            for clear, circular coverage areas.
                        </p>
                    </div>

                    <div 
                        className="feature-card cursor-pointer" 
                        onMouseEnter={() => handleFeatureHover("https://cms.fieldcamp.ai/wp-content/uploads/2025/08/polygon-mapping.svg")}>
                        <h3 className="text-xl font-bold mb-3 text-gray-900">Draw Polygon Boundaries Precisely</h3>
                        <p className="text-gray-600 leading-relaxed">
                            Create custom territory shapes by clicking points on interactive maps. Customer
                            addresses get validated against hand-drawn boundaries that follow neighborhood lines
                            and avoid problem zones.
                        </p>
                    </div>

                    <div 
                        className="feature-card cursor-pointer" 
                        onMouseEnter={() => handleFeatureHover("https://cms.fieldcamp.ai/wp-content/uploads/2025/08/zip-code.svg")}>
                        <h3 className="text-xl font-bold mb-3 text-gray-900">Manage Zip Code Areas Automatically
                        </h3>
                        <p className="text-gray-600 leading-relaxed">
                            Enter specific postal codes or entire cities to define exact service territories.
                            Customer addresses get instantly checked against your zip code database with
                            automatic USPS confirmation.
                        </p>
                    </div>
                </div>

                <div>
                   <img
                      src={featureImage}
                      alt="Feature preview"
                      className="w-full h-full object-cover transition-opacity duration-200"
                      style={{ opacity: imageOpacity }}
                      id="feature-image"
                   />
                </div>
            </div>


            
            <div className="mt-16">
                <div className="bg-green-50 border border-green-200 rounded-2xl p-8 text-center">
                    <h3 className="text-2xl lg:text-3xl font-bold text-green-700 mb-4">From Wasted Miles to
                        Profitable Smiles</h3>
                    <p className="text-lg text-green-700 mb-6 max-w-2xl mx-auto">Avoid long drives, failed trips,
                        and missed opportunities. FieldCamp keeps your team inside money-making zones.</p>
                    <a href="https://calendly.com/jeel-fieldcamp/30min" className="calendly-open btn-green-primary text-lg">Book a
                        Free Demo Now</a>
                </div>
            </div>
        </div>
       </section>   
    )
}            