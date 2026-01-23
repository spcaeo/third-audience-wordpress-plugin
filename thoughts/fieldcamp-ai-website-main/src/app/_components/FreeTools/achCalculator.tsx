'use client';

import React, { useState } from 'react';

const AchCalculator = () => {
  const [airflow, setAirflow] = useState<string>('');
  const [roomLength, setRoomLength] = useState<string>('');
  const [roomWidth, setRoomWidth] = useState<string>('');
  const [ceilingHeight, setCeilingHeight] = useState<string>('');
  const [roomVolume, setRoomVolume] = useState<string>('-- ft³');
  const [achResult, setAchResult] = useState<string>('--');

  const handleCalculate = () => {
    const L = parseFloat(roomLength) || 0;
    const W = parseFloat(roomWidth) || 0;
    const H = parseFloat(ceilingHeight) || 0;
    const CFM = parseFloat(airflow) || 0;

    const volume = L * W * H;
    setRoomVolume(volume > 0 ? volume.toFixed(2) + ' ft³' : '-- ft³');

    if (volume > 0) {
      const ach = (CFM * 60) / volume;
      setAchResult(ach.toFixed(6));
    } else {
      setAchResult('--');
    }
  };

  const handleReset = () => {
    setAirflow('');
    setRoomLength('');
    setRoomWidth('');
    setCeilingHeight('');
    setRoomVolume('-- ft³');
    setAchResult('--');
  };

  return (
    <>
      <style jsx>{`
        .ach-wrapper {
          max-width: 860px;
          margin: auto;
          animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
          0% { opacity: 0; transform: translateY(20px); }
          100% { opacity: 1; transform: translateY(0); }
        }

        .ach-title {
          font-size: 32px;
          font-weight: 800;
          color: #0f172a;
          text-align: center;
          margin-bottom: 8px;
        }

        .ach-subtitle {
          font-size: 15px;
          text-align: center;
          color: #475569;
          margin-bottom: 32px;
        }

        .ach-card {
          background: #ffffffee;
          backdrop-filter: blur(10px);
          border-radius: 18px;
          padding: 32px;
          box-shadow: 0 10px 40px rgba(0,0,0,0.08);
          transition: transform 0.2s ease;
        }

        .ach-card:hover {
          transform: scale(1.01);
        }

        .ach-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 28px;
        }

        @media (max-width: 768px) {
          .ach-grid {
            grid-template-columns: 1fr;
          }
        }

        .ach-label {
          font-size: 14px;
          font-weight: 600;
          margin-bottom: 6px;
          color: #334155;
          display: block;
        }

        .ach-input-group {
          margin-bottom: 22px;
        }

        .ach-input-box {
          background: #f8fafc;
          border: 1px solid #cbd5e1;
          border-radius: 12px;
          padding: 14px 16px;
          display: flex;
          align-items: center;
          gap: 10px;
          transition: all 0.2s ease;
        }

        .ach-input-box:focus-within {
          border-color: #3b82f6;
          box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
        }

        .ach-input-box input {
          width: 100%;
          font-size: 15px;
          background: transparent;
          border: none;
          outline: none;
          color: #0f172a;
        }

        .ach-icon {
          font-size: 18px;
          color: #3b82f6;
        }

        .ach-button-row {
          display: flex;
          gap: 14px;
          margin-top: 8px;
        }

        .ach-btn {
          flex: 1;
          border: none;
          cursor: pointer;
          padding: 14px 18px;
          font-size: 15px;
          border-radius: 12px;
          font-weight: 600;
          transition: all 0.2s ease;
        }

        .ach-btn-primary {
          background: linear-gradient(135deg, #2563eb, #3b82f6);
          color: white;
        }

        .ach-btn-primary:hover {
          transform: translateY(-2px);
          box-shadow: 0 6px 18px rgba(37,99,235,0.4);
        }

        .ach-btn-light {
          background: #e2e8f0;
          color: #334155;
        }

        .ach-btn-light:hover {
          background: #cbd5e1;
        }

        .ach-volume-box {
          margin-top: 12px;
          padding: 14px 16px;
          background: #f1f5f9;
          border-radius: 12px;
          border: 1px dashed #cbd5e1;
          font-size: 15px;
          display: flex;
          justify-content: space-between;
          margin-bottom: 22px;
        }

        .ach-result-panel {
          background: linear-gradient(135deg, #dbeafe, #eff6ff);
          padding: 26px;
          border-radius: 16px;
          border: 1px solid #bfdbfe;
        }

        .ach-result-box {
          background: white;
          padding: 34px 20px;
          text-align: center;
          border-radius: 14px;
          margin-bottom: 20px;
          box-shadow: 0 4px 14px rgba(96,165,250,0.25);
          animation: popIn 0.4s ease-out;
        }

        @keyframes popIn {
          0% { transform: scale(0.85); opacity: 0; }
          100% { transform: scale(1); opacity: 1; }
        }

        .ach-result-value {
          font-size: 54px;
          font-weight: 800;
          color: #1d4ed8;
        }

        .ach-result-unit {
          font-size: 22px;
          color: #2563eb;
          margin-left: 6px;
        }

        .ach-result-subtext {
          color: #64748b;
          margin-top: 6px;
        }

        .ach-result-info {
          font-size: 14px;
          color: #475569;
          line-height: 1.5;
          background: #ffffff;
          padding: 14px;
          border-radius: 12px;
        }
      `}</style>

      <div className="ach-wrapper" id="calc-wrapper">
        <h1 className="ach-title">ACH Calculator</h1>
        <p className="ach-subtitle">A beautifully simple & responsive HVAC-grade ventilation calculator</p>

        <div className="ach-card">
          <div className="ach-grid">
            {/* LEFT SIDE */}
            <div>
              <div className="ach-input-group">
                <label className="ach-label">Airflow (CFM)</label>
                <div className="ach-input-box">
                  <span className="ach-icon">💨</span>
                  <input
                    type="number"
                    placeholder="e.g., 500"
                    value={airflow}
                    onChange={(e) => setAirflow(e.target.value)}
                  />
                </div>
              </div>

              <div className="ach-input-group">
                <label className="ach-label">Room Length (ft)</label>
                <div className="ach-input-box">
                  <span className="ach-icon">📏</span>
                  <input
                    type="number"
                    placeholder="e.g., 20"
                    value={roomLength}
                    onChange={(e) => setRoomLength(e.target.value)}
                  />
                </div>
              </div>

              <div className="ach-input-group">
                <label className="ach-label">Room Width (ft)</label>
                <div className="ach-input-box">
                  <span className="ach-icon">📐</span>
                  <input
                    type="number"
                    placeholder="e.g., 15"
                    value={roomWidth}
                    onChange={(e) => setRoomWidth(e.target.value)}
                  />
                </div>
              </div>

              <div className="ach-input-group">
                <label className="ach-label">Ceiling Height (ft)</label>
                <div className="ach-input-box">
                  <span className="ach-icon">🏢</span>
                  <input
                    type="number"
                    placeholder="e.g., 10"
                    value={ceilingHeight}
                    onChange={(e) => setCeilingHeight(e.target.value)}
                  />
                </div>
              </div>

              <div className="ach-volume-box">
                <span>Room Volume</span>
                <span>{roomVolume}</span>
              </div>

              <div className="ach-button-row">
                <button className="ach-btn ach-btn-primary" onClick={handleCalculate}>
                  Calculate
                </button>
                <button className="ach-btn ach-btn-light" onClick={handleReset}>
                  Reset
                </button>
              </div>
            </div>

            {/* RIGHT SIDE */}
            <div>
              <div className="ach-result-panel">
                <div className="ach-result-box">
                  <span className="ach-result-value">{achResult}</span>
                  <span className="ach-result-unit">ACH</span>
                  <p className="ach-result-subtext">Air changes per hour</p>
                </div>

                <div className="ach-result-info">
                  <strong>What is ACH?</strong><br />
                  ACH measures how many times air is replaced in a room each hour. Higher ACH = better ventilation.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default AchCalculator;
