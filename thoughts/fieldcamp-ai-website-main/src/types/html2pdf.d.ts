declare module 'html2pdf.js' {
    interface Html2PdfOptions {
      margin?: number | number[];
      filename?: string;
      image?: {
        type?: string;
        quality?: number;
      };
      html2canvas?: {
        scale?: number;
        useCORS?: boolean;
        logging?: boolean;
        backgroundColor?: string | null;
      };
      jsPDF?: {
        unit?: 'pt' | 'mm' | 'cm' | 'in';
        format?: string | [number, number];
        orientation?: 'portrait' | 'landscape';
      };
    }
  
    const html2pdf: () => {
      from: (element: HTMLElement | Element | string) => any;
      set: (options: Html2PdfOptions) => any;
      save: () => Promise<void>;
    };
  
    export default html2pdf;
  }
  