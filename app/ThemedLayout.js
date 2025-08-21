'use client';

import { ThemeClientWrapper } from './ThemeClientWrapper';
import { Geist, Geist_Mono } from 'next/font/google';

const geistSans = Geist({
  variable: '--font-geist-sans',
  subsets: ['latin'],
});

const geistMono = Geist_Mono({
  variable: '--font-geist-mono',
  subsets: ['latin'],
});

export default function ThemedLayout({ children }) {
  return (
    <ThemeClientWrapper>
      <html lang="en" className={`${geistSans.variable} ${geistMono.variable}`}>
        <body>
          {children}
        </body>
      </html>
    </ThemeClientWrapper>
  );
}
