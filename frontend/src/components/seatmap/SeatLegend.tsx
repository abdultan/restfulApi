import React from 'react';

export function SeatLegend() {
  const legendItems = [
    { icon: '🟢', label: 'Available', color: 'text-green-600' },
    { icon: '🔴', label: 'Sold', color: 'text-red-600' },
    { icon: '🟡', label: 'Reserved', color: 'text-yellow-600' },
    { icon: '🔵', label: 'Selected', color: 'text-blue-600' },
  ];

  return (
    <div className="flex items-center space-x-4">
      {legendItems.map((item) => (
        <div key={item.label} className="flex items-center space-x-1">
          <span className="text-sm">{item.icon}</span>
          <span className={`text-xs font-medium ${item.color}`}>
            {item.label}
          </span>
        </div>
      ))}
    </div>
  );
}