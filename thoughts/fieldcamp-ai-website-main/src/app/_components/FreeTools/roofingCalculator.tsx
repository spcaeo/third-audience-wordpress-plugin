'use client';

import React, { useState, useEffect } from 'react';
import { CalendlyEmbed } from '../General/Custom';

type RoofType = 'Asphalt Shingle' | 'Metal' | 'Tile' | 'Flat' | 'Wood Shake' | 'Slate';
type RoofPitch = 'Low Slope' | 'Medium Slope' | 'Steep Slope' | 'Very Steep';
type ProjectType = 'Replacement' | 'Repair' | 'New Construction';
type AdditionalService = 'Gutters' | 'Skylights' | 'Chimney Work' | 'Roof Ventilation';

interface ServiceOption {
  label: string;
  value: string;
  costMultiplier: number;
}

const propertySizes = [
  { label: 'Less than 1,000 sq ft', value: 'small', multiplier: 1.0 },
  { label: '1,000 - 2,000 sq ft', value: 'medium', multiplier: 1.8 },
  { label: '2,000 - 3,000 sq ft', value: 'large', multiplier: 2.5 },
  { label: '3,000+ sq ft', value: 'xlarge', multiplier: 3.5 },
];

// Base rates per square foot for different roof types
const roofTypeRates: Record<RoofType, number> = {
  'Asphalt Shingle': 3.50,
  'Metal': 8.00,
  'Tile': 10.00,
  'Flat': 4.50,
  'Wood Shake': 7.50,
  'Slate': 12.00,
};

// Pitch difficulty multipliers
const pitchMultipliers: Record<RoofPitch, number> = {
  'Low Slope': 0.9,
  'Medium Slope': 1.0,
  'Steep Slope': 1.3,
  'Very Steep': 1.6,
};

// Project type multipliers
const projectTypeMultipliers: Record<ProjectType, number> = {
  'Replacement': 1.0,
  'Repair': 1.5,  // Higher due to potential for unexpected issues
  'New Construction': 0.9,  // Lower as it's typically more straightforward
};

// Additional services with their cost multipliers
const additionalServices: ServiceOption[] = [
  { label: 'Gutters', value: 'gutters', costMultiplier: 1.1 },
  { label: 'Skylights', value: 'skylights', costMultiplier: 1.15 },
  { label: 'Chimney Work', value: 'chimney', costMultiplier: 1.2 },
  { label: 'Roof Ventilation', value: 'ventilation', costMultiplier: 1.05 },
];

const RoofingCalculator = () => {
  const [propertySize, setPropertySize] = useState(propertySizes[1].value);
  const [squareFootage, setSquareFootage] = useState(1500);
  const [roofType, setRoofType] = useState<RoofType>('Asphalt Shingle');
  const [roofPitch, setRoofPitch] = useState<RoofPitch>('Medium Slope');
  const [projectType, setProjectType] = useState<ProjectType>('Replacement');
  const [selectedServices, setSelectedServices] = useState<AdditionalService[]>([]);
  const [estimatedCost, setEstimatedCost] = useState<[number, number]>([0, 0]);

  useEffect(() => {
    calculateEstimate();
  }, [propertySize, squareFootage, roofType, roofPitch, projectType, selectedServices]);

  const calculateEstimate = () => {
    const size = propertySizes.find(s => s.value === propertySize);
    if (!size) return;

    // Get base rate for the selected roof type
    let baseRate = roofTypeRates[roofType] || 0;

    // Apply pitch multiplier
    baseRate *= pitchMultipliers[roofPitch] || 1;

    // Apply project type multiplier
    baseRate *= projectTypeMultipliers[projectType] || 1;

    // Apply additional services multipliers
    selectedServices.forEach(service => {
      const serviceInfo = additionalServices.find(s => s.value === service.toLowerCase().replace(' ', ''));
      if (serviceInfo) {
        baseRate *= serviceInfo.costMultiplier;
      }
    });

    // Calculate total cost
    const total = baseRate * squareFootage;

    // Calculate range (+/- 20% for roofing estimates)
    const min = Math.round(total * 0.8);
    const max = Math.round(total * 1.2);

    setEstimatedCost([min, max]);
  };

  const handleSquareFootageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value;
    if (value === '') {
      setSquareFootage(1000);
      return;
    }
    const numValue = Number(value);
    if (!isNaN(numValue)) {
      setSquareFootage(Math.max(100, numValue));
    }
  };

  const handlePropertySizeChange = (size: string) => {
    setPropertySize(size);
    const selected = propertySizes.find(s => s.value === size);
    if (selected) {
      setSquareFootage(selected.multiplier * 1000);
    }
  };

  const toggleService = (service: AdditionalService) => {
    setSelectedServices(prev => 
      prev.includes(service)
        ? prev.filter(item => item !== service)
        : [...prev, service]
    );
  };

  return (
    <div className="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6 space-y-8" id="roofing-cost-calculate">
      <CalendlyEmbed/>
      <div className="text-center space-y-2">
        <h2 className="text-3xl font-light tracking-tight text-gray-900">Roofing Cost Calculator</h2>
        <p className="text-gray-600">Get an instant estimate for your roofing project</p>
      </div>

      <div className="grid md:grid-cols-2 gap-8">
        {/* Left Column */}
        <div className="calculator-inputs shadow-lg rounded-lg p-6 bg-white border border-[#e2e8f0]">
          <div className="space-y-4">
            <label className="block text-sm font-medium text-gray-700">Property Size</label>
            <div className="space-y-2">
              {propertySizes.map((size) => (
                <div key={size.value} className="flex items-center">
                  <input
                    id={`size-${size.value}`}
                    name="property-size"
                    type="radio"
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                    checked={propertySize === size.value}
                    onChange={() => handlePropertySizeChange(size.value)}
                  />
                  <label htmlFor={`size-${size.value}`} className="ml-3 block text-sm font-medium text-gray-700">
                    {size.label}
                  </label>
                </div>
              ))}
            </div>
          </div>

          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Roof Area (sq ft)</label>
            <div className="mt-1 relative rounded-md shadow-sm">
              <input
                type="number"
                min="100"
                className="focus:ring-blue-500 focus:border-blue-500 block w-full pl-4 pr-12 py-2 sm:text-sm border-gray-300 rounded-md"
                value={squareFootage}
                onChange={handleSquareFootageChange}
              />
            </div>
          </div>

          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Roof Type</label>
            <select
              className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              value={roofType}
              onChange={(e) => setRoofType(e.target.value as RoofType)}
            >
              {Object.keys(roofTypeRates).map((type) => (
                <option key={type} value={type}>
                  {type}
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* Right Column */}
        <div className="calculator-results sticky top-[100px] shadow-lg rounded-lg p-[20px] bg-white border border-[#e2e8f0]">
          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Roof Pitch</label>
            <select
              className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              value={roofPitch}
              onChange={(e) => setRoofPitch(e.target.value as RoofPitch)}
            >
              {Object.keys(pitchMultipliers).map((pitch) => (
                <option key={pitch} value={pitch}>
                  {pitch}
                </option>
              ))}
            </select>
          </div>

          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Project Type</label>
            <select
              className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              value={projectType}
              onChange={(e) => setProjectType(e.target.value as ProjectType)}
            >
              {Object.keys(projectTypeMultipliers).map((type) => (
                <option key={type} value={type}>
                  {type}
                </option>
              ))}
            </select>
          </div>

          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Additional Services</label>
            <div className="space-y-3">
              {additionalServices.map((service) => (
                <div key={service.value} className="flex items-center">
                  <input
                    id={`service-${service.value}`}
                    name="services"
                    type="checkbox"
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    checked={selectedServices.includes(service.label as AdditionalService)}
                    onChange={() => toggleService(service.label as AdditionalService)}
                  />
                  <label
                    htmlFor={`service-${service.value}`}
                    className="ml-3 text-sm text-gray-700"
                  >
                    {service.label}
                  </label>
                </div>
              ))}
            </div>
          </div>

          <div className="bg-gray-100 p-4 rounded-lg border border-gray-200">
            <h3 className="text-lg font-medium text-gray-900">Estimated Cost</h3>
            <div className="mt-2">
              <p className="text-3xl font-bold text-black">
                ${estimatedCost[0].toLocaleString()} - ${estimatedCost[1].toLocaleString()}
              </p>
              <p className="mt-1 text-sm text-gray-600">
                Based on your selections and local labor costs
              </p>
            </div>
          </div>

          <a href="https://calendly.com/jeel-fieldcamp/30min"
            className="calendly-open w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Get a Free Quote
          </a>
        </div>
      </div>
    </div>
  );
};

export default RoofingCalculator;