"use client";

import { Card } from "./ui/card";
import { DollarSign, Percent, TrendingUp } from "lucide-react";

interface ServiceResultsCardProps {
  recommendedPrice: number;
  profitMargin: number;
  markup: number;
}

export function ServiceResultsCard({ 
  recommendedPrice, 
  profitMargin, 
  markup 
}: ServiceResultsCardProps) {
  return (
    <Card className="p-8 bg-black text-white">
      <div className="space-y-8">
        <div>
          <div className="flex items-center space-x-2 mb-4">
            <DollarSign className="w-6 h-6 text-neutral-400" />
            <h2 className="text-2xl font-medium text-white">Recommended Price</h2>
          </div>
          <div className="text-7xl font-light tracking-tight mb-2">
            ${recommendedPrice.toFixed(2)}
          </div>
          <div className="h-0.5 bg-white/20 w-full" />
        </div>

        <div className="space-y-6">
          <div>
            <div className="flex items-center space-x-2 mb-2">
              <Percent className="w-5 h-5 text-neutral-400" />
              <h3 className="text-lg text-neutral-400 pb-0">Profit Margin</h3>
            </div>
            <p className="text-3xl font-light text-white  ">{profitMargin.toFixed(2)}%</p>
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
    </Card>
  );
}