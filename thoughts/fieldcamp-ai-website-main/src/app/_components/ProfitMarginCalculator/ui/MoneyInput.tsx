"use client";

import { DollarSign, HelpCircle } from "lucide-react";
import { Input } from "../ui/input";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "../ui/tooltip";

interface MoneyInputProps {
  value: number | '';
  onChange: (value: number) => void;
  label: string;
  tooltip: string;
}

export function MoneyInput({ value, onChange, label, tooltip }: MoneyInputProps) {
  return (
    <div className="space-y-2">
      <div className="flex items-center space-x-2">
        <label className="text-lg font-medium tracking-tight">{label}</label>
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger>
              <HelpCircle className="w-4 h-4 text-neutral-400" />
            </TooltipTrigger>
            <TooltipContent>
              <p className="pb-0">{tooltip}</p>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      </div>
      <div className="relative">
        <DollarSign className="absolute left-3 top-2.5 h-5 w-5 text-neutral-400" />
        <input
          type="number"
          value={value === 0 ? '' : value}
          onChange={(e) => onChange(e.target.value === '' ? 0 : Number(e.target.value))}
          className="pl-8 border-neutral-200 focus:border-black focus:ring-black flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          placeholder="0.00"
          onWheel={(e) => e.currentTarget.blur()}
        />
      </div>
    </div>
  );
}