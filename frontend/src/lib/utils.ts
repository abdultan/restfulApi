import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { format, isToday, isTomorrow, isYesterday } from 'date-fns';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatDate(date: string | Date, formatStr = 'PPP') {
  return format(new Date(date), formatStr);
}

export function formatDateTime(date: string | Date) {
  return format(new Date(date), 'PPP p');
}

export function formatCurrency(amount: number, currency = 'USD') {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
  }).format(amount);
}

export function getRelativeDate(date: string | Date) {
  const dateObj = new Date(date);
  
  if (isToday(dateObj)) {
    return 'Today';
  }
  
  if (isTomorrow(dateObj)) {
    return 'Tomorrow';
  }
  
  if (isYesterday(dateObj)) {
    return 'Yesterday';
  }
  
  return format(dateObj, 'MMM d, yyyy');
}

export function getEventStatus(event: { status: string; start_date: string; end_date: string }) {
  const now = new Date();
  const startDate = new Date(event.start_date);
  const endDate = new Date(event.end_date);

  if (event.status === 'sold_out') {
    return { status: 'Sold Out', variant: 'destructive' as const };
  }

  if (event.status === 'inactive') {
    return { status: 'Inactive', variant: 'secondary' as const };
  }

  if (now > endDate) {
    return { status: 'Past', variant: 'secondary' as const };
  }

  if (now < startDate) {
    return { status: 'Upcoming', variant: 'default' as const };
  }

  return { status: 'Ongoing', variant: 'success' as const };
}

export function getSeatStatusColor(status: string) {
  switch (status) {
    case 'available':
      return 'bg-green-500 hover:bg-green-600';
    case 'sold':
      return 'bg-red-500 cursor-not-allowed';
    case 'reserved':
      return 'bg-yellow-500 cursor-not-allowed';
    default:
      return 'bg-gray-500';
  }
}

export function getSeatStatusIcon(status: string) {
  switch (status) {
    case 'available':
      return '🟢';
    case 'sold':
      return '🔴';
    case 'reserved':
      return '🟡';
    default:
      return '⚪';
  }
}

export function debounce<T extends (...args: any[]) => void>(
  func: T,
  wait: number
): (...args: Parameters<T>) => void {
  let timeout: NodeJS.Timeout;
  
  return (...args: Parameters<T>) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
}

export function generateSeatLabel(row: string, number: string) {
  return `${row}${number}`;
}

export function parseApiError(error: any): string {
  if (error?.response?.data?.message) {
    return error.response.data.message;
  }
  
  if (error?.response?.data?.errors) {
    const errors = error.response.data.errors;
    const firstError = Object.values(errors)[0];
    if (Array.isArray(firstError)) {
      return firstError[0] as string;
    }
  }
  
  return error?.message || 'An unexpected error occurred';
}