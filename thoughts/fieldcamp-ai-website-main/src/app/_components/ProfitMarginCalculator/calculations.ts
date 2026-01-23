export const calculateLaborCost = (
  numWorkers: number,
  hoursPerJob: number,
  hourlyRate: number
): number => {
  return numWorkers * hoursPerJob * hourlyRate;
};

export const calculateOverheadExpense = (
  monthlyExpenses: number,
  workingHours: number,
  jobHours: number
): number => {
  if (workingHours <= 0) return 0;
  const hourlyOverhead = monthlyExpenses / workingHours;
  return hourlyOverhead * jobHours;
};

export const calculateServicePrice = (
  laborCosts: number,
  materialCosts: number,
  overheadExpenses: number,
  targetProfit: number
): {
  recommendedPrice: number;
  profitMargin: number;
  markup: number;
} => {
  // Calculate total costs
  const totalCosts = laborCosts + materialCosts + overheadExpenses;
  
  // Calculate recommended price by adding target profit to total costs
  const recommendedPrice = totalCosts + targetProfit;
  
  // Calculate profit margin as a percentage of the final price
  const profitMargin = recommendedPrice > 0 ? (targetProfit / recommendedPrice) * 100 : 0;
  
  // Calculate markup over costs
  const markup = totalCosts > 0 ? ((recommendedPrice - totalCosts) / totalCosts) * 100 : 0;

  return {
    recommendedPrice,
    profitMargin,
    markup
  };
};