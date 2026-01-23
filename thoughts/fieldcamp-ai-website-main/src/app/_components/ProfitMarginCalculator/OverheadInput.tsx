"use client";

import { MoneyInput } from "./ui/MoneyInput";
import { OverheadCalculator } from "./OverheadCalculator";

interface OverheadInputProps {
  overheadExpenses: number;
  monthlyExpenses: number;
  workingHours: number;
  jobHours: number;
  onOverheadExpensesChange: (value: number) => void;
  onMonthlyExpensesChange: (value: number) => void;
  onWorkingHoursChange: (value: number) => void;
  onJobHoursChange: (value: number) => void;
  onCalculate: () => void;
}

export function OverheadInput({
  overheadExpenses,
  monthlyExpenses,
  workingHours,
  jobHours,
  onOverheadExpensesChange,
  onMonthlyExpensesChange,
  onWorkingHoursChange,
  onJobHoursChange,
  onCalculate,
}: OverheadInputProps) {
  return (
    <div className="space-y-2">
      <MoneyInput
        value={overheadExpenses}
        onChange={onOverheadExpensesChange}
        label="Overhead Expenses"
        tooltip="Total overhead costs for this service"
      />
      <OverheadCalculator
        monthlyExpenses={monthlyExpenses}
        workingHours={workingHours}
        jobHours={jobHours}
        onMonthlyExpensesChange={onMonthlyExpensesChange}
        onWorkingHoursChange={onWorkingHoursChange}
        onJobHoursChange={onJobHoursChange}
        onCalculate={onCalculate}
      />
    </div>
  );
}