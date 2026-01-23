import ProfitCalculator from "@/app/_components/ProfitMarginCalculator/ProfitCalculator";
import { Calculator, DollarSign, Lightbulb, LineChart, PieChart, Settings } from "lucide-react";

export default function Home() {
  return (
    <main className="min-h-screen bg-white">
      {/* Hero Section */}
      <section className="py-10 xl:py-24 mt-[72px]" style={{ backgroundImage: 'url(https://cms.fieldcamp.ai/wp-content/uploads/2025/01/profit-margin.png)', backgroundSize: 'cover', backgroundPosition: 'center' }}>
        <div className="max-w-5xl xl:mx-auto px-0 mx-5">
          <h1 className="text-[38px] xl:text-[55px] font-medium xl:pr-[283px] mb-6 text-black">
          Free Profit Margin Calculator to Maximize Your Profits
          </h1>
          <p className="text-xl text-black tracking-tight">
            Simple. Accurate. Beautiful.
          </p>
        </div>
      </section>

      {/* Calculator Section */}
      <section className="py-20 px-6">
        <ProfitCalculator />
      </section>

      {/* How to Use Section */}
      <section className="bg-neutral-50 py-20 px-6">
        <div className="max-w-5xl mx-auto space-y-16">
          <h2 className="text-4xl font-light tracking-tight text-center">How It Works</h2>
          
          <div className="grid md:grid-cols-3 gap-12">
            <div className="space-y-6 text-center">
              <div className="flex justify-center">
                <div className="w-16 h-16 bg-black rounded-full flex items-center justify-center">
                  <Calculator className="w-8 h-8 text-white" />
                </div>
              </div>
              <h3 className="text-xl font-medium">Enter Job Costs</h3>
              <ul className="space-y-3 text-neutral-600">
                <li>Input your labor costs</li>
                <li>Input material costs</li>
                <li>Include overhead expenses for the job</li>
              </ul>
            </div>
            
            <div className="space-y-6 text-center">
              <div className="flex justify-center">
                <div className="w-16 h-16 bg-black rounded-full flex items-center justify-center">
                  <DollarSign className="w-8 h-8 text-white" />
                </div>
              </div>
              <h3 className="text-xl font-medium">Input Service Price</h3>
              <ul className="space-y-3 text-neutral-600">
                <li>Enter the amount you charged the client for the service</li>
              </ul>
            </div>

            <div className="space-y-6 text-center">
              <div className="flex justify-center">
                <div className="w-16 h-16 bg-black rounded-full flex items-center justify-center">
                  <LineChart className="w-8 h-8 text-white" />
                </div>
              </div>
              <h3 className="text-xl font-medium">Calculate Profit Margin</h3>
              <ul className="space-y-3 text-neutral-600">
                <li>Click calculate to instantly see how profitable your current pricing strategy is</li>
              </ul>
            </div>
          </div>

          <div className="mt-20 bg-white rounded-2xl shadow-xl p-12 space-y-8">
            <h3 className="text-2xl font-medium text-center mb-12">Understanding Your Results</h3>
            <div className="grid md:grid-cols-3 gap-12">
              <div className="space-y-4">
                <div className="flex justify-center">
                  <div className="w-12 h-12 bg-black/10 rounded-full flex items-center justify-center">
                    <span className="text-xl font-medium">%</span>
                  </div>
                </div>
                <p className="font-medium text-center">Profit Margin</p>
                <p className="text-neutral-600 text-center text-sm">
                  View the percentage of the service price that is profit after covering all costs
                </p>
              </div>
              <div className="space-y-4">
                <div className="flex justify-center">
                  <div className="w-12 h-12 bg-black/10 rounded-full flex items-center justify-center">
                    <PieChart className="w-6 h-6" />
                  </div>
                </div>
                <p className="font-medium text-center">Cost Breakdown</p>
                <p className="text-neutral-600 text-center text-sm">
                  Understand how labor, materials, and overhead impact your profitability
                </p>
              </div>
              <div className="space-y-4">
                <div className="flex justify-center">
                  <div className="w-12 h-12 bg-black/10 rounded-full flex items-center justify-center">
                    <Lightbulb className="w-6 h-6" />
                  </div>
                </div>
                <p className="font-medium text-center">Actionable Insights</p>
                <p className="text-neutral-600 text-center text-sm">
                  Use the results to adjust your pricing strategy and improve profit margins
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-black text-white py-12">
        <div className="max-w-5xl mx-auto px-6 text-center">
          <p className="text-neutral-400">
            Make better business decisions with accurate profit calculations
          </p>
        </div>
      </footer>
    </main>
  );
}

import { Metadata } from "next";

export const metadata: Metadata = {
  title: "Accurate Profit Margin Calculator for Better Pricing Decisions",
  description: "Use our free profit margin calculator to assess your pricing strategy. Calculate labor, material, and overhead costs to evaluate your job profits!",
  robots: 'index, follow',
    alternates: { canonical: "https://fieldcamp.ai/free-tools/profit-margin-calculator/" },
  openGraph: {
    title: "Accurate Profit Margin Calculator for Better Pricing Decisions",
    description: "Use our free profit margin calculator to assess your pricing strategy. Calculate labor, material, and overhead costs to evaluate your job profits!",
    url: "https://fieldcamp.ai/free-tools/profit-margin-calculator/",
    // images: [
    //   {
    //     url: "https://www.fieldcamp.com/images/labor-cost-calculator-og.png",
    //     width: 800,
    //     height: 600,
    //     alt: "Labor Cost Calculator",
    //   },
    // ],
  },
};