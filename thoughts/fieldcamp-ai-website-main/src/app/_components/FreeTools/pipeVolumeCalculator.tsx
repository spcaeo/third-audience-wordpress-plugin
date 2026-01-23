'use client';

import React, { useState } from 'react';

type Unit = 'mm' | 'cm' | 'm' | 'in' | 'ft';

const   PipeVolumeCalculator = () => {
  const [innerDiameter, setInnerDiameter] = useState<string>('');
  const [length, setLength] = useState<string>('');
  const [diameterUnit, setDiameterUnit] = useState<Unit>('mm');
  const [lengthUnit, setLengthUnit] = useState<Unit>('m');
  const [volume, setVolume] = useState<number | null>(null);

  const calculateVolume = () => {
    if (!innerDiameter || !length) {
      setVolume(null);
      return;
    }

    const diameter = parseFloat(innerDiameter);
    const len = parseFloat(length);

    if (isNaN(diameter) || isNaN(len) || diameter <= 0 || len <= 0) {
      setVolume(null);
      return;
    }

    // Convert all to meters for calculation
    let diameterMeters = diameter;
    let lengthMeters = len;

    // Convert diameter to meters
    switch (diameterUnit) {
      case 'mm':
        diameterMeters = diameter / 1000;
        break;
      case 'cm':
        diameterMeters = diameter / 100;
        break;
      case 'in':
        diameterMeters = diameter * 0.0254;
        break;
      case 'ft':
        diameterMeters = diameter * 0.3048;
        break;
      // 'm' is already in meters
    }

    // Convert length to meters
    switch (lengthUnit) {
      case 'mm':
        lengthMeters = len / 1000;
        break;
      case 'cm':
        lengthMeters = len / 100;
        break;
      case 'in':
        lengthMeters = len * 0.0254;
        break;
      case 'ft':
        lengthMeters = len * 0.3048;
        break;
      // 'm' is already in meters
    }

    // Calculate volume in cubic meters: V = πr²h
    const radiusMeters = diameterMeters / 2;
    const volumeCubicMeters = Math.PI * Math.pow(radiusMeters, 2) * lengthMeters;

    setVolume(volumeCubicMeters);
  };

  const formatVolume = (vol: number): string => {
    if (vol < 0.001) {
      // Less than 1 liter, show in milliliters
      return `${(vol * 1000000).toFixed(2)} mL`;
    } else if (vol < 1) {
      // Less than 1 cubic meter, show in liters
      return `${(vol * 1000).toFixed(2)} L`;
    } else if (vol < 1000) {
      // Less than 1000 cubic meters, show in cubic meters with 2 decimal places
      return `${vol.toFixed(2)} m³`;
    } else {
      // For larger volumes, show in cubic meters with comma separation
      return `${vol.toLocaleString(undefined, { maximumFractionDigits: 2 })} m³`;
    }
  };

  return (
    <div className="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6 space-y-6" id="cleaning-cost">
      <div className="text-center space-y-2">
        <h2 className="text-3xl font-light tracking-tight text-gray-900">Pipe Volume Calculator</h2>
        <p className="text-gray-600">Calculate the volume of a pipe based on inner diameter and length</p>
      </div>

      <div className="grid md:grid-cols-2 gap-8">
        {/* Left Column - Inputs */}
        <div className="calculator-inputs shadow-lg rounded-lg p-6 bg-white border border-[#e2e8f0]">
          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Inner Diameter</label>
            <div className="flex mt-1 rounded-md shadow-sm">
              <input
                type="number"
                min="0"
                step="0.01"
                className="flex-1 block px-3 py-2 rounded-l-md border border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="e.g. 100"
                value={innerDiameter}
                onChange={(e) => setInnerDiameter(e.target.value)}
              />
              <select
                className="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm rounded-r-md"
                value={diameterUnit}
                onChange={(e) => setDiameterUnit(e.target.value as Unit)}
              >
                <option value="mm">mm</option>
                <option value="cm">cm</option>
                <option value="m">m</option>
                <option value="in">in</option>
                <option value="ft">ft</option>
              </select>
            </div>
          </div>

          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">Length</label>
            <div className="flex mt-1 rounded-md shadow-sm">
              <input
                type="number"
                min="0"
                step="0.01"
                className="flex-1 block px-3 py-2 rounded-l-md border border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="e.g. 10"
                value={length}
                onChange={(e) => setLength(e.target.value)}
              />
              <select
                className="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm rounded-r-md"
                value={lengthUnit}
                onChange={(e) => setLengthUnit(e.target.value as Unit)}
              >
                <option value="mm">mm</option>
                <option value="cm">cm</option>
                <option value="m">m</option>
                <option value="in">in</option>
                <option value="ft">ft</option>
              </select>
            </div>
          </div>

          <button
            type="button"
            onClick={calculateVolume}
            className="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Calculate Volume
          </button>
        </div>

        {/* Right Column - Result */}
        <div className="calculator-results sticky top-[100px] shadow-lg rounded-lg p-[20px] bg-white border border-[#e2e8f0]">
          <div className="bg-gray-50 p-6 rounded-lg border border-gray-200 h-full flex flex-col justify-center">
            <h3 className="text-lg font-medium text-gray-900 mb-4">Calculation Results</h3>
            
            <div className="space-y-4">
              <div className="flex justify-between items-center">
                <span className="text-sm font-medium text-gray-700">Volume:</span>
                <span className="text-lg font-semibold text-gray-900">
                  {volume !== null ? formatVolume(volume) : '--'}
                </span>
              </div>
              
              <div className="pt-4 border-t border-gray-200">
                <h4 className="text-sm font-medium text-gray-700 mb-2">How to use:</h4>
                <ol className="text-sm text-gray-600 list-decimal pl-5 space-y-1">
                  <li>Enter the inner diameter of the pipe</li>
                  <li>Enter the length of the pipe</li>
                  <li>Select appropriate units for each measurement</li>
                  <li>Click &quot;Calculate Volume&quot;</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PipeVolumeCalculator;