"use client";

import { Card } from "../ui/card";

import { DollarSign, Percent, TrendingUp } from "lucide-react";

interface ResultsCardProps {
  totalCosts: number;
  
}

export function LaborResultsCard({ totalCosts }: ResultsCardProps) {
  return (
    <div className="p-8 bg-black text-white rounded-lg border bg-card text-card-foreground shadow-sm">
      <div className="space-y-8">
        <div>
          <div className="flex items-center space-x-2 mb-4">
          
            <h2 className="text-2xl font-medium text-white">Hourly Rate:</h2>
          </div>
          <div className="text-7xl font-light tracking-tight mb-2 text-white">
          <DollarSign className="" />{totalCosts.toFixed(2)}
          </div>
          <div className="h-0.5 bg-white/20 w-full" />
        </div>

        
      </div>
    </div>
  );
}



