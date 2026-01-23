"use client";

import { Input } from "../ui/input";
import { Button } from "../ui/button";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "../ui/collapsible";

interface OverheadCalculatorProps {
  monthlyExpenses: number;
  workingHours: number;
  jobHours: number;
  onMonthlyExpensesChange: (value: number) => void;
  onWorkingHoursChange: (value: number) => void;
  onJobHoursChange: (value: number) => void;
  onCalculate: () => void;
}

export function OverheadCalculator({
  monthlyExpenses,
  workingHours,
  jobHours,
  onMonthlyExpensesChange,
  onWorkingHoursChange,
  onJobHoursChange,
  onCalculate,
}: OverheadCalculatorProps) {
  return (
    <Collapsible className="w-full">
      <CollapsibleTrigger asChild>
        <Button 
          variant="outline" 
          className="w-full justify-between border-neutral-200 hover:border-black hover:bg-white"
        >
          Calculate Overhead Expenses
          <span className="text-xs">▼</span>
        </Button>
      </CollapsibleTrigger>
      <CollapsibleContent className="space-y-3 mt-3 bg-neutral-50 p-4 rounded-lg">
        <Input
          type="number"
          placeholder="Monthly expenses"
          value={monthlyExpenses === 0 ? '' : monthlyExpenses}
          onChange={(e) => onMonthlyExpensesChange(Number(e.target.value))}
          onWheel={(e) => e.currentTarget.blur()}
          className="border-neutral-200 focus:border-black focus:ring-black"
        />
        <Input
          type="number"
          placeholder="Working hours per month"
          value={workingHours === 0 ? '' : workingHours}
          onChange={(e) => onWorkingHoursChange(Number(e.target.value))}
          onWheel={(e) => e.currentTarget.blur()}
          className="border-neutral-200 focus:border-black focus:ring-black"
        />
        <Input
          type="number"
          placeholder="Hours for this job"
          value={jobHours === 0 ? '' : jobHours}
          onChange={(e) => onJobHoursChange(Number(e.target.value))}
          onWheel={(e) => e.currentTarget.blur()}
          className="border-neutral-200 focus:border-black focus:ring-black"
        />
        <Button 
          onClick={onCalculate} 
          className="w-full bg-black hover:bg-neutral-800 text-white"
        >
          Calculate
        </Button>
      </CollapsibleContent>
    </Collapsible>
  );
}