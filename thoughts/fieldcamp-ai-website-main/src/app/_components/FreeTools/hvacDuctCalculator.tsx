'use client';

import React, { useState, useCallback } from 'react';

type UnitSystem = 'imperial' | 'metric';
type TabType = 'circular' | 'rectangular';
type DuctMaterial = 'metal' | 'flex' | 'ductboard';
type FixedDimension = 'none' | 'width' | 'height';

interface CircularResults {
  calculatedDiameter: number;
  standardDiameter: number;
  area: number;
  velocity: number;
  equivalentLength?: number;
  pressureDrop?: number;
}

interface RectangularResults {
  width: number;
  height: number;
  area: number;
  equivalentDiameter: number;
  velocity: number;
}

interface ConverterResult {
  value: string;
  show: boolean;
}

const conversions = {
  cfmToLs: 0.471947,
  lsToCfm: 2.11888,
  fpmToMs: 0.00508,
  msToFpm: 196.85,
  inToMm: 25.4,
  mmToIn: 0.0393701,
  ftToM: 0.3048,
  mToFt: 3.28084,
  inSqToMmSq: 645.16,
  mmSqToInSq: 0.00155
};

const standardSizes = [4, 5, 6, 7, 8, 9, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48];

const HvacDuctCalculator = () => {
  const [unitSystem, setUnitSystem] = useState<UnitSystem>('imperial');
  const [activeTab, setActiveTab] = useState<TabType>('circular');
  const [advancedOpen, setAdvancedOpen] = useState(false);

  // Circular duct inputs
  const [flowRateCirc, setFlowRateCirc] = useState<string>('');
  const [maxVelocityCirc, setMaxVelocityCirc] = useState<string>('700');
  const [ductMaterialCirc, setDuctMaterialCirc] = useState<DuctMaterial>('metal');
  const [frictionRateCirc, setFrictionRateCirc] = useState<string>('0.1');
  const [ductLengthCirc, setDuctLengthCirc] = useState<string>('50');
  const [elbows90Circ, setElbows90Circ] = useState<string>('0');
  const [elbows45Circ, setElbows45Circ] = useState<string>('0');
  const [circularResults, setCircularResults] = useState<CircularResults | null>(null);

  // Rectangular duct inputs
  const [flowRateRect, setFlowRateRect] = useState<string>('');
  const [maxVelocityRect, setMaxVelocityRect] = useState<string>('800');
  const [aspectRatio, setAspectRatio] = useState<string>('2');
  const [fixedDimension, setFixedDimension] = useState<FixedDimension>('none');
  const [fixedValue, setFixedValue] = useState<string>('');
  const [rectangularResults, setRectangularResults] = useState<RectangularResults | null>(null);

  // Converter inputs
  const [rectWidth, setRectWidth] = useState<string>('');
  const [rectHeight, setRectHeight] = useState<string>('');
  const [roundDiameter, setRoundDiameter] = useState<string>('');
  const [knownSide, setKnownSide] = useState<string>('');
  const [rectToRoundResult, setRectToRoundResult] = useState<ConverterResult>({ value: '--', show: false });
  const [roundToRectResult, setRoundToRectResult] = useState<ConverterResult>({ value: '--', show: false });

  const isMetric = unitSystem === 'metric';

  const getFlowUnit = () => isMetric ? 'L/s' : 'CFM';
  const getVelocityUnit = () => isMetric ? 'm/s' : 'FPM';
  const getLengthUnit = () => isMetric ? 'mm' : 'in';
  const getAreaUnit = () => isMetric ? 'mm²' : 'in²';

  const calculateCircular = useCallback(() => {
    let flowRate = parseFloat(flowRateCirc);
    let maxVelocity = parseFloat(maxVelocityCirc);
    const frictionRate = parseFloat(frictionRateCirc) || 0.1;
    const ductLength = parseFloat(ductLengthCirc) || 50;
    const elbows90 = parseInt(elbows90Circ) || 0;
    const elbows45 = parseInt(elbows45Circ) || 0;

    if (!flowRate || !maxVelocity) {
      alert('Please enter Airflow and Max Velocity');
      return;
    }

    // Convert to imperial for calculations
    if (isMetric) {
      flowRate = flowRate * conversions.lsToCfm;
      maxVelocity = maxVelocity * conversions.msToFpm;
    }

    // Calculate required area (sq inches)
    const areaRequired = (flowRate / maxVelocity) * 144;

    // Calculate diameter from area
    const calculatedDiameter = 2 * Math.sqrt(areaRequired / Math.PI);

    // Round up to nearest standard size
    let standardDiameter = standardSizes.find(s => s >= calculatedDiameter) || Math.ceil(calculatedDiameter);

    // Calculate actual values with standard diameter
    const actualArea = Math.PI * Math.pow(standardDiameter / 2, 2);
    const actualVelocity = (flowRate * 144) / actualArea;

    // Calculate equivalent length and pressure drop (imperial only)
    const equivalentLength = ductLength + (elbows90 * 5) + (elbows45 * 2.5);
    const pressureDrop = frictionRate * (equivalentLength / 100);

    // Convert for display if metric
    let displayCalcDia = calculatedDiameter;
    let displayStdDia = standardDiameter;
    let displayArea = actualArea;
    let displayVelocity = actualVelocity;

    if (isMetric) {
      displayCalcDia = calculatedDiameter * conversions.inToMm;
      displayStdDia = standardDiameter * conversions.inToMm;
      displayArea = actualArea * conversions.inSqToMmSq;
      displayVelocity = actualVelocity * conversions.fpmToMs;
    }

    setCircularResults({
      calculatedDiameter: displayCalcDia,
      standardDiameter: displayStdDia,
      area: displayArea,
      velocity: displayVelocity,
      equivalentLength: advancedOpen && !isMetric ? equivalentLength : undefined,
      pressureDrop: advancedOpen && !isMetric ? pressureDrop : undefined
    });
  }, [flowRateCirc, maxVelocityCirc, frictionRateCirc, ductLengthCirc, elbows90Circ, elbows45Circ, isMetric, advancedOpen]);

  const calculateRectangular = useCallback(() => {
    let flowRate = parseFloat(flowRateRect);
    let maxVelocity = parseFloat(maxVelocityRect);
    const ratio = parseFloat(aspectRatio);
    let fixedVal = parseFloat(fixedValue);

    if (!flowRate || !maxVelocity) {
      alert('Please enter Airflow and Max Velocity');
      return;
    }

    // Convert to imperial for calculations
    if (isMetric) {
      flowRate = flowRate * conversions.lsToCfm;
      maxVelocity = maxVelocity * conversions.msToFpm;
      if (fixedVal) fixedVal = fixedVal * conversions.mmToIn;
    }

    // Calculate required area (sq inches)
    const areaRequired = (flowRate / maxVelocity) * 144;

    let width: number, height: number;

    if (fixedDimension === 'none') {
      height = Math.sqrt(areaRequired / ratio);
      width = ratio * height;
    } else if (fixedDimension === 'width') {
      width = fixedVal;
      height = areaRequired / width;
    } else {
      height = fixedVal;
      width = areaRequired / height;
    }

    // Round to nearest even number (standard sizes)
    width = Math.ceil(width / 2) * 2;
    height = Math.ceil(height / 2) * 2;

    // Calculate actual values
    const actualArea = width * height;
    const actualVelocity = (flowRate * 144) / actualArea;
    const equivDiameter = 1.3 * Math.pow(width * height, 0.625) / Math.pow(width + height, 0.25);

    // Convert for display if metric
    let displayWidth = width;
    let displayHeight = height;
    let displayArea = actualArea;
    let displayEquivDia = equivDiameter;
    let displayVelocity = actualVelocity;

    if (isMetric) {
      displayWidth = width * conversions.inToMm;
      displayHeight = height * conversions.inToMm;
      displayArea = actualArea * conversions.inSqToMmSq;
      displayEquivDia = equivDiameter * conversions.inToMm;
      displayVelocity = actualVelocity * conversions.fpmToMs;
    }

    setRectangularResults({
      width: displayWidth,
      height: displayHeight,
      area: displayArea,
      equivalentDiameter: displayEquivDia,
      velocity: displayVelocity
    });
  }, [flowRateRect, maxVelocityRect, aspectRatio, fixedDimension, fixedValue, isMetric]);

  const convertRectToRound = () => {
    let width = parseFloat(rectWidth);
    let height = parseFloat(rectHeight);

    if (!width || !height) {
      alert('Please enter both Width and Height');
      return;
    }

    if (isMetric) {
      width = width * conversions.mmToIn;
      height = height * conversions.mmToIn;
    }

    let equivDiameter = 1.3 * Math.pow(width * height, 0.625) / Math.pow(width + height, 0.25);

    if (isMetric) {
      equivDiameter = equivDiameter * conversions.inToMm;
    }

    setRectToRoundResult({ value: equivDiameter.toFixed(1), show: true });
  };

  const convertRoundToRect = () => {
    let diameter = parseFloat(roundDiameter);
    let knownSideVal = parseFloat(knownSide);

    if (!diameter) {
      alert('Please enter the Diameter');
      return;
    }

    if (isMetric) {
      diameter = diameter * conversions.mmToIn;
      if (knownSideVal) knownSideVal = knownSideVal * conversions.mmToIn;
    }

    const circleArea = Math.PI * Math.pow(diameter / 2, 2);

    let width: number, height: number;

    if (knownSideVal) {
      width = knownSideVal;
      height = circleArea / knownSideVal;
    } else {
      height = Math.sqrt(circleArea / 2);
      width = 2 * height;
    }

    width = Math.ceil(width / 2) * 2;
    height = Math.ceil(height / 2) * 2;

    if (isMetric) {
      width = width * conversions.inToMm;
      height = height * conversions.inToMm;
    }

    const dimUnit = isMetric ? 'mm' : '"';
    setRoundToRectResult({ value: `${Math.round(width)}${dimUnit} x ${Math.round(height)}${dimUnit}`, show: true });
  };

  const handleUnitChange = (unit: UnitSystem) => {
    setUnitSystem(unit);
    if (unit === 'metric' && advancedOpen) {
      setAdvancedOpen(false);
    }
  };

  return (
    <div className="mx-auto bg-white rounded-2xl shadow-xl overflow-hidden" id="hvac-duct-calculator">
      {/* Header */}
      <div className="text-center py-8 px-6">
        <h2 className="text-3xl font-bold text-[#1e3a5f] mb-2">HVAC Duct Calculator</h2>
        <p className="text-gray-500">Calculate the required duct size for your HVAC system</p>
      </div>

      {/* Unit System Toggle */}
      <div className="bg-gradient-to-r from-[#1e3a5f] to-[#2d4a6f] px-6 py-4 flex items-center justify-between flex-wrap gap-3">
        <label className="text-white/90 font-medium text-sm">Unit System:</label>
        <div className="flex bg-white/15 rounded-lg p-1">
          <button
            type="button"
            className={`px-4 py-1.5 rounded-md text-sm font-medium transition-all ${
              unitSystem === 'imperial'
                ? 'bg-white text-[#1e3a5f] shadow-sm'
                : 'text-white/70 hover:text-white'
            }`}
            onClick={() => handleUnitChange('imperial')}
          >
            Imperial
          </button>
          <button
            type="button"
            className={`px-4 py-1.5 rounded-md text-sm font-medium transition-all ${
              unitSystem === 'metric'
                ? 'bg-white text-[#1e3a5f] shadow-sm'
                : 'text-white/70 hover:text-white'
            }`}
            onClick={() => handleUnitChange('metric')}
          >
            Metric
          </button>
        </div>
      </div>

      <div className="p-7">
        {/* Tabs */}
        <div className="flex gap-1 mb-6 border-b-2 border-gray-200">
          <button
            type="button"
            className={`px-5 py-3 font-medium text-sm border-b-2 -mb-0.5 transition-all ${
              activeTab === 'circular'
                ? 'text-[#1e3a5f] border-[#1e3a5f]'
                : 'text-gray-500 border-transparent hover:text-[#1e3a5f]'
            }`}
            onClick={() => setActiveTab('circular')}
          >
            Round Duct
          </button>
          <button
            type="button"
            className={`px-5 py-3 font-medium text-sm border-b-2 -mb-0.5 transition-all ${
              activeTab === 'rectangular'
                ? 'text-[#1e3a5f] border-[#1e3a5f]'
                : 'text-gray-500 border-transparent hover:text-[#1e3a5f]'
            }`}
            onClick={() => setActiveTab('rectangular')}
          >
            Rectangular Duct
          </button>
        </div>

        {/* Round Duct Tab */}
        {activeTab === 'circular' && (
          <div className="animate-fadeIn">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
              <div className="flex flex-col">
                <label className="text-sm font-medium text-gray-700 mb-1.5">Airflow ({getFlowUnit()})</label>
                <div className="flex border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                  <input
                    type="number"
                    placeholder="e.g. 400"
                    className="flex-1 px-3.5 py-3 border-none outline-none text-base"
                    value={flowRateCirc}
                    onChange={(e) => setFlowRateCirc(e.target.value)}
                  />
                  <span className="px-3.5 py-3 bg-gray-50 text-gray-500 text-sm font-medium border-l border-gray-200">
                    {getFlowUnit()}
                  </span>
                </div>
              </div>
              <div className="flex flex-col">
                <label className="text-sm font-medium text-gray-700 mb-1.5">Max Velocity</label>
                <div className="flex border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                  <input
                    type="number"
                    placeholder="e.g. 700"
                    className="flex-1 px-3.5 py-3 border-none outline-none text-base"
                    value={maxVelocityCirc}
                    onChange={(e) => setMaxVelocityCirc(e.target.value)}
                  />
                  <span className="px-3.5 py-3 bg-gray-50 text-gray-500 text-sm font-medium border-l border-gray-200">
                    {getVelocityUnit()}
                  </span>
                </div>
                <span className="text-xs text-gray-400 mt-1">Typical residential: 600-900 FPM</span>
              </div>
              <div className="flex flex-col">
                <label className="text-sm font-medium text-gray-700 mb-1.5">Duct Material</label>
                <select
                  className="w-full px-3.5 py-3 border border-gray-200 rounded-lg text-base outline-none focus:border-[#3b82f6] focus:ring-2 focus:ring-[#3b82f6]/15 appearance-none bg-white cursor-pointer"
                  style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E")`, backgroundRepeat: 'no-repeat', backgroundPosition: 'right 12px center', paddingRight: '40px' }}
                  value={ductMaterialCirc}
                  onChange={(e) => setDuctMaterialCirc(e.target.value as DuctMaterial)}
                >
                  <option value="metal">Sheet Metal</option>
                  <option value="flex">Flexible Duct</option>
                  <option value="ductboard">Duct Board</option>
                </select>
              </div>
            </div>

            {/* Advanced Section */}
            <div className={`mt-5 border border-gray-200 rounded-lg overflow-hidden ${isMetric ? 'opacity-60 pointer-events-none' : ''}`}>
              <div
                className={`flex items-center justify-between px-4 py-3.5 bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors ${advancedOpen ? 'border-b border-gray-200' : ''}`}
                onClick={() => !isMetric && setAdvancedOpen(!advancedOpen)}
              >
                <div className="flex items-center gap-2.5">
                  <span className="font-medium text-gray-700 text-sm">Advanced Options</span>
                  <span className="bg-[#1e3a5f] text-white text-[10px] font-semibold px-2 py-0.5 rounded uppercase tracking-wide">Pro</span>
                  <span className="bg-gray-500 text-white text-[10px] font-semibold px-2 py-0.5 rounded uppercase tracking-wide">Imperial Only</span>
                </div>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  className={`text-gray-500 transition-transform ${advancedOpen ? 'rotate-180' : ''}`}
                >
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </div>
              {advancedOpen && (
                <div className="p-5 animate-fadeIn">
                  <div className="text-sm text-gray-500 mb-4 p-2.5 bg-amber-50 rounded-md border-l-3 border-amber-400">
                    For HVAC professionals: Pressure calculations use imperial HVAC standards (in.wg, feet).
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div className="flex flex-col">
                      <label className="text-sm font-medium text-gray-700 mb-1.5">Friction Rate</label>
                      <div className="flex border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                        <input
                          type="number"
                          step="0.01"
                          className="flex-1 px-3.5 py-3 border-none outline-none text-base"
                          value={frictionRateCirc}
                          onChange={(e) => setFrictionRateCirc(e.target.value)}
                        />
                        <span className="px-3.5 py-3 bg-gray-50 text-gray-500 text-sm font-medium border-l border-gray-200 whitespace-nowrap">
                          in.wg/100ft
                        </span>
                      </div>
                      <span className="text-xs text-gray-400 mt-1">Metal: 0.08-0.1 | Flex: 0.15-0.2</span>
                    </div>
                    <div className="flex flex-col">
                      <label className="text-sm font-medium text-gray-700 mb-1.5">Duct Length</label>
                      <div className="flex border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                        <input
                          type="number"
                          className="flex-1 px-3.5 py-3 border-none outline-none text-base"
                          value={ductLengthCirc}
                          onChange={(e) => setDuctLengthCirc(e.target.value)}
                        />
                        <span className="px-3.5 py-3 bg-gray-50 text-gray-500 text-sm font-medium border-l border-gray-200">ft</span>
                      </div>
                    </div>
                    <div className="flex flex-col">
                      <label className="text-sm font-medium text-gray-700 mb-1.5">90 Elbows</label>
                      <div className="flex border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                        <input
                          type="number"
                          min="0"
                          className="flex-1 px-3.5 py-3 border-none outline-none text-base"
                          value={elbows90Circ}
                          onChange={(e) => setElbows90Circ(e.target.value)}
                        />
                        <span className="px-3.5 py-3 bg-gray-50 text-gray-500 text-sm font-medium border-l border-gray-200">qty</span>
                      </div>
                    </div>
                    <div className="flex flex-col">
                      <label className="text-sm font-medium text-gray-700 mb-1.5">45 Elbows</label>
                      <div className="flex border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                        <input
                          type="number"
                          min="0"
                          className="flex-1 px-3.5 py-3 border-none outline-none text-base"
                          value={elbows45Circ}
                          onChange={(e) => setElbows45Circ(e.target.value)}
                        />
                        <span className="px-3.5 py-3 bg-gray-50 text-gray-500 text-sm font-medium border-l border-gray-200">qty</span>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>

            {isMetric && (
              <div className="mt-4 p-3 bg-gray-100 rounded-md border-l-3 border-gray-400 text-sm text-gray-500">
                Advanced pressure calculations are available in Imperial mode only (industry standard).
              </div>
            )}

            <button
              type="button"
              className="w-full mt-6 py-3.5 px-7 bg-gradient-to-r from-[#1e3a5f] to-[#2d4a6f] text-white rounded-lg font-semibold text-base cursor-pointer transition-all shadow-md hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0"
              onClick={calculateCircular}
            >
              Calculate Duct Size
            </button>

            {/* Circular Results */}
            {circularResults && (
              <div className="mt-6 animate-fadeIn">
                <div className="bg-gradient-to-br from-[#f0f7ff] to-[#e8f4fd] rounded-xl overflow-hidden border border-[#3b82f6]/20">
                  <div className="bg-gradient-to-r from-[#3b82f6] to-[#60a5fa] px-5 py-3.5">
                    <h3 className="text-white font-semibold">Recommended Duct Size</h3>
                  </div>
                  <div className="p-5">
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                      <div className="bg-white p-4 rounded-lg shadow-sm border border-[#22c55e] bg-gradient-to-br from-[#ecfdf5] to-[#d1fae5]">
                        <div className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Standard Size</div>
                        <div className="text-xl font-bold text-[#22c55e]">
                          {Math.round(circularResults.standardDiameter)}<span className="text-sm font-medium text-gray-500 ml-0.5">{getLengthUnit()}</span>
                        </div>
                        <div className="text-xs text-gray-400 mt-1">Nearest standard size</div>
                      </div>
                      <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Calculated</div>
                        <div className="text-xl font-bold text-[#1e3a5f]">
                          {circularResults.calculatedDiameter.toFixed(1)}<span className="text-sm font-medium text-gray-500 ml-0.5">{getLengthUnit()}</span>
                        </div>
                        <div className="text-xs text-gray-400 mt-1">Exact calculation</div>
                      </div>
                      <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Cross-Section Area</div>
                        <div className="text-xl font-bold text-[#1e3a5f]">
                          {circularResults.area.toFixed(1)}<span className="text-sm font-medium text-gray-500 ml-0.5">{getAreaUnit()}</span>
                        </div>
                      </div>
                      <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Actual Velocity</div>
                        <div className="text-xl font-bold text-[#1e3a5f]">
                          {isMetric ? circularResults.velocity.toFixed(2) : Math.round(circularResults.velocity)}<span className="text-sm font-medium text-gray-500 ml-0.5">{getVelocityUnit()}</span>
                        </div>
                      </div>
                    </div>

                    {isMetric && (
                      <div className="mt-3 p-2.5 bg-gray-50 rounded-md text-xs text-gray-500 border border-gray-200">
                        Metric sizes converted from standard North American duct dimensions.
                      </div>
                    )}

                    {circularResults.equivalentLength !== undefined && circularResults.pressureDrop !== undefined && (
                      <div className="mt-4 pt-4 border-t border-dashed border-gray-200">
                        <div className="text-xs text-gray-500 font-medium mb-3 flex items-center gap-1.5">
                          <span className="bg-[#1e3a5f] text-white text-[10px] font-semibold px-2 py-0.5 rounded uppercase">Pro</span>
                          Advanced Results (Imperial)
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                          <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Equivalent Length</div>
                            <div className="text-xl font-bold text-[#1e3a5f]">
                              {circularResults.equivalentLength.toFixed(1)}<span className="text-sm font-medium text-gray-500 ml-0.5">ft</span>
                            </div>
                          </div>
                          <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Pressure Drop</div>
                            <div className="text-xl font-bold text-[#1e3a5f]">
                              {circularResults.pressureDrop.toFixed(3)}<span className="text-sm font-medium text-gray-500 ml-0.5">in.wg</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    )}

                    <div className="mt-4 p-3.5 bg-white/70 rounded-lg border-l-4 border-[#3b82f6]">
                      <div className="font-semibold text-[#1e3a5f] text-sm mb-1">Note:</div>
                      <p className="text-gray-500 text-sm leading-relaxed">
                        Duct size rounded up to nearest standard manufactured size. For flex duct, consider going one size larger due to higher friction.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            )}
          </div>
        )}

        {/* Rectangular Duct Tab */}
        {activeTab === 'rectangular' && (
          <div className="animate-fadeIn">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
              <div className="flex flex-col">
                <label className="text-sm font-medium text-gray-700 mb-1.5">Airflow ({getFlowUnit()})</label>
                <div className="flex border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                  <input
                    type="number"
                    placeholder="e.g. 800"
                    className="flex-1 px-3.5 py-3 border-none outline-none text-base"
                    value={flowRateRect}
                    onChange={(e) => setFlowRateRect(e.target.value)}
                  />
                  <span className="px-3.5 py-3 bg-gray-50 text-gray-500 text-sm font-medium border-l border-gray-200">
                    {getFlowUnit()}
                  </span>
                </div>
              </div>
              <div className="flex flex-col">
                <label className="text-sm font-medium text-gray-700 mb-1.5">Max Velocity</label>
                <div className="flex border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                  <input
                    type="number"
                    placeholder="e.g. 800"
                    className="flex-1 px-3.5 py-3 border-none outline-none text-base"
                    value={maxVelocityRect}
                    onChange={(e) => setMaxVelocityRect(e.target.value)}
                  />
                  <span className="px-3.5 py-3 bg-gray-50 text-gray-500 text-sm font-medium border-l border-gray-200">
                    {getVelocityUnit()}
                  </span>
                </div>
                <span className="text-xs text-gray-400 mt-1">Trunk lines: 700-900 FPM</span>
              </div>
              <div className="flex flex-col">
                <label className="text-sm font-medium text-gray-700 mb-1.5">Aspect Ratio (W:H)</label>
                <select
                  className="w-full px-3.5 py-3 border border-gray-200 rounded-lg text-base outline-none focus:border-[#3b82f6] focus:ring-2 focus:ring-[#3b82f6]/15 appearance-none bg-white cursor-pointer"
                  style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E")`, backgroundRepeat: 'no-repeat', backgroundPosition: 'right 12px center', paddingRight: '40px' }}
                  value={aspectRatio}
                  onChange={(e) => setAspectRatio(e.target.value)}
                >
                  <option value="1">1:1 (Square)</option>
                  <option value="1.5">1.5:1</option>
                  <option value="2">2:1 (Recommended)</option>
                  <option value="2.5">2.5:1</option>
                  <option value="3">3:1</option>
                  <option value="4">4:1 (Max)</option>
                </select>
                <span className="text-xs text-gray-400 mt-1">Keep below 4:1 for efficiency</span>
              </div>
            </div>

            {/* Fixed Dimension Option */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
              <div className="flex flex-col">
                <label className="text-sm font-medium text-gray-700 mb-1.5">Fixed Dimension (Optional)</label>
                <select
                  className="w-full px-3.5 py-3 border border-gray-200 rounded-lg text-base outline-none focus:border-[#3b82f6] focus:ring-2 focus:ring-[#3b82f6]/15 appearance-none bg-white cursor-pointer"
                  style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E")`, backgroundRepeat: 'no-repeat', backgroundPosition: 'right 12px center', paddingRight: '40px' }}
                  value={fixedDimension}
                  onChange={(e) => setFixedDimension(e.target.value as FixedDimension)}
                >
                  <option value="none">Auto Calculate Both</option>
                  <option value="width">I Know the Width</option>
                  <option value="height">I Know the Height</option>
                </select>
              </div>
              {fixedDimension !== 'none' && (
                <div className="flex flex-col">
                  <label className="text-sm font-medium text-gray-700 mb-1.5">Fixed Value</label>
                  <div className="flex border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                    <input
                      type="number"
                      placeholder="e.g. 12"
                      className="flex-1 px-3.5 py-3 border-none outline-none text-base"
                      value={fixedValue}
                      onChange={(e) => setFixedValue(e.target.value)}
                    />
                    <span className="px-3.5 py-3 bg-gray-50 text-gray-500 text-sm font-medium border-l border-gray-200">
                      {getLengthUnit()}
                    </span>
                  </div>
                </div>
              )}
            </div>

            <button
              type="button"
              className="w-full mt-6 py-3.5 px-7 bg-gradient-to-r from-[#1e3a5f] to-[#2d4a6f] text-white rounded-lg font-semibold text-base cursor-pointer transition-all shadow-md hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0"
              onClick={calculateRectangular}
            >
              Calculate Duct Size
            </button>

            {/* Rectangular Results */}
            {rectangularResults && (
              <div className="mt-6 animate-fadeIn">
                <div className="bg-gradient-to-br from-[#f0f7ff] to-[#e8f4fd] rounded-xl overflow-hidden border border-[#3b82f6]/20">
                  <div className="bg-gradient-to-r from-[#3b82f6] to-[#60a5fa] px-5 py-3.5">
                    <h3 className="text-white font-semibold">Recommended Duct Size</h3>
                  </div>
                  <div className="p-5">
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                      <div className="bg-white p-4 rounded-lg shadow-sm border border-[#22c55e] bg-gradient-to-br from-[#ecfdf5] to-[#d1fae5]">
                        <div className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Duct Size</div>
                        <div className="text-xl font-bold text-[#22c55e]">
                          {Math.round(rectangularResults.width)}{isMetric ? 'mm' : '"'} x {Math.round(rectangularResults.height)}{isMetric ? 'mm' : '"'}
                        </div>
                        <div className="text-xs text-gray-400 mt-1">Width x Height</div>
                      </div>
                      <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Cross-Section Area</div>
                        <div className="text-xl font-bold text-[#1e3a5f]">
                          {rectangularResults.area.toFixed(1)}<span className="text-sm font-medium text-gray-500 ml-0.5">{getAreaUnit()}</span>
                        </div>
                      </div>
                      <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Equivalent Round</div>
                        <div className="text-xl font-bold text-[#1e3a5f]">
                          {rectangularResults.equivalentDiameter.toFixed(1)}<span className="text-sm font-medium text-gray-500 ml-0.5">{getLengthUnit()}</span>
                        </div>
                        <div className="text-xs text-gray-400 mt-1">Same capacity</div>
                      </div>
                      <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Actual Velocity</div>
                        <div className="text-xl font-bold text-[#1e3a5f]">
                          {isMetric ? rectangularResults.velocity.toFixed(2) : Math.round(rectangularResults.velocity)}<span className="text-sm font-medium text-gray-500 ml-0.5">{getVelocityUnit()}</span>
                        </div>
                      </div>
                    </div>

                    {isMetric && (
                      <div className="mt-3 p-2.5 bg-gray-50 rounded-md text-xs text-gray-500 border border-gray-200">
                        Metric sizes converted from standard North American duct dimensions.
                      </div>
                    )}

                    <div className="mt-4 p-3.5 bg-white/70 rounded-lg border-l-4 border-[#3b82f6]">
                      <div className="font-semibold text-[#1e3a5f] text-sm mb-1">Note:</div>
                      <p className="text-gray-500 text-sm leading-relaxed">
                        Dimensions rounded to nearest standard size. Equivalent round diameter shown for comparison.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            )}
          </div>
        )}

        {/* Converters Section */}
        <div className="mt-7 pt-7 border-t-2 border-gray-200">
          <h3 className="text-lg font-semibold text-[#1e3a5f] mb-5 flex items-center gap-2.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M17 1l4 4-4 4"/>
              <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
              <path d="M7 23l-4-4 4-4"/>
              <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
            </svg>
            Duct Converters
          </h3>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            {/* Rectangular to Round */}
            <div className="bg-gray-50 p-5 rounded-xl border border-gray-200">
              <h4 className="font-semibold text-gray-700 mb-3.5">Rectangular to Round Equivalent</h4>
              <div className="grid grid-cols-2 gap-4 mb-3.5">
                <div className="flex flex-col">
                  <label className="text-sm font-medium text-gray-700 mb-1.5">Width</label>
                  <div className="flex border border-gray-200 rounded-lg overflow-hidden bg-white focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                    <input
                      type="number"
                      placeholder="e.g. 12"
                      className="flex-1 px-3 py-2.5 border-none outline-none text-sm"
                      value={rectWidth}
                      onChange={(e) => setRectWidth(e.target.value)}
                    />
                    <span className="px-3 py-2.5 bg-gray-50 text-gray-500 text-xs font-medium border-l border-gray-200">
                      {getLengthUnit()}
                    </span>
                  </div>
                </div>
                <div className="flex flex-col">
                  <label className="text-sm font-medium text-gray-700 mb-1.5">Height</label>
                  <div className="flex border border-gray-200 rounded-lg overflow-hidden bg-white focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                    <input
                      type="number"
                      placeholder="e.g. 8"
                      className="flex-1 px-3 py-2.5 border-none outline-none text-sm"
                      value={rectHeight}
                      onChange={(e) => setRectHeight(e.target.value)}
                    />
                    <span className="px-3 py-2.5 bg-gray-50 text-gray-500 text-xs font-medium border-l border-gray-200">
                      {getLengthUnit()}
                    </span>
                  </div>
                </div>
              </div>
              <button
                type="button"
                className="w-full py-2.5 px-4 bg-white text-[#1e3a5f] border-2 border-[#1e3a5f] rounded-lg font-semibold text-sm cursor-pointer transition-all hover:bg-[#1e3a5f] hover:text-white"
                onClick={convertRectToRound}
              >
                Convert
              </button>
              {rectToRoundResult.show && (
                <div className="mt-3.5 p-3.5 bg-white rounded-lg text-center border border-gray-200 animate-fadeIn">
                  <div className="text-xs text-gray-500 mb-1">Equivalent Round Diameter</div>
                  <div className="text-xl font-bold text-[#22c55e]">{rectToRoundResult.value} {getLengthUnit()}</div>
                </div>
              )}
            </div>

            {/* Round to Rectangular */}
            <div className="bg-gray-50 p-5 rounded-xl border border-gray-200">
              <h4 className="font-semibold text-gray-700 mb-3.5">Round to Rectangular Equivalent</h4>
              <div className="grid grid-cols-2 gap-4 mb-3.5">
                <div className="flex flex-col">
                  <label className="text-sm font-medium text-gray-700 mb-1.5">Diameter</label>
                  <div className="flex border border-gray-200 rounded-lg overflow-hidden bg-white focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                    <input
                      type="number"
                      placeholder="e.g. 10"
                      className="flex-1 px-3 py-2.5 border-none outline-none text-sm"
                      value={roundDiameter}
                      onChange={(e) => setRoundDiameter(e.target.value)}
                    />
                    <span className="px-3 py-2.5 bg-gray-50 text-gray-500 text-xs font-medium border-l border-gray-200">
                      {getLengthUnit()}
                    </span>
                  </div>
                </div>
                <div className="flex flex-col">
                  <label className="text-sm font-medium text-gray-700 mb-1.5">One Side (Optional)</label>
                  <div className="flex border border-gray-200 rounded-lg overflow-hidden bg-white focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                    <input
                      type="number"
                      placeholder="Fixed side"
                      className="flex-1 px-3 py-2.5 border-none outline-none text-sm"
                      value={knownSide}
                      onChange={(e) => setKnownSide(e.target.value)}
                    />
                    <span className="px-3 py-2.5 bg-gray-50 text-gray-500 text-xs font-medium border-l border-gray-200">
                      {getLengthUnit()}
                    </span>
                  </div>
                </div>
              </div>
              <button
                type="button"
                className="w-full py-2.5 px-4 bg-white text-[#1e3a5f] border-2 border-[#1e3a5f] rounded-lg font-semibold text-sm cursor-pointer transition-all hover:bg-[#1e3a5f] hover:text-white"
                onClick={convertRoundToRect}
              >
                Convert
              </button>
              {roundToRectResult.show && (
                <div className="mt-3.5 p-3.5 bg-white rounded-lg text-center border border-gray-200 animate-fadeIn">
                  <div className="text-xs text-gray-500 mb-1">Equivalent Rectangle</div>
                  <div className="text-xl font-bold text-[#22c55e]">{roundToRectResult.value}</div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      <style jsx>{`
        @keyframes fadeIn {
          from { opacity: 0; transform: translateY(8px); }
          to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
          animation: fadeIn 0.3s ease;
        }
      `}</style>
    </div>
  );
};

export default HvacDuctCalculator;
