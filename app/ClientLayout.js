import { Geist, Geist_Mono } from "next/font/google";
import { ThemeClientWrapper } from './ThemeClientWrapper';
import "./globals.css";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata = {
  title: "Blue Docs",
  description: "Technical docs by GVSU staff and students.",
};

function Header() {
  return (
    <header className="header">
      <div className="header-logo">
        <img src="./sting_logo.jpg" alt="GVSU-STING Logo"/>
      </div>
      <div className="header-search">
        <input type="text" placeholder="Try &quot;How To: SSH&quot;..." className="search-input" />
      </div>
    </header>
  );
}

export default function RootLayout({ children }) {
  return (
    <html lang="en">
      <body className={`${geistSans.variable} ${geistMono.variable}`}>
        <ThemeClientWrapper>
          <Header />
          <main>{children}</main>
        </ThemeClientWrapper>
      </body>
    </html>
  );
}
