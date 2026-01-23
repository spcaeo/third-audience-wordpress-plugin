'use client';

import React, { useState, useEffect } from 'react';
import { CalendlyEmbed } from '../General/Custom';

type ServiceType = 'Mowing' | 'Fertilization' | 'Weed Control' | 'Aeration' | 'Seeding' | 'Pest Control' | 'Leaf Removal' | 'Spring/Fall Cleanup' | 'Shrub & Tree Care';
type Frequency = 'One-time' | 'Weekly' | 'Bi-weekly' | 'Monthly';
type AerationType = 'Liquid' | 'Spike' | 'Core';
type SeedingType = 'Aeration & Overseeding' | 'Hydroseeding' | 'Slice Seeding';

interface ServiceOption {
  label: string;
  value: string;
  cost: number;
}

const propertySizes = [
  { label: 'Less than 1,000 sq ft', value: 'small', max: 1000 },
  { label: '1,000 - 5,000 sq ft', value: 'medium', min: 1000, max: 5000 },
  { label: '5,000 - 10,000 sq ft', value: 'large', min: 5000, max: 10000 },
  { label: '10,000+ sq ft', value: 'xlarge', min: 10000 },
];

// Base rates per square foot
const serviceRates = {
  'Mowing': 0.08,
  'Fertilization': 0.12,
  'Weed Control': 0.10,
  'Aeration': 0.15,
  'Seeding': 0.25,
  'Pest Control': 0.18,
  'Leaf Removal': 0.05,
  'Spring/Fall Cleanup': 0.15,
  'Shrub & Tree Care': 0.20,
};

// Additional cost multipliers for aeration types
const aerationMultipliers = {
  'Liquid': 1.2,
  'Spike': 1.0,
  'Core': 1.5,
};

// Additional cost multipliers for seeding types
const seedingMultipliers = {
  'Aeration & Overseeding': 1.0,
  'Hydroseeding': 1.8,
  'Slice Seeding': 1.4,
};

const frequencyMultipliers = {
  'One-time': 1,
  'Weekly': 3.5,    // Approx 4 weeks in a month
  'Bi-weekly': 1.75, // 2 times a month
  'Monthly': 1,
};

const additionalServices: ServiceOption[] = [
  { label: 'Edging', value: 'edging', cost: 15 },
  { label: 'Hedge Trimming', value: 'hedgeTrimming', cost: 25 },
  { label: 'Sprinkler Check', value: 'sprinkler', cost: 20 },
  { label: 'Gutter Cleaning', value: 'gutter', cost: 35 },
];

const LawnCareCalculator = () => {
  const [propertySize, setPropertySize] = useState(propertySizes[1].value);
  const [squareFootage, setSquareFootage] = useState(3000);
  const [selectedService, setSelectedService] = useState<ServiceType>('Mowing');
  const [aerationType, setAerationType] = useState<AerationType>('Core');
  const [seedingType, setSeedingType] = useState<SeedingType>('Aeration & Overseeding');
  const [frequency, setFrequency] = useState<Frequency>('One-time');
  const [selectedAddOns, setSelectedAddOns] = useState<string[]>([]);
  const [estimatedCost, setEstimatedCost] = useState<[number, number]>([0, 0]);

  useEffect(() => {
    calculateEstimate();
  }, [propertySize, squareFootage, selectedService, aerationType, seedingType, frequency, selectedAddOns]);

  const calculateEstimate = () => {
    const size = propertySizes.find(s => s.value === propertySize);
    if (!size) return;

    // Get base rate for the selected service
    let baseRate = serviceRates[selectedService] || 0.10;

    // Apply multipliers for aeration or seeding if selected
    if (selectedService === 'Aeration') {
      baseRate *= (aerationMultipliers[aerationType] || 1);
    } else if (selectedService === 'Seeding') {
      baseRate *= (seedingMultipliers[seedingType] || 1);
    }

    // Calculate base cost
    let total = baseRate * squareFootage;

    // Apply frequency multiplier
    total *= (frequencyMultipliers[frequency] || 1);

    // Add additional services
    const addOnsTotal = selectedAddOns.reduce((sum, addOn) => {
      const service = additionalServices.find(s => s.value === addOn);
      return sum + (service?.cost || 0);
    }, 0);

    // Calculate range (+/- 15%)
    const min = Math.round((total + addOnsTotal) * 0.85);
    const max = Math.round((total + addOnsTotal) * 1.15);

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
    if (selected?.max) {
      setSquareFootage(selected.max);
    }
  };

  const toggleAddOn = (value: string) => {
    setSelectedAddOns(prev => 
      prev.includes(value) 
        ? prev.filter(item => item !== value) 
        : [...prev, value]
    );
  };

  const renderServiceOptions = () => {
    if (selectedService === 'Aeration') {
      return (
        <div className="mt-2 space-y-2">
          <label className="block text-sm font-medium text-gray-700">Aeration Type</label>
          <select
            className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            value={aerationType}
            onChange={(e) => setAerationType(e.target.value as AerationType)}
          >
            {Object.entries(aerationMultipliers).map(([type]) => (
              <option key={type} value={type}>{type}</option>
            ))}
          </select>
        </div>
      );
    } else if (selectedService === 'Seeding') {
      return (
        <div className="mt-2 space-y-2">
          <label className="block text-sm font-medium text-gray-700">Seeding Type</label>
          <select
            className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            value={seedingType}
            onChange={(e) => setSeedingType(e.target.value as SeedingType)}
          >
            {Object.entries(seedingMultipliers).map(([type]) => (
              <option key={type} value={type}>{type}</option>
            ))}
          </select>
        </div>
      );
    }
    return null;
  };

  return (
    <div className="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6 space-y-8" id="cost-calc">
      <CalendlyEmbed/>
      <div className="text-center space-y-2">
        <h2 className="text-3xl font-light tracking-tight text-gray-900">Lawn Care Service Calculator</h2>
        <p className="text-gray-600">Get an instant estimate for your lawn care service</p>
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
            <label className="block text-sm font-medium text-gray-700">Square Footage</label>
            <div className="mt-1 relative rounded-md shadow-sm">
              <input
                type="number"
                min="100"
                className="focus:ring-blue-500 focus:border-blue-500 block w-full pl-4 pr-12 py-2 sm:text-sm border-gray-300 rounded-md"
                value={squareFootage}
                onChange={handleSquareFootageChange}
              />
              <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <span className="text-gray-500 sm:text-sm">sq ft</span>
              </div>
            </div>
          </div>

          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Service Type</label>
            <select
              className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              value={selectedService}
              onChange={(e) => setSelectedService(e.target.value as ServiceType)}
            >
              {Object.keys(serviceRates).map((service) => (
                <option key={service} value={service}>
                  {service}
                </option>
              ))}
            </select>
            {renderServiceOptions()}
          </div>

          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Service Frequency</label>
            <div className="grid grid-cols-2 gap-3">
              {(Object.keys(frequencyMultipliers) as Frequency[]).map((freq) => (
                <div key={freq} className="flex items-center">
                  <input
                    id={`freq-${freq}`}
                    name="frequency"
                    type="radio"
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500"
                    checked={frequency === freq}
                    onChange={() => setFrequency(freq)}
                  />
                  <label htmlFor={`freq-${freq}`} className="ml-2 block text-sm text-gray-700">
                    {freq}
                  </label>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Right Column */}
        <div className="calculator-results sticky top-[100px] shadow-lg rounded-lg p-[20px] bg-white border border-[#e2e8f0]">
          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Additional Services</label>
            <div className="space-y-3">
              {additionalServices.map((service) => (
                <div key={service.value} className="flex items-center">
                  <input
                    id={`addon-${service.value}`}
                    name="addons"
                    type="checkbox"
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    checked={selectedAddOns.includes(service.value)}
                    onChange={() => toggleAddOn(service.value)}
                  />
                  <label
                    htmlFor={`addon-${service.value}`}
                    className="ml-3 text-sm text-gray-700"
                  >
                    {service.label} <span className="text-gray-500">(+${service.cost})</span>
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

          <a 
            href="https://calendly.com/jeel-fieldcamp/30min"
            className="calendly-open w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Book This Service
          </a>
        </div>
      </div>
    </div>
  );
};

export default LawnCareCalculator;