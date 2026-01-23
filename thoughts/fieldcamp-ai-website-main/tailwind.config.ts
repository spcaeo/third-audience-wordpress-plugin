import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        background: "var(--background)",
        foreground: "var(--foreground)",
      },
      screens: {
        'xs': '480px',    // Extra small devices
        'sm': '640px',    // Small devices (default)
        'md': '768px',    // Medium devices (default)
        'lg': '1024px',   // Large devices (default)
        'xl': '1280px',   // Extra large devices (default)
        '2xl': '1536px',  // 2X Large devices (default)
        '3xl': '1920px',  // Custom: 3X Large devices
      },
      fontFamily: {
        raleway: ['Raleway', 'sans-serif'],  
        sfPro: ['SFPRO', 'sans-serif'],  
      },
    },
  },
  plugins: [],
};
export default config;
