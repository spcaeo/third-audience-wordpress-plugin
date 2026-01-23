"use client";

import { useState } from "react";
import { Calculator } from "lucide-react";
import { MoneyInput } from "./ui/MoneyInput";
import { ResultsCard } from "./calculator/ResultsCard";
import { OverheadCalculator } from "./calculator/OverheadCalculator";

export default function ProfitCalculator() {
  const [laborCosts, setLaborCosts] = useState<number>(0);
  const [materialCosts, setMaterialCosts] = useState<number>(0);
  const [overheadExpenses, setOverheadExpenses] = useState<number>(0);
  const [servicePrice, setServicePrice] = useState<number>(0);
  const [monthlyExpenses, setMonthlyExpenses] = useState<number>(0);
  const [workingHours, setWorkingHours] = useState<number>(0);
  const [jobHours, setJobHours] = useState<number>(0);

  const totalCosts = laborCosts + materialCosts + overheadExpenses;
  const profit = servicePrice - totalCosts;
  const profitMargin = servicePrice > 0 ? (profit / servicePrice) * 100 : 0;
  const markup = totalCosts > 0 ? ((servicePrice - totalCosts) / totalCosts) * 100 : 0;

  const calculateOverhead = () => {
    if (workingHours > 0) {
      const hourlyOverhead = monthlyExpenses / workingHours;
      const totalOverhead = hourlyOverhead * jobHours;
      setOverheadExpenses(totalOverhead);
    }
  };

  return (
    <div className="max-w-5xl mx-auto space-y-12">
      <div className="flex items-center space-x-3">
        <Calculator className="w-8 h-8" />
        <h2 className="text-3xl font-light tracking-tight">Profit Calculator</h2>
      </div>

      <div className="grid lg:grid-cols-2 gap-8">
        <div className="space-y-6">
          <MoneyInput
            value={laborCosts}
            onChange={setLaborCosts}
            label="Labor Costs"
            tooltip="Direct labor costs for the project"
          />

          <MoneyInput
            value={materialCosts}
            onChange={setMaterialCosts}
            label="Material Costs"
            tooltip="Cost of materials used in the project"
          />

          <div className="space-y-2">
            <MoneyInput
              value={overheadExpenses}
              onChange={setOverheadExpenses}
              label="Overhead Expenses"
              tooltip="Indirect costs associated with the project"
            />
            <OverheadCalculator
              monthlyExpenses={monthlyExpenses}
              workingHours={workingHours}
              jobHours={jobHours}
              onMonthlyExpensesChange={setMonthlyExpenses}
              onWorkingHoursChange={setWorkingHours}
              onJobHoursChange={setJobHours}
              onCalculate={calculateOverhead}
            />
          </div>

          <MoneyInput
            value={servicePrice}
            onChange={setServicePrice}
            label="Service Price"
            tooltip="Final price charged to the customer"
          />
        </div>

        <ResultsCard
          profitMargin={profitMargin}
          profit={profit}
          markup={markup}
        />
      </div>
    </div>
  );
}