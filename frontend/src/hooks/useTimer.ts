import { useState, useEffect, useRef } from 'react';

interface UseTimerProps {
  initialTime: number;
  onExpire?: () => void;
  autoStart?: boolean;
}

export function useTimer({ initialTime, onExpire, autoStart = false }: UseTimerProps) {
  const [timeLeft, setTimeLeft] = useState(initialTime);
  const [isActive, setIsActive] = useState(autoStart);
  const intervalRef = useRef<NodeJS.Timeout | null>(null);

  useEffect(() => {
    if (isActive && timeLeft > 0) {
      intervalRef.current = setInterval(() => {
        setTimeLeft((time) => {
          if (time <= 1) {
            setIsActive(false);
            onExpire?.();
            return 0;
          }
          return time - 1;
        });
      }, 1000);
    } else {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
      }
    }

    return () => {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
      }
    };
  }, [isActive, timeLeft, onExpire]);

  const start = () => setIsActive(true);
  const pause = () => setIsActive(false);
  const reset = (newTime?: number) => {
    setTimeLeft(newTime ?? initialTime);
    setIsActive(false);
  };

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  };

  return {
    timeLeft,
    isActive,
    isExpired: timeLeft === 0,
    formattedTime: formatTime(timeLeft),
    start,
    pause,
    reset,
  };
}