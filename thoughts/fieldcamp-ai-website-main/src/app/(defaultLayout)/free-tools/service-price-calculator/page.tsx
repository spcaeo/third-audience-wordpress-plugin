
import { Calculator, ContentSections, FaqSection, HeroSection, HowToUseSection } from "@/app/_components/FreeTools/serviceCostCalculator";


// --- New page2.tsx (combining all sections) ---
export default function ServicePricePage2() {
  return (
    <main className="min-h-screen bg-white">
      <HeroSection />
      <Calculator />
      <HowToUseSection />
      <ContentSections />
      <FaqSection />
    </main>
  );
} 


import { Metadata } from "next";

export const metadata: Metadata = {
  title: "Free Pricing Calculator - Calculate Service Costs & Quotes",
  description:
    "Calculate accurate service pricing with our free calculator. Find out how much to charge for labor, estimate job costs, and create professional quotes.",
    robots: 'index, follow',
    alternates: { canonical: "https://fieldcamp.ai/free-tools/service-price-calculator/" },
  openGraph: {
    title: "Free Pricing Calculator - Calculate Service Costs & Quotes",
    description:
      "Calculate accurate service pricing with our free calculator. Find out how much to charge for labor, estimate job costs, and create professional quotes.",
    url: "https://fieldcamp.ai/free-tools/service-price-calculator/",
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