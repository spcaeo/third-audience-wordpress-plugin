"use client";

import { Card } from "../ui/card";

import { DollarSign, Percent, TrendingUp } from "lucide-react";

interface ResultsCardProps {
  profitMargin: number;
  profit: number;
  markup: number;
}

export function ResultsCard({ profitMargin, profit, markup }: ResultsCardProps) {
  return (
    <div className="p-8 bg-black text-white rounded-lg border bg-card text-card-foreground shadow-sm">
      <div className="space-y-8">
        <div>
          <div className="flex items-center space-x-2 mb-4">
            <Percent className="w-6 h-6 text-neutral-400" />
            <h2 className="text-2xl font-medium text-white">Profit Margin</h2>
          </div>
          <div className="text-7xl font-light tracking-tight mb-2 text-white">
            {profitMargin.toFixed(2)}%
          </div>
          <div className="h-0.5 bg-white/20 w-full" />
        </div>

        <div className="space-y-6">
          <div>
            <div className="flex items-center space-x-2 mb-2">
              <DollarSign className="w-5 h-5 text-neutral-400" />
              <h3 className="text-lg text-neutral-400 pb-0">Profit</h3>
            </div>
            <p className="text-3xl font-light text-white">${profit.toFixed(2)}</p>
          </div>

          <div>
            <div className="flex items-center space-x-2 mb-2">
              <TrendingUp className="w-5 h-5 text-neutral-400" />
              <h3 className="text-lg text-neutral-400 pb-0">Markup</h3>
            </div>
            <p className="text-3xl font-light text-white">{markup.toFixed(2)}%</p>
          </div>
        </div>
      </div>
    </div>
  );
}