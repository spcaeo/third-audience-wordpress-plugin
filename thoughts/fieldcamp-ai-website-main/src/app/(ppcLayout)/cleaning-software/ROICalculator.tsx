'use client';

import React, { useState, useEffect } from 'react';

export default function ROICalculator() {
  const [techs, setTechs] = useState(5);
  const [jobs, setJobs] = useState(50);
  const [avgJobValue, setAvgJobValue] = useState(150);
  const [adminHours, setAdminHours] = useState(20);
  const [showAdvanced, setShowAdvanced] = useState(false);
  const [results, setResults] = useState({
    totalValue: 0,
    hoursSaved: 0,
    revenueIncrease: 0,
    investment: 0,
    breakdownData: {
      revenueIncrease: 0,
      hoursSaved: 0,
      milesSaved: 0,
      moreJobs: 0,
      overheadReduced: 0,
      betterReviews: 0.3
    },
    roi: 0
  });

  useEffect(() => {
    const calculateROI = () => {
      // Time Savings
      const hoursPerTechSaved = 2.3; // per week
      const adminTimeSaved = adminHours * 0.73; // 73% reduction
      const totalHoursSaved = (techs * hoursPerTechSaved) + adminTimeSaved;
      const hourlyValue = 35; // $ per hour

      // Revenue Increases
      const jobsIncrease = jobs * 0.23; // 23% more jobs fit in
      const revenueIncrease = jobsIncrease * avgJobValue;

      // Cost Reductions
      const missedJobsSaved = jobs * 0.04 * avgJobValue; // 4% no-show reduction
      const overtimeSaved = techs * 312; // avg monthly OT savings
      const gasOptimization = techs * 47; // route optimization

      // Efficiency Gains
      const fasterInvoicing = jobs * 4 * 0.15; // 15 min saved per invoice
      const customerRetention = (jobs * 4 * avgJobValue * 0.02); // 2% better retention

      // Total Monthly Value
      const totalValue = Math.round(
        (totalHoursSaved * hourlyValue) + 
        revenueIncrease + 
        missedJobsSaved + 
        overtimeSaved + 
        gasOptimization + 
        (fasterInvoicing * hourlyValue / 60) +
        customerRetention
      );

      const investment = Math.round(techs * 39.99); // dynamic pricing: $39.99 per technician
      const roi = Math.round((totalValue / investment) * 10) / 10;

      setResults({
        totalValue,
        hoursSaved: Math.round(totalHoursSaved * 4.33), // monthly
        revenueIncrease: Math.round(revenueIncrease),
        investment,
        breakdownData: {
          revenueIncrease: Math.round(revenueIncrease),
          hoursSaved: Math.round(totalHoursSaved * 4.33),
          milesSaved: Math.round(techs * 180), // monthly miles saved
          moreJobs: Math.round(jobsIncrease * 4.33), // monthly extra jobs
          overheadReduced: Math.round(overtimeSaved + gasOptimization),
          betterReviews: 0.3
        },
        roi
      });
    };

    const timeoutId = setTimeout(calculateROI, 300); // Debounce
    return () => clearTimeout(timeoutId);
  }, [techs, jobs, avgJobValue, adminHours]);

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
            Based on data from 500+ cleaning companies
          </div>
        </div>

        <div className="bg-white shadow-lg border-2 border-gray-300 hover:border-gray-400 hover:shadow-xl transition-all duration-300 calculator-container">
          
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            {/* Input Section */}
            <div className="space-y-8">
              <h3 className="text-[24px] font-bold text-gray-800 mb-8">Your Business Details</h3>
              
              {/* Number of Cleaning Technicians */}
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <label className="text-[16px] font-medium text-gray-800 flex items-center gap-2">
                    Number of Cleaning Technicians
                    <div className="group relative">
                      <span className="cursor-help text-gray-400">(i)</span>
                      <div className="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                        Include all cleaning staff, full and part-time
                      </div>
                    </div>
                  </label>
                  <span className="bg-black text-white px-3 py-1 rounded-full font-bold">{techs}</span>
                </div>
                <input 
                  type="range" 
                  min="1" 
                  max="50" 
                  value={techs}
                  onChange={(e) => setTechs(Number(e.target.value))}
                  className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                />
                <div className="flex justify-between text-xs text-gray-500">
                  <span>1</span>
                  <span>50</span>
                </div>
              </div>

              {/* Jobs Completed Per Week */}
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <label className="text-[16px] font-medium text-gray-800 flex items-center gap-2">
                    Jobs Completed Per Week
                    <div className="group relative">
                      <span className="cursor-help text-gray-400">(i)</span>
                      <div className="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                        Total jobs across all teams
                      </div>
                    </div>
                  </label>
                  <span className="bg-black text-white px-3 py-1 rounded-full font-bold">{jobs}</span>
                </div>
                <input 
                  type="range" 
                  min="10" 
                  max="300" 
                  step="5"
                  value={jobs}
                  onChange={(e) => setJobs(Number(e.target.value))}
                  className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                />
                <div className="flex justify-between text-xs text-gray-500">
                  <span>10</span>
                  <span>300</span>
                </div>
              </div>

              {/* Average Job Value */}
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <label className="text-[16px] font-medium text-gray-800 flex items-center gap-2">
                    Average Job Value
                    <div className="group relative">
                      <span className="cursor-help text-gray-400">(i)</span>
                      <div className="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                        Your typical invoice amount
                      </div>
                    </div>
                  </label>
                  <span className="bg-black text-white px-3 py-1 rounded-full font-bold">${avgJobValue}</span>
                </div>
                <input 
                  type="range" 
                  min="50" 
                  max="500" 
                  step="10"
                  value={avgJobValue}
                  onChange={(e) => setAvgJobValue(Number(e.target.value))}
                  className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                />
                <div className="flex justify-between text-xs text-gray-500">
                  <span>$50</span>
                  <span>$500</span>
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
                        Time on scheduling, invoicing, phone calls
                      </div>
                    </div>
                  </label>
                  <span className="bg-black text-white px-3 py-1 rounded-full font-bold">{adminHours}h</span>
                </div>
                <input 
                  type="range" 
                  min="5" 
                  max="60" 
                  value={adminHours}
                  onChange={(e) => setAdminHours(Number(e.target.value))}
                  className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                />
                <div className="flex justify-between text-xs text-gray-500">
                  <span>5h</span>
                  <span>60h</span>
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
                    +{results.breakdownData.moreJobs}
                  </div>
                  <div className="text-sm text-gray-600">More Jobs Booked</div>
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