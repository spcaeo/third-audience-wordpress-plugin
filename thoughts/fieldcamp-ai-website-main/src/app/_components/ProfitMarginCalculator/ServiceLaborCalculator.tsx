"use client";

import { Input } from "./ui/input";
import { Button } from "./ui/button";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "./ui/collapsible";

interface LaborCalculatorProps {
  numWorkers: number;
  hoursPerJob: number;
  hourlyRate: number;
  onNumWorkersChange: (value: number) => void;
  onHoursPerJobChange: (value: number) => void;
  onHourlyRateChange: (value: number) => void;
  onCalculate: () => void;
}

export function LaborCalculator({
  numWorkers,
  hoursPerJob,
  hourlyRate,
  onNumWorkersChange,
  onHoursPerJobChange,
  onHourlyRateChange,
  onCalculate,
}: LaborCalculatorProps) {
  return (
    <Collapsible className="w-full">
      <CollapsibleTrigger asChild>
        <Button 
          variant="outline" 
          className="w-full justify-between border-neutral-200 hover:border-black hover:bg-white"
        >
          Calculate Labor Costs
          <span className="text-xs">▼</span>
        </Button>
      </CollapsibleTrigger>
      <CollapsibleContent className="space-y-3 mt-3 bg-neutral-50 p-4 rounded-lg">
        <Input
          type="number"
          placeholder="Number of workers"
          value={numWorkers === 0 ? '' : numWorkers}
          onChange={(e) => onNumWorkersChange(Number(e.target.value))}
          onWheel={(e) => e.currentTarget.blur()}
          className="border-neutral-200 focus:border-black focus:ring-black"
        />
        <Input
          type="number"
          placeholder="Hours per job"
          value={hoursPerJob === 0 ? '' : hoursPerJob}
          onChange={(e) => onHoursPerJobChange(Number(e.target.value))}
          onWheel={(e) => e.currentTarget.blur()}
          className="border-neutral-200 focus:border-black focus:ring-black"
        />
        <Input
          type="number"
          placeholder="Hourly rate"
          value={hourlyRate === 0 ? '' : hourlyRate}
          onChange={(e) => onHourlyRateChange(Number(e.target.value))}
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