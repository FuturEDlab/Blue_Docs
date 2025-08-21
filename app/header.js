'use client';

import { useTheme } from 'next-themes';
import { useEffect, useState } from 'react';

export default function Header() {
  const { theme, setTheme, resolvedTheme } = useTheme();
  const [mounted, setMounted] = useState(false);

  // Prevent hydration mismatch
  useEffect(() => {
    setMounted(true);
  }, []);

  if (!mounted) return null;

  const isDark = resolvedTheme === 'dark';

  return (
    <header className="header">
      <div id="header-leftside">
        <div id="logo">
          <img src="sting_logo2.png" alt="GVSU-STING Logo"/>
        </div>
        <div id="dark-light-mode-toggle">
          <label className="switch">
            <input
              type="checkbox"
              checked={isDark}
              onChange={() => setTheme(isDark ? 'light' : 'dark')}
            />
            <span className="slider round"></span>
          </label>
        </div>
      </div>

      <div className="header-search">
        <input
          type="text"
          placeholder='Try "How To: SSH"...'
          className="search-input"
        />
        <div className="bg-blue-500 text-red p-40">I’m Tailwind!</div>
      </div>
    </header>
  );
}
