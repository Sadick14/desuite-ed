export function formatCurrency(value: number, decimals: number = 1): string {
  if (value === 0) {
return '0';
}

  const absoluteValue = Math.abs(value);
  const sign = value < 0 ? '-' : '';

  if (absoluteValue >= 1_000_000_000_000) {
    return `${sign}${(absoluteValue / 1_000_000_000_000).toFixed(decimals)}T`;
  }

  if (absoluteValue >= 1_000_000_000) {
    return `${sign}${(absoluteValue / 1_000_000_000).toFixed(decimals)}B`;
  }

  if (absoluteValue >= 1_000_000) {
    return `${sign}${(absoluteValue / 1_000_000).toFixed(decimals)}M`;
  }

  if (absoluteValue >= 1_000) {
    return `${sign}${(absoluteValue / 1_000).toFixed(decimals)}K`;
  }

  return `${sign}${absoluteValue.toFixed(0)}`;
}

export function formatCurrencyFull(value: number): string {
  if (value === 0) {
return 'GHS 0';
}

  return `GHS ${value.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
}

export function formatCurrencyCompact(value: number, decimals: number = 1): string {
  if (value === 0) {
return 'GHS 0';
}

  return `GHS ${formatCurrency(value, decimals)}`;
}

export function formatDate(date: string | Date): string {
  const dateObj = typeof date === 'string' ? new Date(date) : date;
  return dateObj.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}
