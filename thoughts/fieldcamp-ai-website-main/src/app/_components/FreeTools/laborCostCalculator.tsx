'use client';
import React, { useState, useEffect } from 'react';

interface CalculatorResults {
  baseAnnual: number;
  payrollTaxAmount: number;
  benefitsAmount: number;
  workersCompAmount: number;
  indirectAmount: number;
  totalAnnual: number;
  trueHourly: number;
  billableRate: number;
}

const LaborCostCalculator = () => {

  const [baseWage, setBaseWage] = useState<number>(25);
    const [annualHours, setAnnualHours] = useState<number>(2080);
    const [payrollTax, setPayrollTax] = useState<number>(8.5);
    const [benefitsCost, setBenefitsCost] = useState<number>(8);
    const [workersComp, setWorkersComp] = useState<number>(3);
    const [indirectCosts, setIndirectCosts] = useState<number>(12);
    const [profitMargin, setProfitMargin] = useState<number>(15);
  
    const [results, setResults] = useState<CalculatorResults>({
      baseAnnual: 52000,
      payrollTaxAmount: 4420,
      benefitsAmount: 16640,
      workersCompAmount: 1560,
      indirectAmount: 24960,
      totalAnnual: 99580,
      trueHourly: 47.87,
      billableRate: 55.05
    });
  
    // Memoize updateCalculations to prevent infinite loops
    const updateCalculations = React.useCallback(() => {
      const baseAnnual = baseWage * annualHours;
      const payrollTaxAmount = baseAnnual * (payrollTax / 100);
      const benefitsAmount = benefitsCost * annualHours;
      const workersCompAmount = baseAnnual * (workersComp / 100);
      const indirectAmount = indirectCosts * annualHours;
      
      const totalAnnual = baseAnnual + payrollTaxAmount + benefitsAmount + workersCompAmount + indirectAmount;
      const trueHourly = totalAnnual / annualHours;
      const billableRate = trueHourly * (1 + profitMargin / 100);
  
      setResults({
        baseAnnual,
        payrollTaxAmount,
        benefitsAmount,
        workersCompAmount,
        indirectAmount,
        totalAnnual,
        trueHourly,
        billableRate
      });
    }, [baseWage, annualHours, payrollTax, benefitsCost, workersComp, indirectCosts, profitMargin]);
  
    // Run calculations when dependencies change
    useEffect(() => {
      updateCalculations();
    }, [updateCalculations]);
  
    const setHVACPreset = () => {
      setBaseWage(28);
      setPayrollTax(9.2);
      setBenefitsCost(10);
      setWorkersComp(3.5);
      setIndirectCosts(14);
      setProfitMargin(18);
    };
  
    const setPlumbingPreset = () => {
      setBaseWage(32);
      setPayrollTax(8.8);
      setBenefitsCost(12);
      setWorkersComp(2.8);
      setIndirectCosts(16);
      setProfitMargin(20);
    };
  
    const setElectricalPreset = () => {
      setBaseWage(35);
      setPayrollTax(9.0);
      setBenefitsCost(14);
      setWorkersComp(2.2);
      setIndirectCosts(18);
      setProfitMargin(22);
    };
  
    const setConstructionPreset = () => {
      setBaseWage(30);
      setPayrollTax(10.5);
      setBenefitsCost(8);
      setWorkersComp(8.5);
      setIndirectCosts(12);
      setProfitMargin(15);
    };
  
    const downloadReport = () => {
      alert('PDF report download functionality would be implemented here. This would generate a comprehensive labor cost breakdown report with all calculations and recommendations.');
    };
  
    const formatCurrency = (value: number, decimals: number = 0): string => {
      return '$' + value.toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
      });
    };

  return  <><style jsx>{`
    * {
      box-sizing: border-box;
    }
      .container {
        max-width: 1245px;
        margin: 0 auto;
        padding:  0 20px;
      }
      body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        line-height: 1.6;
        color: #333;
        background-color: #f8fafc;
      }
      
      /* Header */
      header {
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
      }
      
      nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
      }
      
      .logo {
        font-size: 1.5rem;
        font-weight: bold;
        color: #2563eb;
      }
      
      .nav-links {
        display: flex;
        list-style: none;
        gap: 2rem;
      }
      
      .nav-links a {
        text-decoration: none;
        color: #374151;
        font-weight: 500;
        transition: color 0.3s;
      }
      
      .nav-links a:hover {
        color: #2563eb;
      }
      
      .cta-header {
        background: #2563eb;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.3s;
      }
      
      .cta-header:hover {
        background: #1d4ed8;
      }
      
      /* Hero Section */
    
      .hero .subtitle {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.9;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
      }
      
      .benefits-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
        margin: 2rem 0;
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
      }
      
      .benefit-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-align: left;
      }
      
      .benefit-item .checkmark {
        background: #10b981;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        flex-shrink: 0;
      }
      
      .cta-subtitle {
        font-size: 0.9rem;
        margin-top: 0.5rem;
        padding-bottom: 0;
        opacity: 0.8;
      }
      
      /* Social Proof Section */
      
      .testimonials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
      }
      
      .testimonial-card {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #2563eb;
      }
      
      .testimonial-content p {
        font-style: italic;
        color: #4b5563;
        margin-bottom: 1rem;
        line-height: 1.6;
      }
      
      .testimonial-author {
        display: flex;
        align-items: center;
      }
      
      .author-info strong {
        display: block;
        color: #1f2937;
        font-weight: 600;
      }
      
      .author-info span {
        color: #6b7280;
        font-size: 0.9rem;
      }
      
      .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        text-align: center;
      }
      
      .stat-item {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      
      .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #2563eb;
        margin-bottom: 0.5rem;
      }
      
      .stat-label {
        color: #6b7280;
        font-weight: 500;
      }
      
      /* Calculator Section */
      .calculator-section {
        background: white;
        padding: 4rem 0;
        margin: -2rem 0 0 0;
        position: relative;
        z-index: 10;
      }
      
      .calculator-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        max-width: 1000px;
        margin: 0 auto;
      }
                
      .calculator-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: start;
      }
      
      .calculator-inputs {
        display: grid;
        gap: 1.5rem;
      }
      
      .preset-buttons {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 6px;
      }
      
      .preset-buttons h4 {
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        color: #374151;
      }
      
      .preset-btn {
        background: #000;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        cursor: pointer;
        font-size: 0.875rem;
        transition: background 0.3s;
      }
      
      .preset-btn:hover {
        background: #333;
      }
      
      .input-group {
        display: flex;
        flex-direction: column;
      }
      
      .input-group label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #374151;
      }
      
      .input-group input {
        padding: 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s;
      }
      
      .input-group input:focus {
        outline: none;
        border-color: #2563eb;
      }
      
      .result-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
      }
      
      .result-item:last-child {
        border-bottom: none;
        font-weight: 700;
        font-size: 1.1rem;
        color: #2563eb;
      }
      
      .result-label {
        font-weight: 500;
      }
      
      .result-value {
        font-weight: 600;
        color: #1f2937;
      }
      
      .download-btn {
        background: #000;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        margin-top: 1rem;
        transition: background 0.3s;
      }
      
      .download-btn:hover {
        background: #333;
      }
      
      /* Content Sections */
      .formula-box {
        background: #f3f4f6;
        border-left: 4px solid #2563eb;
        padding: 1.5rem;
        margin: 1.5rem 0;
        border-radius: 0 6px 6px 0;
        font-family: 'Courier New', monospace;
        font-size: 1rem;
      }
      .content-section h2, .content-section h3, .content-section h4, .content-section h5 {
        margin-bottom: 10px;
        color: #000;
      }
      .content-section ul {
        margin: 0rem 0 15px 0;
        }

        .content-section li {
            margin-bottom: 0.5rem;
        }
      .example-box {
        background: #ecfdf5;
        border: 1px solid #000;
        padding: 1.5rem;
        margin: 1.5rem 0;
        border-radius: 6px;
      }
      
      .industry-grid {
        display: grid;
        gap: 2rem;
        margin: 2rem 0;
      }
      
    
      .industry-card h4 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1rem;
      }
      
      /* FAQ Section */
      .faq-section {
        background: white;
        padding: 4rem 0;
      }
      
      .faq-container {
        max-width: 900px;
        margin: 0 auto;
      }
      
      .faq-item {
        border-bottom: 1px solid #e5e7eb;
        padding: 1.5rem 0;
      }
      
      .faq-question {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.75rem;
      }
      
      .faq-answer {
        font-size: 1.1rem;
        line-height: 1.7;
        color: #4b5563;
      }
      
      /* CTA Sections */
      
      .cta-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
      }
      
      .cta-secondary {
        background: white;
        color: #000;
        padding: 1rem 2rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.3s;
        border: none;
        cursor: pointer;
      }
      
      .cta-secondary:hover {
        background: #f8fafc;
      }
      
      /* Footer */
      footer {
        background: #1f2937;
        color: white;
        padding: 3rem 0 1rem 0;
      }
      
      .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
      }
      
      .footer-section h4 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
      }
      
      .footer-section a {
        color: #d1d5db;
        text-decoration: none;
        display: block;
        margin-bottom: 0.5rem;
        transition: color 0.3s;
      }
      
      .footer-section a:hover {
        color: white;
      }
      
      .footer-bottom {
        border-top: 1px solid #374151;
        padding-top: 1rem;
        text-align: center;
        color: #9ca3af;
      }
      
      /* Mobile Responsiveness */
      @media (max-width: 768px) {
        .nav-links {
          display: none;
        }
        
        .hero h1 {
          font-size: 2rem;
        }
        
        .calculator-grid {
          grid-template-columns: 1fr;
          gap: 2rem;
        }
        
        .benefits-list {
          grid-template-columns: 1fr;
        }
        
        .industry-grid {
          grid-template-columns: 1fr;
        }
        
        .cta-buttons {
          flex-direction: column;
          align-items: center;
        }
      }
    `}</style>
  <section className="bg-white mb-[40px] md:mb-[60px] lg:mb-[90px]" id="calculator">
  <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div className="container">
      <div className="calculator-header text-center">
      <h2 className="font-bold text-gray-900 mb-3"><span className="text-[#667085] block">Advanced</span>Labor Cost Calculator</h2>
        <p className="pb-[30px]">Real-time calculation updates as you type. Mobile-optimized for on-site estimates.</p>
      </div>
      
      <div className="grid lg:grid-cols-2 gap-8 items-start">
        <div className="calculator-inputs shadow-lg rounded-lg p-6 bg-white border border-[#e2e8f0]">
          <div className="preset-buttons">
            <h4>Industry Presets:</h4>
            <button onClick={setHVACPreset} className="preset-btn">HVAC</button>
            <button onClick={setPlumbingPreset} className="preset-btn">Plumbing</button>
            <button onClick={setElectricalPreset} className="preset-btn">Electrical</button>
            <button onClick={setConstructionPreset} className="preset-btn">Construction</button>
          </div>
          
          <div className="input-group">
            <label htmlFor="baseWage">Base Hourly Wage ($)</label>
            <input
              type="number"
              id="baseWage"
              value={baseWage}
              onChange={(e) => setBaseWage(Number(e.target.value))}
              min="0"
              max="200"
              step="0.50"
            />
          </div>
          
          <div className="input-group">
            <label htmlFor="annualHours">Annual Work Hours</label>
            <input
              type="number"
              id="annualHours"
              value={annualHours}
              onChange={(e) => setAnnualHours(Number(e.target.value))}
              min="1000"
              max="3000"
            />
          </div>
          
          <div className="input-group">
            <label htmlFor="payrollTax">Payroll Tax Rate (%)</label>
            <input
              type="number"
              id="payrollTax"
              value={payrollTax}
              onChange={(e) => setPayrollTax(Number(e.target.value))}
              min="0"
              max="20"
              step="0.1"
            />
          </div>
          
          <div className="input-group">
            <label htmlFor="benefitsCost">Benefits Cost per Hour ($)</label>
            <input
              type="number"
              id="benefitsCost"
              value={benefitsCost}
              onChange={(e) => setBenefitsCost(Number(e.target.value))}
              min="0"
              max="50"
              step="0.50"
            />
          </div>
          
          <div className="input-group">
            <label htmlFor="workersComp">Workers&apos; Comp Rate (%)</label>
            <input
              type="number"
              id="workersComp"
              value={workersComp}
              onChange={(e) => setWorkersComp(Number(e.target.value))}
              min="0"
              max="15"
              step="0.1"
            />
          </div>
          
          <div className="input-group">
            <label htmlFor="indirectCosts">Indirect Costs per Hour ($)</label>
            <input
              type="number"
              id="indirectCosts"
              value={indirectCosts}
              onChange={(e) => setIndirectCosts(Number(e.target.value))}
              min="0"
              max="50"
              step="0.50"
            />
          </div>
          
          <div className="input-group">
            <label htmlFor="profitMargin">Target Profit Margin (%)</label>
            <input
              type="number"
              id="profitMargin"
              value={profitMargin}
              onChange={(e) => setProfitMargin(Number(e.target.value))}
              min="0"
              max="50"
              step="1"
            />
          </div>
        </div>
        
        <div className="calculator-results sticky top-[100px] shadow-lg rounded-lg p-[20px] bg-white border border-[#e2e8f0]">
          <h3>Cost Breakdown Results</h3>
          
          <div className="result-item">
            <span className="result-label">Base Annual Wages:</span>
            <span className="result-value">{formatCurrency(results.baseAnnual)}</span>
          </div>
          
          <div className="result-item">
            <span className="result-label">Payroll Taxes:</span>
            <span className="result-value">{formatCurrency(results.payrollTaxAmount)}</span>
          </div>
          
          <div className="result-item">
            <span className="result-label">Benefits Cost:</span>
            <span className="result-value">{formatCurrency(results.benefitsAmount)}</span>
          </div>
          
          <div className="result-item">
            <span className="result-label">Workers&apos; Compensation:</span>
            <span className="result-value">{formatCurrency(results.workersCompAmount)}</span>
          </div>
          
          <div className="result-item">
            <span className="result-label">Indirect Costs:</span>
            <span className="result-value">{formatCurrency(results.indirectAmount)}</span>
          </div>
          
          <div className="result-item">
            <span className="result-label">Total Annual Cost:</span>
            <span className="result-value">{formatCurrency(results.totalAnnual)}</span>
          </div>
          
          <div className="result-item">
            <span className="result-label">True Hourly Cost:</span>
            <span className="result-value">{formatCurrency(results.trueHourly, 2)}</span>
          </div>
          
          <div className="result-item">
            <span className="result-label">Recommended Billable Rate:</span>
            <span className="result-value">{formatCurrency(results.billableRate, 2)}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</>
};

export default LaborCostCalculator;
