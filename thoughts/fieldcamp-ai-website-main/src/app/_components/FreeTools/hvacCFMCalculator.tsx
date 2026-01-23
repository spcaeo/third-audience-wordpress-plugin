'use client';

import React, { useState, useEffect, useCallback } from 'react';

type RoomType = 'living' | 'bedroom' | 'kitchen' | 'bathroom' | 'office' | 'other' | '';
type CeilingHeight = '8' | '9' | '10' | '11' | '12' | '13' | '14' | '15' | '';
type WindowType = 'single' | 'double' | 'triple' | 'none' | '';
type SunExposure = 'heavy-shade' | 'light-shade' | 'no-shade' | 'good-sun' | '';
type InsulationQuality = 'poor' | 'average' | 'good' | 'excellent' | '';

interface Room {
  id: number;
  type: RoomType;
  length: string;
  width: string;
  ceilingHeight: CeilingHeight;
  windowType: WindowType;
  numWindows: string;
  sunExposure: SunExposure;
  insulation: InsulationQuality;
  cfm: number;
}

// ACH values by room type
const achValues: Record<string, number> = {
  'living': 7,
  'bedroom': 5,
  'kitchen': 8,
  'bathroom': 9,
  'office': 6,
  'other': 6
};

// Adjustment factors
const windowFactors: Record<string, number> = {
  'single': 1.15,
  'double': 1.0,
  'triple': 0.95,
  'none': 0.9
};

const sunFactors: Record<string, number> = {
  'heavy-shade': 0.85,
  'light-shade': 0.95,
  'no-shade': 1.1,
  'good-sun': 1.2
};

const insulationFactors: Record<string, number> = {
  'poor': 1.2,
  'average': 1.0,
  'good': 0.9,
  'excellent': 0.8
};

const roomTypeLabels: Record<string, string> = {
  'living': 'Living Room',
  'bedroom': 'Bedroom',
  'kitchen': 'Kitchen',
  'bathroom': 'Bathroom',
  'office': 'Office',
  'other': 'Other'
};

const HvacCFMCalculator = () => {
  const [rooms, setRooms] = useState<Room[]>([
    {
      id: 1,
      type: '',
      length: '',
      width: '',
      ceilingHeight: '',
      windowType: '',
      numWindows: '',
      sunExposure: '',
      insulation: '',
      cfm: 0
    }
  ]);
  const [occupants, setOccupants] = useState<string>('');
  const [totalCFM, setTotalCFM] = useState(0);
  const [roomCount, setRoomCount] = useState(1);

  // Calculate CFM for a single room
  const calculateRoomCFM = useCallback((room: Room): number => {
    const length = parseFloat(room.length) || 0;
    const width = parseFloat(room.width) || 0;
    const height = parseFloat(room.ceilingHeight) || 0;
    const numWindows = parseInt(room.numWindows) || 0;

    // If essential fields are missing, return 0
    if (!room.type || !length || !width || !height) {
      return 0;
    }

    // Calculate room volume
    const volume = length * width * height;

    // Get base ACH for room type
    const baseACH = achValues[room.type] || 6;

    // Calculate base CFM: (Volume × ACH) / 60
    let cfm = (volume * baseACH) / 60;

    // Apply adjustment factors
    if (room.windowType && numWindows > 0) {
      const windowFactor = windowFactors[room.windowType] || 1.0;
      cfm *= (1 + (windowFactor - 1) * (numWindows * 0.1));
    }

    if (room.sunExposure) {
      cfm *= sunFactors[room.sunExposure] || 1.0;
    }

    if (room.insulation) {
      cfm *= insulationFactors[room.insulation] || 1.0;
    }

    return Math.round(cfm);
  }, []);

  // Update CFM for all rooms when inputs change
  useEffect(() => {
    const updatedRooms = rooms.map(room => ({
      ...room,
      cfm: calculateRoomCFM(room)
    }));

    // Only update if CFM values actually changed
    const cfmChanged = updatedRooms.some((room, index) => room.cfm !== rooms[index].cfm);
    if (cfmChanged) {
      setRooms(updatedRooms);
    }

    // Calculate total CFM including occupants
    const roomsCFM = updatedRooms.reduce((sum, room) => sum + room.cfm, 0);
    const occupantsCFM = (parseInt(occupants) || 0) * 5;
    setTotalCFM(roomsCFM + occupantsCFM);
  }, [rooms, occupants, calculateRoomCFM]);

  // Handle room input changes
  const handleRoomChange = (id: number, field: keyof Room, value: string) => {
    setRooms(prevRooms => prevRooms.map(room =>
      room.id === id ? { ...room, [field]: value } : room
    ));
  };

  // Handle number input - removes leading zeros and non-numeric characters
  const handleNumberInput = (value: string): string => {
    // Remove any non-numeric characters except decimal point
    let cleanValue = value.replace(/[^0-9.]/g, '');

    // Handle multiple decimal points
    const parts = cleanValue.split('.');
    if (parts.length > 2) {
      cleanValue = parts[0] + '.' + parts.slice(1).join('');
    }

    // Remove leading zeros (but keep "0." for decimals)
    if (cleanValue.length > 1 && cleanValue[0] === '0' && cleanValue[1] !== '.') {
      cleanValue = cleanValue.replace(/^0+/, '');
    }

    return cleanValue;
  };

  // Add a new room
  const addRoom = () => {
    const newRoomCount = roomCount + 1;
    setRoomCount(newRoomCount);
    const newRoom: Room = {
      id: newRoomCount,
      type: '',
      length: '',
      width: '',
      ceilingHeight: '',
      windowType: '',
      numWindows: '',
      sunExposure: '',
      insulation: '',
      cfm: 0
    };
    setRooms([...rooms, newRoom]);
  };

  // Remove a room
  const removeRoom = (id: number) => {
    if (rooms.length > 1) {
      setRooms(rooms.filter(room => room.id !== id));
    }
  };

  // Get breakdown items for display
  const getBreakdownItems = () => {
    const items: { name: string; cfm: number }[] = [];

    rooms.forEach((room, index) => {
      if (room.cfm > 0) {
        const roomName = room.type ? roomTypeLabels[room.type] : `Room ${index + 1}`;
        items.push({ name: roomName, cfm: room.cfm });
      }
    });

    const numOccupants = parseInt(occupants) || 0;
    if (numOccupants > 0) {
      items.push({ name: `Occupants (${numOccupants} x 5 CFM)`, cfm: numOccupants * 5 });
    }

    return items;
  };

  const breakdownItems = getBreakdownItems();

  return (
    <div className="max-w-[900px] mx-auto" id="hvac-cfm-calculator">
      {/* Header */}
      <div className="text-center mb-8">
        <h2 className="text-4xl font-bold text-[#1e3a5f] mb-2 tracking-tight">HVAC CFM Calculator</h2>
        <p className="text-gray-500">Calculate the required CFM for your HVAC system</p>
      </div>

      <div className="bg-white rounded-2xl shadow-xl overflow-hidden">
        {/* Calculator Header Bar */}
        <div className="bg-gradient-to-r from-[#1e3a5f] to-[#2d4a6f] px-6 py-4">
          <h3 className="text-white font-semibold text-lg">HVAC CFM Calculator</h3>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-[1fr_320px]">
          {/* Left Panel - Room Inputs */}
          <div className="p-6 border-r border-gray-200 md:border-r md:border-b-0 border-b">
            <div className="flex flex-col gap-5">
              {rooms.map((room, index) => (
                <div key={room.id} className="bg-gray-50 rounded-xl p-5 border border-gray-200">
                  <div className="flex justify-between items-center mb-4">
                    <h4 className="font-semibold text-[#1e3a5f]">Room {index + 1}</h4>
                    {rooms.length > 1 && (
                      <button
                        type="button"
                        onClick={() => removeRoom(room.id)}
                        className="p-1 rounded text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                          <line x1="18" y1="6" x2="6" y2="18"></line>
                          <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                      </button>
                    )}
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {/* Room Type - Full Width */}
                    <div className="sm:col-span-2">
                      <label className="block text-sm font-medium text-gray-700 mb-1.5">Room Type</label>
                      <select
                        className="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none focus:border-[#3b82f6] focus:ring-2 focus:ring-[#3b82f6]/15 bg-white cursor-pointer appearance-none"
                        style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E")`, backgroundRepeat: 'no-repeat', backgroundPosition: 'right 10px center', paddingRight: '36px' }}
                        value={room.type}
                        onChange={(e) => handleRoomChange(room.id, 'type', e.target.value)}
                      >
                        <option value="">Select room type</option>
                        <option value="living">Living Room</option>
                        <option value="bedroom">Bedroom</option>
                        <option value="kitchen">Kitchen</option>
                        <option value="bathroom">Bathroom</option>
                        <option value="office">Office</option>
                        <option value="other">Other</option>
                      </select>
                    </div>

                    {/* Length */}
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1.5">Length</label>
                      <div className="flex border border-gray-200 rounded-lg overflow-hidden bg-white focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                        <input
                          type="text"
                          inputMode="decimal"
                          placeholder="e.g. 15"
                          className="flex-1 px-3 py-2.5 border-none outline-none text-sm bg-transparent min-w-0"
                          value={room.length}
                          onChange={(e) => handleRoomChange(room.id, 'length', handleNumberInput(e.target.value))}
                        />
                        <span className="px-3 py-2.5 bg-gray-50 text-gray-500 text-xs font-medium border-l border-gray-200">ft</span>
                      </div>
                    </div>

                    {/* Width */}
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1.5">Width</label>
                      <div className="flex border border-gray-200 rounded-lg overflow-hidden bg-white focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                        <input
                          type="text"
                          inputMode="decimal"
                          placeholder="e.g. 12"
                          className="flex-1 px-3 py-2.5 border-none outline-none text-sm bg-transparent min-w-0"
                          value={room.width}
                          onChange={(e) => handleRoomChange(room.id, 'width', handleNumberInput(e.target.value))}
                        />
                        <span className="px-3 py-2.5 bg-gray-50 text-gray-500 text-xs font-medium border-l border-gray-200">ft</span>
                      </div>
                    </div>

                    {/* Ceiling Height */}
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1.5">Ceiling Height</label>
                      <select
                        className="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none focus:border-[#3b82f6] focus:ring-2 focus:ring-[#3b82f6]/15 bg-white cursor-pointer appearance-none"
                        style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E")`, backgroundRepeat: 'no-repeat', backgroundPosition: 'right 10px center', paddingRight: '36px' }}
                        value={room.ceilingHeight}
                        onChange={(e) => handleRoomChange(room.id, 'ceilingHeight', e.target.value)}
                      >
                        <option value="">Select height</option>
                        <option value="8">8 ft</option>
                        <option value="9">9 ft</option>
                        <option value="10">10 ft</option>
                        <option value="11">11 ft</option>
                        <option value="12">12 ft</option>
                        <option value="13">13 ft</option>
                        <option value="14">14 ft</option>
                        <option value="15">15 ft</option>
                      </select>
                    </div>

                    {/* Window Type */}
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1.5">Window Type</label>
                      <select
                        className="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none focus:border-[#3b82f6] focus:ring-2 focus:ring-[#3b82f6]/15 bg-white cursor-pointer appearance-none"
                        style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E")`, backgroundRepeat: 'no-repeat', backgroundPosition: 'right 10px center', paddingRight: '36px' }}
                        value={room.windowType}
                        onChange={(e) => handleRoomChange(room.id, 'windowType', e.target.value)}
                      >
                        <option value="">Select type</option>
                        <option value="single">Single Pane</option>
                        <option value="double">Double Pane</option>
                        <option value="triple">Triple Pane</option>
                        <option value="none">None</option>
                      </select>
                    </div>

                    {/* Number of Windows */}
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1.5">Number of Windows</label>
                      <div className="flex border border-gray-200 rounded-lg overflow-hidden bg-white focus-within:border-[#3b82f6] focus-within:ring-2 focus-within:ring-[#3b82f6]/15">
                        <input
                          type="text"
                          inputMode="numeric"
                          placeholder="e.g. 2"
                          className="flex-1 px-3 py-2.5 border-none outline-none text-sm bg-transparent min-w-0"
                          value={room.numWindows}
                          onChange={(e) => handleRoomChange(room.id, 'numWindows', handleNumberInput(e.target.value))}
                        />
                        <span className="px-3 py-2.5 bg-gray-50 text-gray-500 text-xs font-medium border-l border-gray-200">qty</span>
                      </div>
                    </div>

                    {/* Sun Exposure */}
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1.5">Sun Exposure</label>
                      <select
                        className="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none focus:border-[#3b82f6] focus:ring-2 focus:ring-[#3b82f6]/15 bg-white cursor-pointer appearance-none"
                        style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E")`, backgroundRepeat: 'no-repeat', backgroundPosition: 'right 10px center', paddingRight: '36px' }}
                        value={room.sunExposure}
                        onChange={(e) => handleRoomChange(room.id, 'sunExposure', e.target.value)}
                      >
                        <option value="">Select exposure</option>
                        <option value="heavy-shade">Heavy Shade</option>
                        <option value="light-shade">Light Shade</option>
                        <option value="no-shade">No Shade</option>
                        <option value="good-sun">Good Amount of Sun</option>
                      </select>
                    </div>

                    {/* Insulation */}
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1.5">Insulation</label>
                      <select
                        className="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm outline-none focus:border-[#3b82f6] focus:ring-2 focus:ring-[#3b82f6]/15 bg-white cursor-pointer appearance-none"
                        style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E")`, backgroundRepeat: 'no-repeat', backgroundPosition: 'right 10px center', paddingRight: '36px' }}
                        value={room.insulation}
                        onChange={(e) => handleRoomChange(room.id, 'insulation', e.target.value)}
                      >
                        <option value="">Select quality</option>
                        <option value="poor">Poor</option>
                        <option value="average">Average</option>
                        <option value="good">Good</option>
                        <option value="excellent">Excellent</option>
                      </select>
                    </div>
                  </div>

                  {/* Room CFM Result */}
                  <div className="mt-4 p-3 bg-white rounded-lg flex justify-between items-center border border-gray-200">
                    <span className="text-sm text-gray-500">Calculated CFM for this room:</span>
                    <span className="font-bold text-lg text-[#3b82f6]">{room.cfm} CFM</span>
                  </div>
                </div>
              ))}

              {/* Add Room Button */}
              <button
                type="button"
                onClick={addRoom}
                className="w-full py-3.5 px-5 bg-white text-[#1e3a5f] border-2 border-dashed border-gray-200 rounded-xl font-semibold text-sm cursor-pointer transition-all hover:border-[#1e3a5f] hover:bg-gray-50 flex items-center justify-center gap-2"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Another Room
              </button>
            </div>
          </div>

          {/* Right Panel - Results */}
          <div className="p-6 bg-gradient-to-br from-[#f0f7ff] to-[#e8f4fd]">
            <div className="mb-5">
              <h3 className="font-semibold text-[#1e3a5f] mb-1">Total Required CFM</h3>
              <p className="text-sm text-gray-500">Based on your room configurations and occupants</p>
            </div>

            {/* Total CFM Display */}
            <div className="bg-white rounded-xl p-6 text-center border-2 border-[#22c55e] mb-5">
              <div className="text-xs text-gray-500 uppercase tracking-wider mb-2">Total Airflow Required</div>
              <div className="text-4xl font-bold text-[#22c55e]">
                {totalCFM}<span className="text-xl font-semibold text-gray-500 ml-1">CFM</span>
              </div>
            </div>

            {/* Occupants Section */}
            <div className="bg-white rounded-lg p-4 mb-4">
              <label className="block text-sm font-medium text-gray-700 mb-2">Number of Occupants</label>
              <input
                type="text"
                inputMode="numeric"
                placeholder="e.g. 4"
                className="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-base outline-none focus:border-[#3b82f6] focus:ring-2 focus:ring-[#3b82f6]/15"
                value={occupants}
                onChange={(e) => setOccupants(handleNumberInput(e.target.value))}
              />
              <div className="text-xs text-gray-400 mt-1.5">CFM per person: 5</div>
            </div>

            {/* Room Breakdown */}
            <div className="bg-white rounded-lg p-4">
              <h4 className="text-sm font-semibold text-gray-700 mb-3">CFM Breakdown</h4>
              {breakdownItems.length > 0 ? (
                <div>
                  {breakdownItems.map((item, index) => (
                    <div key={index} className="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                      <span className="text-sm text-gray-700">{item.name}</span>
                      <span className="font-semibold text-[#1e3a5f]">{item.cfm} CFM</span>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="text-center py-5 text-gray-400">
                  <svg xmlns="http://www.w3.org/2000/svg" className="w-12 h-12 mx-auto mb-3 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                  </svg>
                  <p className="text-sm">Enter room details to see CFM breakdown</p>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default HvacCFMCalculator;
