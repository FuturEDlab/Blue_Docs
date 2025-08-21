'use client';

import { ThemeProvider } from 'next-themes';

export function ThemeClientWrapper({ children }) {
  return (
    <ThemeProvider attribute="class" defaultTheme="system" enableSystem>
      {children}
    </ThemeProvider>
  );
}
