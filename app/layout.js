import './globals.css';
import { Geist, Geist_Mono } from 'next/font/google';
import { ThemeClientWrapper } from './ThemeClientWrapper';
import Header from './header.js'

const geistSans = Geist({
  variable: '--font-geist-sans',
  subsets: ['latin'],
});

const geistMono = Geist_Mono({
  variable: '--font-geist-mono',
  subsets: ['latin'],
});

export const metadata = {
  title: 'Blue Docs',
  description: 'Technical docs by GVSU staff and students.',
};

export default function RootLayout({ children }) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body className={`${geistSans.variable} ${geistMono.variable}`}>
        <ThemeClientWrapper>
          <Header />
          <main>{children}</main>
        </ThemeClientWrapper>
      </body>
    </html>
  );
}
