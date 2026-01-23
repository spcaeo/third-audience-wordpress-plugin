'use client';

import React, { useState, useEffect } from 'react';

type ClimateZone = 'Hot' | 'Moderate' | 'Cold';
type InsulationQuality = 'Poor' | 'Average' | 'Good' | 'Excellent';
type RoomType = 'Bedroom' | 'Living Room' | 'Kitchen' | 'Office' | 'Other';

const BTU_PER_SQFT: Record<ClimateZone, number> = {
  'Hot': 30,
  'Moderate': 25,
  'Cold': 20
};

const INSULATION_FACTOR: Record<InsulationQuality, number> = {
  'Poor': 1.2,
  'Average': 1.0,
  'Good': 0.9,
  'Excellent': 0.8
};

const OCCUPANT_FACTOR = 600; // BTUs per person
const WINDOW_FACTOR = 1000; // BTUs per window
const APPLIANCE_FACTOR = 4000; // BTUs for major appliances
const CEILING_HEIGHT_FACTOR = 1.2; // Multiplier for rooms with high ceilings

const HvacLoadCalculator = () => {
  const [length, setLength] = useState<string>('');
  const [width, setWidth] = useState<string>('');
  const [ceilingHeight, setCeilingHeight] = useState<number>(8);
  const [climateZone, setClimateZone] = useState<ClimateZone>('Moderate');
  const [insulation, setInsulation] = useState<InsulationQuality>('Average');
  const [occupants, setOccupants] = useState<number>(1);
  const [windows, setWindows] = useState<number>(1);
  const [hasAppliance, setHasAppliance] = useState<boolean>(false);
  const [roomType, setRoomType] = useState<RoomType>('Living Room');
  const [btuRequired, setBtuRequired] = useState<number | null>(null);
  const [tonnage, setTonnage] = useState<number | null>(null);

  const calculateLoad = () => {
    if (!length || !width) {
      setBtuRequired(null);
      setTonnage(null);
      return;
    }

    const len = parseFloat(length);
    const w = parseFloat(width);

    if (isNaN(len) || isNaN(w) || len <= 0 || w <= 0) {
      setBtuRequired(null);
      setTonnage(null);
      return;
    }

    // Base calculation
    const area = len * w;
    let btu = area * BTU_PER_SQFT[climateZone];

    // Apply insulation factor
    btu *= INSULATION_FACTOR[insulation];

    // Add BTUs for occupants
    btu += occupants * OCCUPANT_FACTOR;

    // Add BTUs for windows
    btu += windows * WINDOW_FACTOR;

    // Add BTUs for appliances if present
    if (hasAppliance) {
      btu += APPLIANCE_FACTOR;
    }

    // Adjust for ceiling height
    if (ceilingHeight > 8) {
      btu *= 1 + ((ceilingHeight - 8) * 0.1); // 10% increase per foot over 8ft
    }

    // Round to nearest 1000 BTUs
    const roundedBtu = Math.round(btu / 1000) * 1000;
    
    // Calculate tonnage (1 ton = 12,000 BTUs)
    const calculatedTonnage = roundedBtu / 12000;

    setBtuRequired(roundedBtu);
    setTonnage(calculatedTonnage);
  };

  useEffect(() => {
    calculateLoad();
  }, [length, width, ceilingHeight, climateZone, insulation, occupants, windows, hasAppliance, roomType]);

  return (
    <div className="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6 space-y-8" id="hvac-calculate-loader">
      <div className="text-center space-y-2">
        <h2 className="text-3xl font-light tracking-tight text-gray-900">HVAC Load Calculator</h2>
        <p className="text-gray-600">Calculate the required cooling capacity for your room</p>
      </div>

      <div className="grid md:grid-cols-2 gap-8">
        {/* Left Column - Inputs */}
        <div className="calculator-inputs shadow-lg rounded-lg p-6 bg-white border border-[#e2e8f0]">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <label className="block text-sm font-medium text-gray-700">Room Length (ft)</label>
              <input
                type="number"
                min="1"
                step="0.1"
                className="mt-1 block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                value={length}
                onChange={(e) => setLength(e.target.value)}
                placeholder="e.g. 15"
              />
            </div>
            
            <div className="space-y-2">
              <label className="block text-sm font-medium text-gray-700">Room Width (ft)</label>
              <input
                type="number"
                min="1"
                step="0.1"
                className="mt-1 block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                value={width}
                onChange={(e) => setWidth(e.target.value)}
                placeholder="e.g. 12"
              />
            </div>
          </div>

          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Ceiling Height (ft)</label>
            <select
              className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              value={ceilingHeight}
              onChange={(e) => setCeilingHeight(Number(e.target.value))}
            >
              {[8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20].map((height) => (
                <option key={height} value={height}>{height} ft</option>
              ))}
            </select>
          </div>

          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Climate Zone</label>
            <select
              className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              value={climateZone}
              onChange={(e) => setClimateZone(e.target.value as ClimateZone)}
            >
              <option value="Hot">Hot (Southern regions)</option>
              <option value="Moderate">Moderate (Central regions)</option>
              <option value="Cold">Cold (Northern regions)</option>
            </select>
          </div>

          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Insulation Quality</label>
            <select
              className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              value={insulation}
              onChange={(e) => setInsulation(e.target.value as InsulationQuality)}
            >
              <option value="Poor">Poor (Older home, drafty)</option>
              <option value="Average">Average (Standard insulation)</option>
              <option value="Good">Good (Newer home, well-insulated)</option>
              <option value="Excellent">Excellent (Energy efficient, tight construction)</option>
            </select>
          </div>
        </div>

        {/* Right Column - Additional Options and Results */}
        <div className="calculator-results sticky top-[100px] shadow-lg rounded-lg p-[20px] bg-white border border-[#e2e8f0]">
          <div className="space-y-4">
            <div className="space-y-2">
              <label className="block text-sm font-medium text-gray-700">Number of Occupants</label>
              <input
                type="number"
                min="1"
                className="mt-1 block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                value={occupants}
                onChange={(e) => setOccupants(Math.max(1, Number(e.target.value) || 1))}
              />
            </div>

            <div className="space-y-2">
              <label className="block text-sm font-medium text-gray-700">Number of Windows</label>
              <input
                type="number"
                min="0"
                className="mt-1 block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                value={windows}
                onChange={(e) => setWindows(Math.max(0, Number(e.target.value) || 0))}
              />
            </div>

            <div className="flex items-center">
              <input
                id="appliance"
                type="checkbox"
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                checked={hasAppliance}
                onChange={(e) => setHasAppliance(e.target.checked)}
              />
              <label htmlFor="appliance" className="ml-2 block text-sm text-gray-700">
                Room contains major appliances (refrigerator, oven, etc.)
              </label>
            </div>

            <div className="space-y-2">
              <label className="block text-sm font-medium text-gray-700">Room Type</label>
              <select
                className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                value={roomType}
                onChange={(e) => setRoomType(e.target.value as RoomType)}
              >
                <option value="Living Room">Living Room</option>
                <option value="Bedroom">Bedroom</option>
                <option value="Kitchen">Kitchen</option>
                <option value="Office">Office</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>

          <div className="bg-blue-50 p-6 rounded-lg border border-blue-200">
            <h3 className="text-lg font-medium text-blue-800 mb-4">Cooling Requirement</h3>
            
            <div className="space-y-4">
              <div className="flex justify-between items-center">
                <span className="text-sm font-medium text-gray-700">BTUs Required:</span>
                <span className="text-xl font-bold text-blue-900">
                  {btuRequired ? btuRequired.toLocaleString() : '--'} BTU/h
                </span>
              </div>
              
              <div className="flex justify-between items-center pt-2 border-t border-blue-100">
                <span className="text-sm font-medium text-gray-700">Recommended AC Size:</span>
                <span className="text-lg font-semibold text-blue-900">
                  {tonnage ? `${tonnage.toFixed(1)} Tons` : '--'}
                </span>
              </div>

              <div className="mt-4 p-3 bg-blue-100 rounded-md">
                <h4 className="text-sm font-medium text-blue-800 mb-1">Note:</h4>
                <p className="text-xs text-blue-700">
                  This is an estimate. For precise calculations, consult with an HVAC professional. 
                  Factors like sun exposure, window types, and building materials can affect the actual load.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default HvacLoadCalculator;