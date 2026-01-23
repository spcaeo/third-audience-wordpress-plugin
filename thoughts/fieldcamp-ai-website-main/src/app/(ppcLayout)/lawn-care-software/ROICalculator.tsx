'use client';

import React, { useState, useEffect } from 'react';

export default function ROICalculator() {
  const [crews, setCrews] = useState(3);
  const [properties, setProperties] = useState(40);
  const [avgPropertyValue, setAvgPropertyValue] = useState(85);
  const [adminHours, setAdminHours] = useState(18);
  const [results, setResults] = useState({
    totalValue: 0,
    hoursSaved: 0,
    revenueIncrease: 0,
    investment: 0,
    breakdownData: {
      revenueIncrease: 0,
      hoursSaved: 0,
      milesSaved: 0,
      moreProperties: 0,
      overheadReduced: 0,
      betterReviews: 0.3
    },
    roi: 0
  });

  useEffect(() => {
    const calculateROI = () => {
      // Time Savings
      const hoursPerCrewSaved = 2.8; // per week
      const adminTimeSaved = adminHours * 0.68; // 68% reduction
      const totalHoursSaved = (crews * hoursPerCrewSaved) + adminTimeSaved;
      const hourlyValue = 38; // $ per hour

      // Revenue Increases
      const propertiesIncrease = properties * 0.43; // 43% more properties fit in
      const revenueIncrease = propertiesIncrease * avgPropertyValue;

      // Cost Reductions
      const missedPropertiesSaved = properties * 0.035 * avgPropertyValue; // 3.5% no-show reduction
      const overtimeSaved = crews * 285; // avg monthly OT savings
      const fuelOptimization = crews * 52; // route optimization

      // Efficiency Gains
      const fasterInvoicing = properties * 4 * 0.12; // 12 min saved per invoice
      const customerRetention = (properties * 4 * avgPropertyValue * 0.025); // 2.5% better retention

      // Total Monthly Value
      const totalValue = Math.round(
        (totalHoursSaved * hourlyValue) + 
        revenueIncrease + 
        missedPropertiesSaved + 
        overtimeSaved + 
        fuelOptimization + 
        (fasterInvoicing * hourlyValue / 60) +
        customerRetention
      );

      const investment = Math.round(crews * 39.99); // dynamic pricing: $39.99 per crew
      const roi = Math.round((totalValue / investment) * 10) / 10;

      setResults({
        totalValue,
        hoursSaved: Math.round(totalHoursSaved * 4.33), // monthly
        revenueIncrease: Math.round(revenueIncrease),
        investment,
        breakdownData: {
          revenueIncrease: Math.round(revenueIncrease),
          hoursSaved: Math.round(totalHoursSaved * 4.33),
          milesSaved: Math.round(crews * 210), // monthly miles saved
          moreProperties: Math.round(propertiesIncrease * 4.33), // monthly extra properties
          overheadReduced: Math.round(overtimeSaved + fuelOptimization),
          betterReviews: 0.3
        },
        roi
      });
    };

    const timeoutId = setTimeout(calculateROI, 300); // Debounce
    return () => clearTimeout(timeoutId);
  }, [crews, properties, avgPropertyValue, adminHours]);

  return (
    <section className='roi-calculator-section py-160 bg-white'>
      <div className='container max-w-[1245px] mx-auto px-[15px] lg:px-[15px]'>
        
        {/* Header */}
        <div className="text-center mb-8">
          <h2 className="text-[32px] md:text-[36px] font-bold leading-[1.2] mb-4">
            Calculate Your Monthly Savings
          </h2>
          <p className="text-[18px] text-gray-600 mb-6">
            See your ROI in real numbers based on your business size
          </p>
          <div className="inline-flex items-center bg-gray-100 text-gray-700 px-4 py-2 rounded-full text-sm font-medium">
            Based on data from 400+ lawn care companies
          </div>
        </div>

        <div className="bg-white shadow-lg border-2 border-gray-300 hover:border-gray-400 hover:shadow-xl transition-all duration-300 calculator-container">
          
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            {/* Input Section */}
            <div className="space-y-8">
              <h3 className="text-[24px] font-bold text-gray-800 mb-8">Your Business Details</h3>
              
              {/* Number of Crews */}
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <label className="text-[16px] font-medium text-gray-800 flex items-center gap-2">
                    Number of Crews
                    <div className="group relative">
                      <span className="cursor-help text-gray-400">(i)</span>
                      <div className="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                        Include all crews, full and part-time
                      </div>
                    </div>
                  </label>
                  <span className="bg-black text-white px-3 py-1 rounded-full font-bold">{crews}</span>
                </div>
                <input 
                  type="range" 
                  min="1" 
                  max="25" 
                  value={crews}
                  onChange={(e) => setCrews(Number(e.target.value))}
                  className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                />
                <div className="flex justify-between text-xs text-gray-500">
                  <span>1</span>
                  <span>25</span>
                </div>
              </div>

              {/* Properties Per Week */}
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <label className="text-[16px] font-medium text-gray-800 flex items-center gap-2">
                    Properties Per Week
                    <div className="group relative">
                      <span className="cursor-help text-gray-400">(i)</span>
                      <div className="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                        Total properties across all crews
                      </div>
                    </div>
                  </label>
                  <span className="bg-black text-white px-3 py-1 rounded-full font-bold">{properties}</span>
                </div>
                <input 
                  type="range" 
                  min="10" 
                  max="200" 
                  step="5"
                  value={properties}
                  onChange={(e) => setProperties(Number(e.target.value))}
                  className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                />
                <div className="flex justify-between text-xs text-gray-500">
                  <span>10</span>
                  <span>200</span>
                </div>
              </div>

              {/* Average Property Value */}
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <label className="text-[16px] font-medium text-gray-800 flex items-center gap-2">
                    Average Property Value
                    <div className="group relative">
                      <span className="cursor-help text-gray-400">(i)</span>
                      <div className="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                        Your typical service amount
                      </div>
                    </div>
                  </label>
                  <span className="bg-black text-white px-3 py-1 rounded-full font-bold">${avgPropertyValue}</span>
                </div>
                <input 
                  type="range" 
                  min="25" 
                  max="200" 
                  step="5"
                  value={avgPropertyValue}
                  onChange={(e) => setAvgPropertyValue(Number(e.target.value))}
                  className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                />
                <div className="flex justify-between text-xs text-gray-500">
                  <span>$25</span>
                  <span>$200</span>
                </div>
              </div>

              {/* Hours Spent on Admin Weekly */}
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <label className="text-[16px] font-medium text-gray-800 flex items-center gap-2">
                    Hours Spent on Admin Weekly
                    <div className="group relative">
                      <span className="cursor-help text-gray-400">(i)</span>
                      <div className="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                        Time on routing, scheduling, invoicing
                      </div>
                    </div>
                  </label>
                  <span className="bg-black text-white px-3 py-1 rounded-full font-bold">{adminHours}h</span>
                </div>
                <input 
                  type="range" 
                  min="5" 
                  max="50" 
                  value={adminHours}
                  onChange={(e) => setAdminHours(Number(e.target.value))}
                  className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                />
                <div className="flex justify-between text-xs text-gray-500">
                  <span>5h</span>
                  <span>50h</span>
                </div>
              </div>

            </div>

            {/* Results Section */}
            <div className="space-y-8">
              
              {/* Primary Metric */}
              <div className="text-center bg-gray-50 rounded-2xl p-8 shadow-lg border border-gray-200">
                <div className="text-[20px] font-medium mb-2 text-gray-800">Your Monthly Value:</div>
                <div className="text-[48px] md:text-[60px] font-bold leading-none text-black">
                  ${results.totalValue.toLocaleString()}
                </div>
                <div className="text-gray-600 mt-4">
                  = {results.roi}x ROI on your investment
                </div>
              </div>

              {/* Breakdown Grid */}
              <div className="grid grid-cols-2 gap-4">
                <div className="bg-gray-50 rounded-xl p-4 text-center">
                  <div className="text-[24px] font-bold text-black">
                    ${results.breakdownData.revenueIncrease.toLocaleString()}
                  </div>
                  <div className="text-sm text-gray-600">Revenue Increase</div>
                </div>
                
                <div className="bg-gray-50 rounded-xl p-4 text-center">
                  <div className="text-[24px] font-bold text-black">
                    {results.breakdownData.hoursSaved}
                  </div>
                  <div className="text-sm text-gray-600">Hours Saved</div>
                </div>

                <div className="bg-gray-50 rounded-xl p-4 text-center">
                  <div className="text-[24px] font-bold text-black">
                    {results.breakdownData.milesSaved}
                  </div>
                  <div className="text-sm text-gray-600">Miles Saved</div>
                </div>

                <div className="bg-gray-50 rounded-xl p-4 text-center">
                  <div className="text-[24px] font-bold text-black">
                    +{results.breakdownData.moreProperties}
                  </div>
                  <div className="text-sm text-gray-600">More Properties</div>
                </div>

                <div className="bg-gray-50 rounded-xl p-4 text-center">
                  <div className="text-[24px] font-bold text-black">
                    ${results.breakdownData.overheadReduced.toLocaleString()}
                  </div>
                  <div className="text-sm text-gray-600">Overhead Reduced</div>
                </div>

                <div className="bg-gray-50 rounded-xl p-4 text-center">
                  <div className="text-[24px] font-bold text-black">
                    +{results.breakdownData.betterReviews}
                  </div>
                  <div className="text-sm text-gray-600">Better Reviews</div>
                </div>
              </div>

              {/* ROI Comparison */}
              <div className="bg-gray-50 rounded-xl p-6">
                <div className="flex items-center justify-between">
                  <div className="text-center">
                    <div className="text-sm text-gray-600">Your Investment</div>
                    <div className="text-[24px] font-bold text-gray-800">${results.investment}/mo</div>
                  </div>
                  <div className="text-2xl">vs</div>
                  <div className="text-center">
                    <div className="text-sm text-gray-600">Your Return</div>
                    <div className="text-[24px] font-bold text-black">
                      ${results.totalValue.toLocaleString()}/mo
                    </div>
                  </div>
                </div>
              </div>

            </div>

          </div>

          {/* CTA Section */}
          <div className="text-center space-y-4 mt-12">
            <a 
              href="https://calendly.com/jeel-fieldcamp/30min" 
              className="calendly-open inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-medium hover:opacity-90 transition-all transform hover:scale-105 shadow-lg"
            >
              See How to Achieve These Savings
            </a>
            <p className="text-sm text-gray-600">15-minute demo tailored to your business</p>
          </div>

        </div>

      </div>

      <style jsx>{`
        .calculator-container {
          border-radius: 12px;
          padding: 16px;
          max-width: 800px;
          margin: 0 auto;
        }
        
        @media (min-width: 768px) {
          .calculator-container {
            padding: 32px;
          }
        }
        
        .slider::-webkit-slider-thumb {
          appearance: none;
          height: 24px;
          width: 24px;
          border-radius: 50%;
          background: #374151;
          cursor: pointer;
          border: 3px solid white;
          box-shadow: 0 2px 6px rgba(0,0,0,0.2);
          transition: all 0.2s ease;
        }
        .slider::-webkit-slider-thumb:hover {
          background: #1f2937;
          transform: scale(1.1);
        }
        .slider::-webkit-slider-track {
          background: #e5e7eb;
          height: 12px;
          border-radius: 6px;
        }
        .slider::-moz-range-thumb {
          height: 24px;
          width: 24px;
          border-radius: 50%;
          background: #374151;
          cursor: pointer;
          border: 3px solid white;
          box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        .slider::-moz-range-track {
          background: #e5e7eb;
          height: 12px;
          border-radius: 6px;
        }
      `}</style>
    </section>
  );
}