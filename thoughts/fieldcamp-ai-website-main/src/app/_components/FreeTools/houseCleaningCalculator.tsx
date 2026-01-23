'use client';

import React, { useState, useEffect } from 'react';
import { CalendlyEmbed } from '../General/Custom';

type VisitType = 'One-time' | 'Regular weekly/biweekly' | 'Move-in/Move-out';
const visitTypeOptions: VisitType[] = [
  'One-time',
  'Regular weekly/biweekly',
  'Move-in/Move-out',
];

const addOnOptions = [
  { label: 'Floor Cleaning', value: 'floorCleaning', cost: 20 },
  { label: 'Appliances', value: 'appliances', cost: 25 },
  { label: 'Window Cleaning', value: 'windowCleaning', cost: 30 },
  { label: 'Laundry', value: 'laundry', cost: 15 },
];

const baseCostPerRoom = 50;
const baseCostPerBathroom = 30;
const visitTypeMultiplier = {
  'One-time': 1.2,
  'Regular weekly/biweekly': 1,
  'Move-in/Move-out': 1.5,
};

const HouseCleaningCalculator = () => {
  const [calculationMode, setCalculationMode] = useState<'rooms' | 'squareFootage'>('rooms');
  const [visitType, setVisitType] = useState<VisitType>(visitTypeOptions[1]);
  const [bedrooms, setBedrooms] = useState(1);
  const [bathrooms, setBathrooms] = useState(1);
  const [addOns, setAddOns] = useState<string[]>([]);
  const [squareFootage, setSquareFootage] = useState(1000);
  const [estimatedCost, setEstimatedCost] = useState<[number, number]>([0, 0]);

  useEffect(() => {
    let baseCost = 0;

    if (calculationMode === 'rooms') {
      baseCost = (bedrooms * baseCostPerRoom) + (bathrooms * baseCostPerBathroom);
    } else {
      baseCost = (squareFootage / 1000) * 120; // e.g., $120 per 1000 sqft
    }

    const addOnsCost = addOns.reduce((total, key) => {
      const item = addOnOptions.find(addon => addon.value === key);
      return item ? total + item.cost : total;
    }, 0);

    const multiplier = visitTypeMultiplier[visitType];
    const total = (baseCost + addOnsCost) * multiplier;

    // +/- 10% range
    const min = Math.round(total * 0.9);
    const max = Math.round(total * 1.1);
    setEstimatedCost([min, max]);
  }, [bedrooms, bathrooms, squareFootage, addOns, visitType, calculationMode]);

  return (
    
    <div className="rounded-xl overflow-hidden md:p-6 space-y-8">
      <CalendlyEmbed/>
      <div className="text-center space-y-2">
        <h2 className="text-3xl font-light tracking-tight text-gray-900 mb-0">Cleaning Cost Calculator</h2>
        <p className="text-gray-600">Get an instant estimate for your cleaning service</p>
      </div>

      <div className="grid md:grid-cols-2 gap-8">
        {/* Left Column */}
        <div className="calculator-inputs shadow-lg rounded-lg p-6 bg-white border border-[#e2e8f0]">
          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Calculation Method</label>
            <div className="flex rounded-md shadow-sm" role="group">
              <button
                type="button"
                className={`flex-1 px-4 py-2 text-sm font-medium rounded-l-lg border ${
                  calculationMode === 'rooms' 
                    ? 'bg-black text-white border-black' 
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                }`}
                onClick={() => setCalculationMode('rooms')}
              >
                By Rooms
              </button>
              <button
                type="button"
                className={`flex-1 px-4 py-2 text-sm font-medium rounded-r-lg border ${
                  calculationMode === 'squareFootage' 
                    ? 'bg-black text-white border-black' 
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                }`}
                onClick={() => setCalculationMode('squareFootage')}
              >
                By Square Footage
              </button>
            </div>
          </div>

          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Service Type</label>
            <select
              className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              value={visitType}
              onChange={(e) => setVisitType(e.target.value as VisitType)}
            >
              {visitTypeOptions.map((option) => (
                <option key={option} value={option}>
                  {option}
                </option>
              ))}
            </select>
          </div>

          {calculationMode === 'rooms' ? (
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <label className="block text-sm font-medium text-gray-700">Bedrooms</label>
                <div className="flex rounded-md shadow-sm">
                  <button
                    type="button"
                    className="px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-700 hover:bg-gray-200"
                    onClick={() => setBedrooms(Math.max(1, bedrooms - 1))}
                  >
                    -
                  </button>
                  <input
                    type="number"
                    min="1"
                    className="flex-1 min-w-0 block w-full px-3 py-2 text-center border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm border appearance-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                    value={bedrooms}
                    onChange={(e) => setBedrooms(Math.max(1, Number(e.target.value) || 1))}
                  />
                  <button
                    type="button"
                    className="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md text-gray-700 hover:bg-gray-200"
                    onClick={() => setBedrooms(bedrooms + 1)}
                  >
                    +
                  </button>
                </div>
              </div>
              <div className="space-y-2">
                <label className="block text-sm font-medium text-gray-700">Bathrooms</label>
                <div className="flex rounded-md shadow-sm">
                  <button
                    type="button"
                    className="px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-700 hover:bg-gray-200"
                    onClick={() => setBathrooms(Math.max(1, bathrooms - 1))}
                  >
                    -
                  </button>
                  <input
                    type="number"
                    min="1"
                    className="flex-1 min-w-0 block w-full px-3 py-2 text-center border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm border appearance-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                    value={bathrooms}
                    onChange={(e) => setBathrooms(Math.max(1, Number(e.target.value) || 1))}
                  />
                  <button
                    type="button"
                    className="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md text-gray-700 hover:bg-gray-200"
                    onClick={() => setBathrooms(bathrooms + 1)}
                  >
                    +
                  </button>
                </div>
              </div>
            </div>
          ) : (
            <div className="space-y-2">
              <label className="block text-sm font-medium text-gray-700">
                Square Footage
              </label>
              <div className="mt-1 relative rounded-md shadow-sm">
                <input
                  type="number"
                  min="100"
                  // step="100"
                  className="focus:ring-blue-500 focus:border-blue-500 block w-full pl-4 pr-12 py-2 sm:text-sm border-gray-300 rounded-md appearance-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                  value={squareFootage}
                  onChange={(e) => {
                    const value = e.target.value;
                    // Allow empty string for better UX when deleting
                    if (value === '') {
                      setSquareFootage(100); // Set to default when empty
                      return;
                    }
                    // Only update if it's a valid number
                    const numValue = Number(value);
                    if (!isNaN(numValue)) {
                      setSquareFootage(Math.max(100, numValue));
                    }
                  }}
                  onBlur={(e) => {
                    // When input loses focus, ensure it's at least 100
                    if (e.target.value === '') {
                      setSquareFootage(100);
                    }
                  }}
                />
                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <span className="text-gray-500 sm:text-sm">sq ft</span>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Right Column */}
        <div className="calculator-results sticky top-[100px] shadow-lg rounded-lg p-[20px] bg-white border border-[#e2e8f0]">
          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Add-on Services</label>
            <div className="space-y-3">
              {addOnOptions.map((addon) => (
                <div key={addon.value} className="flex items-center">
                  <input
                    id={`addon-${addon.value}`}
                    name="addons"
                    type="checkbox"
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    checked={addOns.includes(addon.value)}
                    onChange={(e) => {
                      const checked = e.target.checked;
                      setAddOns((prev) =>
                        checked
                          ? [...prev, addon.value]
                          : prev.filter((a) => a !== addon.value)
                      );
                    }}
                  />
                  <label
                    htmlFor={`addon-${addon.value}`}
                    className="ml-3 text-sm text-gray-700"
                  >
                    {addon.label} <span className="text-gray-500">(+${addon.cost})</span>
                  </label>
                </div>
              ))}
            </div>
          </div>

          <div className="bg-gray-100 p-4 rounded-lg border border-gray-200">
            <h3 className="text-lg font-medium text-gray-900">Estimated Cost</h3>
            <div className="mt-2">
              <p className="text-3xl font-bold text-black">
                ${estimatedCost[0]} - ${estimatedCost[1]}
              </p>
              <p className="mt-1 text-sm text-gray-600">
                Based on your selections and location
              </p>
            </div>
          </div>

          <a href="https://calendly.com/jeel-fieldcamp/30min"
            className="calendly-open w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-black hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Book This Service
          </a>
        </div>
      </div>
    </div>
  );
};

export default HouseCleaningCalculator;
