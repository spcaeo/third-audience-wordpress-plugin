"use client";
import { useState, useEffect, useCallback } from "react";
import { Calculator as  Star, CheckCircle } from "lucide-react";
import Accordion from "@/app/_components/Accordion";
import { ResponsiveContainer, PieChart, Pie, Cell, Tooltip } from "recharts";

// CSS styles from HTML mockup
const styles = `
  .content-section {
    background: white;
    margin: 2rem 0;
    padding: 3rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  }
  .content-section p {
    margin-bottom: 1rem;
    color: #555;
    line-height: 1.7;
  }

  .content-section ul {
    margin: 1rem 0 1rem 2rem;
  }

  .content-section li {
    margin-bottom: 0.5rem;
    color: #555;
  }

  .example {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 12px;
    margin: 2rem 0;
    border-left: 4px solid #28a745;
  }

  .example h4 {
    color: #28a745;
    margin-bottom: 1rem;
  }

  .calculation-breakdown {
    font-family: 'Courier New', monospace;
    background: #fff;
    padding: 1rem;
    border-radius: 6px;
    margin-top: 1rem;
  }

  .pro-tips {
    background: #e8f4fd;
    border-left: 4px solid #007bff;
    padding: 1.5rem;
    margin: 2rem 0;
    border-radius: 0 8px 8px 0;
  }

  .pro-tips h4 {
    color: #007bff;
    margin-bottom: 1rem;
    font-weight: 600;
  }

  .industry-tabs {
    display: flex;
    margin-bottom: 2rem;
    border-bottom: 2px solid #e9ecef;
  }

  .tab-btn {
    padding: 1rem 2rem;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    color: #666;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
  }

  .tab-btn.active {
    color: #007bff;
    border-bottom-color: #007bff;
  }

  .tab-content {
    display: none;
  }

  .tab-content.active {
    display: block;
  }

  .mistake {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 1.5rem;
    margin: 1rem 0;
    border-radius: 0 8px 8px 0;
  }

  .mistake h4 {
    color: #856404;
    margin-bottom: 0.5rem;
  }

  .mistake p {
    color: #856404;
    margin-bottom: 0.5rem;
  }

  .mistake .solution {
    font-weight: 600;
    color: #155724;
    margin-top: 0.5rem;
  }

  @media (max-width: 768px) {
    .industry-tabs {
      flex-wrap: wrap;
    }

    .tab-btn {
      padding: 0.75rem 1rem;
      font-size: 0.875rem;
    }
    .content-section {
      padding: 20px;
    }
    .example{
        padding: 20px;
    }
    .content-section p{
      font-size: 16px;
    }
  }
`;

// Add styles to document
if (typeof document !== 'undefined') {
  const styleSheet = document.createElement("style");
  styleSheet.textContent = styles;
  document.head.appendChild(styleSheet);
}

// Type definitions
type BreakdownItem = { 
  name: string; 
  value: number; 
  color: string; 
};

type IndustryKey = "hvac" | "plumbing" | "electrical" | "landscaping" | "cleaning" | "general";
type ComplexityKey = "simple" | "standard" | "complex" | "emergency";

type FormData = {
  industry: IndustryKey;
  complexity: ComplexityKey;
  laborRate: number;
  estimatedHours: number;
  includeTravel: boolean;
  teamSize: number;
  materialCosts: number;
  materialMarkup: number;
  equipmentCosts: number;
  overheadRate: number;
  targetProfit: number;
};

type Results = {
  servicePrice: number;
  totalProfit: number;
  profitMargin: number;
  breakdown: BreakdownItem[];
};


// --- Hero Section (copied from lovable repo) ---
export const HeroSection = () => {
  const [calculationCount, setCalculationCount] = useState(52847);
  useEffect(() => {
    const interval = setInterval(() => {
      setCalculationCount(prev => prev + Math.floor(Math.random() * 3) + 1);
    }, 5000);
    return () => clearInterval(interval);
  }, []);
  return (
    <section className="homepage-banner from-blue-900 via-blue-800 to-blue-700 text-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 className="homepage-banner-title text-4xl md:text-6xl font-bold mb-6 leading-tight !text-[#232529]">Professional Service <br></br>Price Calculator</h1>
        <p className="text-xl md:text-2xl !py-0 !mb-0 !text-[#232529]">Calculate Profitable Pricing in Seconds</p>
        <p className="text-lg md:text-xl mt-0 mb-5 pb-0 !text-[#232529] max-w-4xl mx-auto">Get accurate service pricing that covers all costs and ensures profitable margins. Trusted by 10,000+ HVAC, plumbing, electrical & landscaping professionals.</p>
        <div className="grid md:grid-cols-3 gap-6 md:gap-8 max-w-4xl mx-auto mb-12">
          <div className="bg-[#faf8f8] rounded-lg p-6">
            <div className="text-3xl font-bold text-black mb-2">{calculationCount.toLocaleString()}</div>
            <div className="text-sm text-black">calculations performed this month</div>
          </div>
          <div className="bg-[#faf8f8] rounded-lg p-6">
            <div className="flex justify-center mb-2">
              {[...Array(5)].map((_, i) => (<Star key={i} className="w-5 h-5 fill-yellow-400 text-yellow-400" />))}
            </div>
            <div className="text-lg font-semibold text-black mb-1">4.9/5 Rating</div>
            <div className="text-sm text-black">based on 1,200+ reviews</div>
          </div>
          <div className="bg-[#faf8f8] rounded-lg p-6">
            <div className="flex justify-center space-x-2 mb-2">
              <CheckCircle className="w-5 h-5 text-green-400" />
              <CheckCircle className="w-5 h-5 text-green-400" />
              <CheckCircle className="w-5 h-5 text-green-400" />
            </div>
            <div className="text-lg font-semibold text-black mb-1">Trusted By</div>
            <div className="text-sm text-black">HVAC • Plumbing • Electrical • Landscaping</div>
          </div>
        </div>
        <a href="#calculator" className="inline-block bg-[#000] p-[.5rem_1.25rem] rounded-lg hover:bg-[#000] transition-colors shadow-lg">Start Calculating →</a>
      </div>
    </section>
  );
};

// --- Calculator (copied from lovable repo) ---
export const Calculator = () => {
  const [formData, setFormData] = useState<FormData>({
    industry: "hvac",
    complexity: "standard",
    laborRate: 75,
    estimatedHours: 2,
    includeTravel: false,
    teamSize: 1,
    materialCosts: 200,
    materialMarkup: 25,
    equipmentCosts: 50,
    overheadRate: 20,
    targetProfit: 25
  });

  const [results, setResults] = useState<Results>({
    servicePrice: 0,
    totalProfit: 0,
    profitMargin: 0,
    breakdown: []
  });

  const industryDefaults = {
    hvac: { laborRate: 85, description: "Heating, Ventilation & Air Conditioning" },
    plumbing: { laborRate: 75, description: "Plumbing & Water Systems" },
    electrical: { laborRate: 90, description: "Electrical Systems & Wiring" },
    landscaping: { laborRate: 65, description: "Landscaping & Outdoor Services" },
    cleaning: { laborRate: 45, description: "Cleaning & Maintenance Services" },
    general: { laborRate: 60, description: "General Contracting Services" }
  } as const;

  const complexityMultipliers = {
    simple: 1.0,
    standard: 1.2,
    complex: 1.5,
    emergency: 2.0
  } as const;

  useEffect(() => { calculateResults(); }, [formData]);

  const calculateResults = useCallback(() => {
    const complexityMultiplier = complexityMultipliers[formData.complexity];
    const totalHours = formData.estimatedHours * formData.teamSize;
    const travelHours = formData.includeTravel ? 1 : 0;
    const laborCost = (totalHours + travelHours) * formData.laborRate * complexityMultiplier;
    const materialCostWithMarkup = formData.materialCosts * (1 + formData.materialMarkup / 100);
    const subtotal = laborCost + materialCostWithMarkup + formData.equipmentCosts;
    const overheadCost = subtotal * (formData.overheadRate / 100);
    const totalCost = subtotal + overheadCost;
    const servicePrice = totalCost / (1 - formData.targetProfit / 100);
    const totalProfit = servicePrice - totalCost;
    const profitMargin = (totalProfit / servicePrice) * 100;
    const breakdown: BreakdownItem[] = [
      { name: "Labor", value: laborCost, color: "#3b82f6" },
      { name: "Materials", value: materialCostWithMarkup, color: "#10b981" },
      { name: "Equipment", value: formData.equipmentCosts, color: "#f59e0b" },
      { name: "Overhead", value: overheadCost, color: "#ef4444" },
      { name: "Profit", value: totalProfit, color: "#8b5cf6" }
    ];
    setResults({ servicePrice, totalProfit, profitMargin, breakdown });
  }, [formData]);

  return (
    <section id="calculator" className="bg-white mb-[40px] md:mb-[60px] lg:mb-[90px]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-5">
          <h2 className="font-bold text-gray-900 mb-3"><span className="text-[#667085] block">Advanced</span>Service Price Calculator</h2>
          <p className="max-w-3xl mx-auto">Calculate your profitable service pricing with our comprehensive tool</p>
        </div>
        <div className="grid lg:grid-cols-2 gap-8 items-start">
          {/* Calculator Inputs */}
          <div className="shadow-lg rounded-lg p-6 bg-white border border-[#e2e8f0]">
            <h3 className="text-xl font-semibold mb-4">Service Details</h3>
            <div className="space-y-6">
              <div>
                <label className="block text-base font-semibold mb-3">Service Industry</label>
                <div className="grid grid-cols-2 gap-4">
                  {Object.entries(industryDefaults).map(([key, value]) => (
                    <div key={key} className="flex items-center space-x-2">
                      <input type="radio" id={key} name="industry" checked={formData.industry === key} onChange={() => setFormData({ ...formData, industry: key as IndustryKey, laborRate: value.laborRate })} className="h-4 w-4" />
                      <label htmlFor={key} className="capitalize cursor-pointer">{key}</label>
                    </div>
                  ))}
                </div>
                <p className="text-sm text-gray-500 mt-2">{industryDefaults[formData.industry].description}</p>
              </div>
              <div>
                <label className="block text-base font-semibold mb-3">Job Complexity</label>
                <select value={formData.complexity} onChange={(e) => setFormData({ ...formData, complexity: e.target.value as ComplexityKey })} className="w-full p-2 border border-gray-300 rounded">
                  <option value="simple">Simple (x1.0)</option>
                  <option value="standard">Standard (x1.2)</option>
                  <option value="complex">Complex (x1.5)</option>
                  <option value="emergency">Emergency (x2.0)</option>
                </select>
              </div>
              <div>
                <label className="block text-base font-semibold mb-3">Labor Rate ($/hour)</label>
                <input type="number" value={formData.laborRate} onChange={(e) => setFormData({ ...formData, laborRate: parseFloat(e.target.value) || 0 })} placeholder={`Industry average: $${industryDefaults[formData.industry].laborRate}`} className="w-full p-2 border border-gray-300 rounded" />
                <p className="text-sm text-gray-500 mt-1">Industry benchmark: ${industryDefaults[formData.industry].laborRate}/hour</p>
              </div>
              <div>
                <label className="block text-base font-semibold mb-3">Estimated Hours</label>
                <div className="grid grid-cols-4 gap-2 mb-3">
                  {[1, 2, 4, 8].map((hours) => (
                    <button key={hours} onClick={() => setFormData({ ...formData, estimatedHours: hours })} className={formData.estimatedHours === hours ? "bg-[#000] text-white p-2 rounded text-sm" : "bg-gray-200 p-2 rounded text-sm"}> {hours}h </button>
                  ))}
                </div>
                <input type="number" value={formData.estimatedHours} onChange={(e) => setFormData({ ...formData, estimatedHours: parseFloat(e.target.value) || 0 })} step="0.5" className="w-full p-2 border border-gray-300 rounded" />
              </div>
              <div>
                <label className="block text-base font-semibold mb-3">Team Size</label>
                <input type="number" value={formData.teamSize} onChange={(e) => setFormData({ ...formData, teamSize: parseInt(e.target.value) || 1 })} min="1" max="10" className="w-full p-2 border border-gray-300 rounded" />
              </div>
              <div>
                <label className="block text-base font-semibold mb-3">Material Costs ($)</label>
                <input type="number" value={formData.materialCosts} onChange={(e) => setFormData({ ...formData, materialCosts: parseFloat(e.target.value) || 0 })} placeholder="200" className="w-full p-2 border border-gray-300 rounded" />
              </div>
              <div>
                <label className="block text-base font-semibold mb-3">Material Markup (%)</label>
                <input type="number" value={formData.materialMarkup} onChange={(e) => setFormData({ ...formData, materialMarkup: parseFloat(e.target.value) || 0 })} placeholder="25" className="w-full p-2 border border-gray-300 rounded" />
              </div>
              <div>
                <label className="block text-base font-semibold mb-3">Equipment Costs ($)</label>
                <input type="number" value={formData.equipmentCosts} onChange={(e) => setFormData({ ...formData, equipmentCosts: parseFloat(e.target.value) || 0 })} placeholder="50" className="w-full p-2 border border-gray-300 rounded" />
              </div>
              <div>
                <label className="block text-base font-semibold mb-3">Overhead Rate (%)</label>
                <input type="number" value={formData.overheadRate} onChange={(e) => setFormData({ ...formData, overheadRate: parseFloat(e.target.value) || 0 })} placeholder="20" className="w-full p-2 border border-gray-300 rounded" />
              </div>
              <div>
                <label className="block text-base font-semibold mb-3">Target Profit (%)</label>
                <input type="number" value={formData.targetProfit} onChange={(e) => setFormData({ ...formData, targetProfit: parseFloat(e.target.value) || 0 })} placeholder="25" className="w-full p-2 border border-gray-300 rounded" />
              </div>
            </div>
          </div>
          {/* Results Panel */}
          <div className="sticky top-[100px]">
            <div className="shadow-lg rounded-lg p-6 bg-white border border-[#e2e8f0] h-auto">
            <h3 className="text-xl font-semibold mb-4">Results</h3>
            <div className="h-64 mb-6">
                <ResponsiveContainer width="100%" height="100%">
                  <PieChart>
                    <Pie
                      data={results.breakdown}
                      cx="50%"
                      cy="50%"
                      innerRadius={60}
                      outerRadius={100}
                      dataKey="value"
                    >
                      {results.breakdown.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} />
                      ))}
                    </Pie>
                    <Tooltip formatter={(value) => `$${typeof value === 'number' ? value.toFixed(2) : value}`} />
                  </PieChart>
                </ResponsiveContainer>
              </div>
            <div className="text-center mb-6">
              <p className="text-sm text-gray-500">Your Service Price</p>
              <p className="text-3xl font-bold text-green-600">${results.servicePrice.toFixed(2)}</p>
            </div>
            <div className="flex justify-between mb-6">
              <div className="text-center">
                <p className="text-sm text-gray-500">Profit</p>
                <p className="font-semibold">${results.totalProfit.toFixed(0)}</p>
              </div>
              <div className="text-center">
                <p className="text-sm text-gray-500">Margin</p>
                <p className="font-semibold">{results.profitMargin.toFixed(1)}%</p>
              </div>
              <div className="text-center">
                <p className="text-sm text-gray-500">Total Cost</p>
                <p className="font-semibold">${(results.servicePrice - results.totalProfit).toFixed(0)}</p>
              </div>
            </div>
            <div className="border-t pt-4">
              <h4 className="font-semibold mb-2">Cost Breakdown</h4>
              {results.breakdown.map((item, i) => (
                <div key={i} className="flex justify-between mb-1">
                  <span>{item.name}:</span>
                  <span>${item.value.toFixed(0)} ({((item.value / results.servicePrice) * 100).toFixed(0)}%)</span>
                </div>
              ))}
            </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

// --- "How to Use" Section (copied from lovable's EducationalContent) ---
export const HowToUseSection = () => (
  <section className="bg-white mb-[40px] md:mb-[60px] lg:mb-[90px]">
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div className="shadow-lg rounded-lg md:p-[40px] p-[20px] bg-white border border-[#e2e8f0]">
        <h2 className="text-center mb-[20px]">
          <span className="text-[#667085] block">How to Use This </span> Service Price Calculator
        </h2>
        <div className="grid md:grid-cols-3 gap-8">
          <div className="text-center bg-[#3b82f617] rounded-[10px] p-[20px]">
            <div className="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">1</div>
            <h3 className="text-lg font-semibold mb-2">Enter Service Details</h3>
            <p className="text-gray-600">Select your industry, job complexity, and estimated hours for accurate calculations.</p>
          </div>
          <div className="text-center bg-[#3b82f617] rounded-[10px] p-[20px]">
            <div className="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">2</div>
            <h3 className="text-lg font-semibold mb-2">Add Costs & Margins</h3>
            <p className="text-gray-600">Input material costs, equipment expenses, overhead rate, and target profit margin.</p>
          </div>
          <div className="text-center bg-[#3b82f617] rounded-[10px] p-[20px]">
            <div className="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">3</div>
            <h3 className="text-lg font-semibold mb-2">Get Your Price</h3>
            <p className="text-gray-600">Review the breakdown, save your calculation, and email the quote to your customer.</p>
          </div>
        </div>
        <div className="mt-8 bg-blue-50 p-6 rounded-lg">
          <h4 className="font-semibold text-lg mb-3">Pro Tips for Accurate Pricing:</h4>
          <ul className="list-disc list-inside space-y-2 text-gray-700">
            <li>Always include travel time for accurate labor calculations</li>
            <li>Factor in job complexity multipliers for challenging installations</li>
            <li>Apply appropriate material markups based on your business model</li>
            <li>Review and update your overhead rate quarterly</li>
            <li>Set minimum profit margins to ensure business sustainability</li>
          </ul>
        </div>
      </div>
    </div>
  </section>
);

// --- Content Sections (converted from Claude HTML) ---
export const ContentSections = () => {
  const [activeTab, setActiveTab] = useState('hvac');

  const tabContents = {
    hvac: {
      title: "HVAC Contractor Pricing Best Practices",
      content: (
        <>
          <p className="text-gray-600 mb-4">HVAC contractors face unique pricing challenges including expensive equipment, seasonal demand, and specialized certifications.</p>
          <ul className="list-disc list-inside space-y-2 text-gray-600 mb-6">
            <li>Equipment-intensive overhead (trucks, tools, diagnostic equipment)</li>
            <li>EPA 608 certification requirements and ongoing training costs</li>
            <li>Seasonal demand variations (peak summer/winter pricing)</li>
            <li>Emergency service premiums (25-50% markup for after-hours)</li>
            <li>Refrigerant phase-out cost impacts on system pricing</li>
            <li>Warranty and callback considerations</li>
          </ul>
          <div className="pro-tips bg-blue-50 p-6 rounded-lg">
            <h4 className="font-semibold text-lg mb-3">Typical HVAC Pricing Structure:</h4>
            <ul className="list-disc list-inside space-y-2 text-gray-700">
              <li>Service calls: $89-150 base fee + hourly rate</li>
              <li>Diagnostic fees: $89-150 (often applied to repair cost)</li>
              <li>Emergency/after-hours: 1.5x regular rates</li>
              <li>Installation: Project-based with 25-35% profit margins</li>
            </ul>
          </div>
        </>
      )
    },
    plumbing: {
      title: "Plumbing Service Pricing Methods",
      content: (
        <>
          <p className="text-gray-600 mb-4">Plumbing services require different pricing approaches based on job type and complexity.</p>
          <ul className="list-disc list-inside space-y-2 text-gray-600 mb-6">
            <li>Flat rate pricing for common repairs (toilet, faucet, drain cleaning)</li>
            <li>Time and materials for complex diagnostics and custom work</li>
            <li>Emergency premiums for after-hours and weekend calls</li>
            <li>Travel charges for locations beyond service area</li>
          </ul>
          <div className="pro-tips bg-blue-50 p-6 rounded-lg">
            <h4 className="font-semibold text-lg mb-3">Typical Plumbing Rates:</h4>
            <ul className="list-disc list-inside space-y-2 text-gray-700">
              <li>Service calls: $99-175 base fee</li>
              <li>Hourly rates: $70-120/hour</li>
              <li>Emergency surcharge: $50-100 additional</li>
              <li>Travel time: Usually included in service call fee</li>
            </ul>
          </div>
        </>
      )
    },
    electrical: {
      title: "Electrical Service Pricing Guidelines",
      content: (
        <>
          <p className="text-gray-600 mb-4">Electrical work requires careful pricing due to safety, code compliance, and liability factors.</p>
          <ul className="list-disc list-inside space-y-2 text-gray-600 mb-6">
            <li>Strict code compliance requirements</li>
            <li>Safety equipment and training costs</li>
            <li>Licensing and continuing education expenses</li>
            <li>Higher liability insurance requirements</li>
            <li>Permit and inspection coordination</li>
          </ul>
          <div className="pro-tips bg-blue-50 p-6 rounded-lg">
            <h4 className="font-semibold text-lg mb-3">Rate Structure:</h4>
            <ul className="list-disc list-inside space-y-2 text-gray-700">
              <li>Service calls: $89-150 diagnostic fee</li>
              <li>Hourly rates: $70-110/hour</li>
              <li>Minimum charges: 1-2 hour minimums common</li>
              <li>Material markup: 25-40% on electrical supplies</li>
            </ul>
          </div>
        </>
      )
    },
    landscaping: {
      title: "Landscaping Service Pricing",
      content: (
        <>
          <p className="text-gray-600 mb-4">Landscaping businesses must account for seasonal variations, weather risks, and equipment costs.</p>
          <ul className="list-disc list-inside space-y-2 text-gray-600 mb-6">
            <li>Seasonal workforce and equipment utilization</li>
            <li>Weather delays and rescheduling costs</li>
            <li>Equipment transportation and setup time</li>
            <li>Material delivery and storage considerations</li>
            <li>Property access and site preparation</li>
          </ul>
          <div className="pro-tips bg-blue-50 p-6 rounded-lg">
            <h4 className="font-semibold text-lg mb-3">Typical Structure:</h4>
            <ul className="list-disc list-inside space-y-2 text-gray-700">
              <li>Design consultation: $75-150/hour</li>
              <li>Installation crews: $35-55/hour per worker</li>
              <li>Maintenance: $30-50/hour or monthly contracts</li>
              <li>Equipment charges: Daily rates for specialized tools</li>
            </ul>
          </div>
        </>
      )
    }
  };

  return (
    <section className="bg-white mb-[40px] md:mb-[60px] lg:mb-[90px]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Pricing Fundamentals */}
        <div className="content-section mb-16">
          <h2 className="font-semibold text-center mb-6"><span className="text-[#667085] block">Complete Guide</span> to Profitable Service Pricing</h2>
          
          <h3 className="text-xl font-semibold mb-4">Why Proper Pricing Matters</h3>
          <p className="text-gray-600 mb-6">Underpricing is the #1 reason service businesses fail. When you Don&apos;t charge enough to cover all costs plus a reasonable profit, you&apos;re essentially paying customers to let you work. Proper pricing ensures business sustainability, allows for equipment replacement, and funds growth opportunities.</p>

          <h3 className="text-xl font-semibold mb-4">The 4 Pillars of Service Pricing</h3>
          
          <div className="space-y-6">
            <div className="example">
              <h4 className="text-lg font-semibold mb-2">1. Labor Costs - Your Foundation</h4>
              <p className="text-gray-600">True labor cost goes beyond base wages. Include payroll taxes (7.65%), workers compensation (varies by state), health insurance, vacation pay, and training time. A $25/hour employee actually costs $35-40/hour when you include all expenses.</p>
            </div>

            <div className="example">
              <h4 className="text-lg font-semibold mb-2">2. Direct Material Costs</h4>
              <p className="text-gray-600">Track actual material costs including waste, spoilage, and handling time. Apply appropriate markup (15-35%) to cover purchasing, storage, and delivery costs. Monitor material price fluctuations and adjust pricing accordingly.</p>
            </div>

            <div className="example">
              <h4 className="text-lg font-semibold mb-2">3. Overhead Expenses - The Hidden Costs</h4>
              <p className="text-gray-600">Overhead includes rent, insurance, licenses, office supplies, phone, internet, accounting, marketing, and vehicle expenses. Calculate your overhead rate by dividing total monthly expenses by monthly billable hours.</p>
            </div>

            <div className="example">
              <h4 className="text-lg font-semibold mb-2">4. Profit Margin - Your Business Future</h4>
              <p className="text-gray-600">Profit isn&apos;t optional—it funds equipment replacement, business growth, and economic downturns. Target profit margins: HVAC (25-35%), Plumbing (30-40%), Electrical (25-35%), Landscaping (20-30%).</p>
            </div>
          </div>

          <h3 className="text-xl font-semibold mt-8 mb-4">The Professional Service Pricing Formula</h3>
          <div className="calculation-breakdown bg-gray-100 p-4 rounded font-mono text-sm">
            <strong>Service Price = (Labor + Materials + Equipment + Overhead) ÷ (1 - Profit Margin %)</strong>
          </div>
          <p className="mt-4 text-gray-600">This formula ensures all costs are covered and guarantees your target profit margin. Unlike simple markup, this method accounts for variable job complexity and provides consistent profitability.</p>
        </div>

        {/* Industry Strategies */}
        <div className="content-section mb-16">
          <h2 className="font-semibold text-center mb-6"><span className="text-[#667085] block">Service Pricing by Industry:</span> Expert Strategies</h2>
          
          <div className="industry-tabs">
            {Object.keys(tabContents).map((tab) => (
              <button
                key={tab}
                className={`tab-btn ${activeTab === tab ? 'active' : ''}`}
                onClick={() => setActiveTab(tab)}
              >
                {tab.charAt(0).toUpperCase() + tab.slice(1)}
              </button>
            ))}
          </div>

          {Object.entries(tabContents).map(([tab, content]) => (
            <div key={tab} className={`tab-content ${activeTab === tab ? 'active' : ''}`}>
              <h3 className="text-xl font-semibold mb-4">{content.title}</h3>
              {content.content}
            </div>
          ))}
        </div>

        {/* Examples */}
        <div className="content-section mb-16">
          <h2 className="font-semibold text-center mb-6"><span className="text-[#667085] block">Service Pricing Examples:</span> See the Calculator in Action</h2>
          
          <div className="space-y-6">
            <div className="example">
              <h4 className="text-lg font-semibold mb-2">Example 1: HVAC System Repair</h4>
              <p className="text-gray-600 mb-2"><strong>Job:</strong> Residential AC compressor replacement</p>
              <div className="calculation-breakdown bg-gray-100 p-4 rounded font-mono text-sm">
                • Labor: 4 hours × $75/hour = $300<br />
                • Materials: $450 compressor + 25% markup = $562.50<br />
                • Equipment: Daily depreciation + fuel = $35<br />
                • Overhead: 30% of direct costs = $269.25<br />
                • Subtotal: $1,166.75<br />
                • Target profit margin: 25%<br />
                • <strong>Final Price: $1,555.67</strong>
              </div>
              <p className="mt-4 text-gray-600">This price ensures all costs are covered plus a healthy 25% profit margin, allowing for business growth and equipment replacement.</p>
            </div>

            <div className="example">
              <h4 className="text-lg font-semibold mb-2">Example 2: Emergency Plumbing Call</h4>
              <p className="text-gray-600 mb-2"><strong>Job:</strong> Weekend toilet repair with parts replacement</p>
              <div className="calculation-breakdown bg-gray-100 p-4 rounded font-mono text-sm">
                • Labor: 2 hours × $95/hour (emergency rate) = $190<br />
                • Materials: $85 parts + 30% markup = $110.50<br />
                • Travel/Equipment: $25<br />
                • Emergency surcharge: $75<br />
                • Overhead: 28% of direct costs = $112.14<br />
                • Subtotal: $512.64<br />
                • Target profit margin: 35%<br />
                • <strong>Final Price: $788.68</strong>
              </div>
              <p className="mt-4 text-gray-600">Emergency pricing reflects the inconvenience and higher costs of weekend service while maintaining profitability.</p>
            </div>

            <div className="example">
              <h4 className="text-lg font-semibold mb-2">Example 3: Landscaping Installation</h4>
              <p className="text-gray-600 mb-2"><strong>Job:</strong> Residential shrub bed installation (200 sq ft)</p>
              <div className="calculation-breakdown bg-gray-100 p-4 rounded font-mono text-sm">
              • Labor: 6 hours × 2 workers × $35/hour = $420<br />
              • Materials: Plants, soil, mulch = $325<br />
              • Equipment: Daily tool rental + fuel = $85<br />
              • Overhead: 25% of direct costs = $207.50<br />
              • Subtotal: $1,037.50<br />
              • Target profit margin: 30%<br />
              • <strong>Final Price: $1,482.14</strong>
              </div>
              <p className="mt-4 text-gray-600">This pricing accounts for team labor, quality materials, and equipment while ensuring sustainable profit margins.</p>
            </div>
          </div>
        </div>

        {/* Implementation Guide */}
        <div className="content-section mb-16">
          <h2 className="font-semibold text-center mb-6"><span className="text-[#667085] block">How to Implement</span> Your New Pricing Strategy</h2>
          
          <h3 className="text-xl font-semibold mb-4">Customer Communication</h3>
          <div className="pro-tips bg-blue-50 p-6 rounded-lg mb-8">
            <h4 className="font-semibold text-lg mb-3">Presenting Prices with Confidence</h4>
            <ul className="list-disc list-inside space-y-2 text-gray-700">
              <li><strong>Lead with Value, Not Price:</strong> Focus on the benefits and outcomes your service provides. Explain your expertise, quality materials, and professional approach before discussing price.</li>
              <li><strong>Break Down Costs When Needed:</strong> If customers question pricing, provide a general breakdown showing labor, materials, and overhead costs. This demonstrates transparency and professionalism.</li>
              <li><strong>Handle Objections Professionally:</strong> Common objections and responses include explaining what&apos;s included in your service and discussing different options while maintaining quality standards.</li>
              <li><strong>Offer Payment Options:</strong> Consider offering financing, payment plans, or cash discounts to make your services more accessible while maintaining profit margins.</li>
            </ul>
          </div>

          <h3 className="text-xl font-semibold mb-4">Price Testing and Optimization</h3>
          <div className="pro-tips bg-blue-50 p-6 rounded-lg">
            <h4 className="font-semibold text-lg mb-3">Testing and Refining Your Pricing</h4>
            <ul className="list-disc list-inside space-y-2 text-gray-700">
              <li><strong>Gradual Implementation:</strong> Don&apos;t change all prices at once. Test new pricing on new customers first, then gradually implement for existing customers.</li>
              <li><strong>Monitor Key Metrics:</strong> Track your win/loss ratio, average job value, and profit margins. If you&apos;re winning every bid, your prices may be too low.</li>
              <li><strong>Regular Cost Reviews:</strong> Review and update your costs quarterly: labor rates, material pricing, overhead expenses, and market rates.</li>
              <li><strong>Seasonal Adjustments:</strong> Many service businesses benefit from seasonal pricing such as HVAC higher rates during peak seasons and landscaping premium pricing for spring rush.</li>
            </ul>
          </div>
        </div>

        {/* Common Mistakes */}
        <div className="content-section mb-16">
          <h2 className="font-semibold text-center mb-6"><span className="text-[#667085] block">5 Pricing Mistakes</span> That Kill Service Business Profits</h2>
          
          <div className="space-y-6">
            <div className="mistake bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600">
              <h4 className="font-semibold text-lg mb-2">1. Forgetting Hidden Costs</h4>
              <p className="text-gray-600 mb-2">Many contractors only calculate obvious costs like labor and materials, forgetting travel time, fuel, insurance, licenses, administrative time, and customer communication. These &quot;hidden&quot; costs can eat up 20-30% of your revenue.</p>
              <div className="solution font-semibold text-green-600 mt-2">Solution: Track all time and expenses for several jobs to identify true costs.</div>
            </div>

            <div className="mistake bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600">
              <h4 className="font-semibold text-lg mb-2">2. Using Outdated Rates</h4>
              <p className="text-gray-600 mb-2">Labor costs change annually with wage increases and benefit changes. Material prices fluctuate monthly. Overhead rates shift with business growth and expense changes.</p>
              <div className="solution font-semibold text-green-600 mt-2">Solution: Review and update all rates quarterly, adjust pricing immediately when major costs change.</div>
            </div>

            <div className="mistake bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600">
              <h4 className="font-semibold text-lg mb-2">3. Competing Only on Price</h4>
              <p className="text-gray-600 mb-2">Racing to the bottom hurts everyone in the industry and leads to poor service quality. Customers who only care about price are often the most difficult to work with.</p>
              <div className="solution font-semibold text-green-600 mt-2">Solution: Focus on value, quality, and reliability. Build a reputation that justifies premium pricing.</div>
            </div>

            <div className="mistake bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600">
              <h4 className="font-semibold text-lg mb-2">4. Inconsistent Pricing</h4>
              <p className="text-gray-600 mb-2">Using different pricing methods for similar jobs confuses customers and staff. Inconsistency makes it impossible to track profitability accurately.</p>
              <div className="solution font-semibold text-green-600 mt-2">Solution: Use standard formulas and document pricing decisions. Train all estimators to use the same methods.</div>
            </div>

            <div className="mistake bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600">
              <h4 className="font-semibold text-lg mb-2">5. Inadequate Profit Margins</h4>
              <p className="text-gray-600 mb-2">A 10% margin won&apos;t sustain business growth or handle economic downturns. Low margins leave no room for equipment replacement, training, or business development.</p>
              <div className="solution font-semibold text-green-600 mt-2">Solution: Target industry-standard margins (20-35%) and stick to them. Remember: profit isn&apos;t optional.</div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

// --- FAQ (using existing Accordion component) ---
const faqItems = [
  { 
    title: "How do I calculate my true overhead rate?", 
    content: [
      "List all your monthly business expenses: rent, insurance, phone, internet, licensing, accounting, marketing, vehicle expenses, and office supplies. Divide this total by your monthly billable hours.",
      "Example: $8,000 monthly expenses ÷ 160 billable hours = $50/hour overhead rate or 31.25% if your labor rate is $80/hour.",
      "Update this calculation quarterly as expenses and productivity change."
    ]
  },
  { 
    title: "What profit margin should I target?", 
    content: [
      "Industry benchmarks:",
      "• HVAC: 25-35%",
      "• Plumbing: 30-40%",
      "• Electrical: 25-35%",
      "• Landscaping: 20-30%",
      "• Cleaning: 15-25%",
      "Consider your market position, competition, and business goals. Higher margins allow for better equipment, training, and service quality."
    ]
  },
  { 
    title: "Should I price emergency services differently?", 
    content: [
      "Yes. Emergency and after-hours services should include a premium of 25-50% above regular rates. This covers higher labor costs for overtime, disruption to scheduled work, immediate availability and response, and additional risk and liability.",
      "Most customers understand and accept emergency pricing when they need immediate help."
    ]
  },
  { 
    title: "How often should I update my pricing?", 
    content: [
      "Review costs quarterly and adjust pricing as needed: monitor material cost changes monthly, review labor rates annually or when costs change, update overhead rates quarterly, watch competitor pricing and market conditions, and implement price increases gradually (5-10% annually is typical)."
    ]
  },
  { 
    title: "What if customers say I'm too expensive?", 
    content: [
      "Focus on value, not price: explain your qualifications and experience, highlight quality materials and warranties, emphasize reliability and professionalism, offer to explain cost breakdown if appropriate, and stand firm on pricing that ensures quality service.",
      "Remember: customers who only care about price are rarely the best customers."
    ]
  },
  { 
    title: "How do I handle material cost fluctuations?", 
    content: [
      "Strategies for managing material price changes: include price escalation clauses in contracts, quote material prices valid for 30 days only, monitor supplier pricing weekly, adjust markup percentages as needed, communicate changes to customers proactively, and consider bulk purchasing for stable pricing."
    ]
  },
  { 
    title: "Should I offer package pricing or discounts?", 
    content: [
      "Package pricing works well for maintenance contracts (monthly/annual), multiple services for same customer, and off-season work to level workload.",
      "Maintain the same profit margins on packages. Discounts should come from reduced overhead (scheduling efficiency) rather than reduced profit."
    ]
  }
];

export const FaqSection = () => (
  <section className="py-16 bg-gray-50">
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 className="font-semibold text-center mb-6">Service Pricing Questions Answered</h2>
      <Accordion items={faqItems} />
    </div>
  </section>
);