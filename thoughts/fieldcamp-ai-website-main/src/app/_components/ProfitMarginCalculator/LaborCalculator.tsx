"use client";

import { useState } from "react";
import { Calculator } from "lucide-react";
import { MoneyInput } from "./ui/MoneyInput";
import { LaborResultsCard } from "./calculator/LaborResultsCard";

export default function LaborCalculator() {
  const [hourlySalary, setHourlySalary] = useState<number>(0);
  const [directHourlyCosts, setDirectHourlyCosts] = useState<number>(0);
  const [inDirectHourlyCosts, setInDirectHourlyCosts] = useState<number>(0);
  const [hourlyProfit, setHourlyProfit] = useState<number>(0);
 
  const totalCosts = hourlySalary + directHourlyCosts + inDirectHourlyCosts + hourlyProfit;
  


  return (
    <div className="max-w-5xl mx-auto space-y-12">
      <div className="flex items-center space-x-3">
        <Calculator className="w-8 h-8" />
        <h2 className="text-3xl font-light tracking-tight">Labor Cost Calculator</h2>
      </div>

      <div className="grid lg:grid-cols-2 gap-8">
        <div className="space-y-6">
          <MoneyInput
            value={hourlySalary}
            onChange={setHourlySalary}
            label="Combined Hourly Salary"
            tooltip="The total hourly wages paid to employees"
          />

          <MoneyInput
            value={directHourlyCosts}
            onChange={setDirectHourlyCosts}
            label="Combined Direct Hourly Costs"
            tooltip="Direct costs like materials and equipment per hour"
          />

          <MoneyInput
            value={inDirectHourlyCosts}
            onChange={setInDirectHourlyCosts}
            label="Combined Indirect Hourly Costs"
            tooltip="Indirect costs like overhead and utilities per hour"
          />
          <MoneyInput
            value={hourlyProfit}
            onChange={setHourlyProfit}
            label="Desired Hourly Profit"
            tooltip="Your target profit margin per hour"
          />
        </div>

        <LaborResultsCard
          totalCosts={totalCosts}
        />
      </div>
    </div>
  );
}