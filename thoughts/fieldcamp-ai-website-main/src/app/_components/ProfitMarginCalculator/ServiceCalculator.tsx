"use client";

import { useState } from "react";
import { Calculator } from "lucide-react";

import { MoneyInput } from "./ui/MoneyInput";
import { ServiceResultsCard } from "./ServiceResultsCard";
import { LaborCalculator } from "./ServiceLaborCalculator";
import { OverheadInput } from "./OverheadInput";
import { calculateServicePrice, calculateOverheadExpense } from "./calculations";

export default function ServiceCalculator() {
  // Labor costs state
  const [laborCosts, setLaborCosts] = useState<number>(0);
  const [numWorkers, setNumWorkers] = useState<number>(0);
  const [hoursPerJob, setHoursPerJob] = useState<number>(0);
  const [hourlyRate, setHourlyRate] = useState<number>(0);

  // Other costs and rates state
  const [materialCosts, setMaterialCosts] = useState<number>(0);
  const [overheadExpenses, setOverheadExpenses] = useState<number>(0);
  const [monthlyExpenses, setMonthlyExpenses] = useState<number>(0);
  const [workingHours, setWorkingHours] = useState<number>(0);
  const [jobHours, setJobHours] = useState<number>(0);
  const [targetProfit, setTargetProfit] = useState<number>(0);

  const handleLaborCalculation = () => {
    const total = numWorkers * hoursPerJob * hourlyRate;
    setLaborCosts(total);
  };

  const handleOverheadCalculation = () => {
    const overhead = calculateOverheadExpense(monthlyExpenses, workingHours, jobHours);
    setOverheadExpenses(overhead);
  };

  // Calculate final price and metrics
  const {
    recommendedPrice,
    profitMargin,
    markup
  } = calculateServicePrice(
    laborCosts,
    materialCosts,
    overheadExpenses,
    targetProfit
  );

  return (
    <div className="max-w-5xl mx-auto space-y-12">
      <div className="flex items-center space-x-3">
        <Calculator className="w-8 h-8" />
        <h2 className="text-3xl font-light tracking-tight">Service Price Calculator</h2>
      </div>

      <div className="grid lg:grid-cols-2 gap-8">
        <div className="space-y-6">
          <div className="space-y-2">
            <MoneyInput
              value={laborCosts}
              onChange={setLaborCosts}
              label="Labor Costs"
              tooltip="Total cost of labor for the service"
            />
            <LaborCalculator
              numWorkers={numWorkers}
              hoursPerJob={hoursPerJob}
              hourlyRate={hourlyRate}
              onNumWorkersChange={setNumWorkers}
              onHoursPerJobChange={setHoursPerJob}
              onHourlyRateChange={setHourlyRate}
              onCalculate={handleLaborCalculation}
            />
          </div>

          <MoneyInput
            value={materialCosts}
            onChange={setMaterialCosts}
            label="Material Costs"
            tooltip="Cost of materials needed for the service"
          />

          <OverheadInput
            overheadExpenses={overheadExpenses}
            monthlyExpenses={monthlyExpenses}
            workingHours={workingHours}
            jobHours={jobHours}
            onOverheadExpensesChange={setOverheadExpenses}
            onMonthlyExpensesChange={setMonthlyExpenses}
            onWorkingHoursChange={setWorkingHours}
            onJobHoursChange={setJobHours}
            onCalculate={handleOverheadCalculation}
          />

          <MoneyInput
            value={targetProfit}
            onChange={setTargetProfit}
            label="Target Profit"
            tooltip="Desired profit amount in dollars"
          />
        </div>

        <ServiceResultsCard
          recommendedPrice={recommendedPrice}
          profitMargin={profitMargin}
          markup={markup}
        />
      </div>
    </div>
  );
}