"use client";

import { useState, useRef } from 'react';
import { Upload, X, Pencil } from 'lucide-react';

interface LineItem {
  id: string;
  name: string;
  description: string;
  quantity: number;
  unitCost: number;
}

interface InvoiceData {
  invoiceNumber: string;
  companyName: string;
  companyEmail: string;
  companyPhone: string;
  companyAddress: string;
  companyCity: string;
  companyPostalCode: string;
  companyState: string;
  companyCountry: string;
  clientFirstName: string;
  clientLastName: string;
  clientAddress: string;
  clientCity: string;
  clientState: string;
  clientPostalCode: string;
  clientEmail: string;
  clientPhone: string;
  issuedDate: string;
  paymentDate: string;
  lineItems: LineItem[];
  discount: number;
  discountType: 'percentage' | 'amount';
  taxRate: number;
  clientMessage: string;
  preFilledData?: {
    serviceName1: string;
    serviceDescription1: string;
    serviceCost1: string;
    serviceName2: string;
    serviceDescription2: string;
    serviceCost2: string;
    serviceName3: string;
    serviceDescription3: string;
    serviceCost3: string;
    
  };
}

interface GeneratedInvoiceProps {
  data: InvoiceData;
  companyLogo: string | null;
  calculations: {
    subtotal: number;
    discountAmount: number;
    taxAmount: number;
    total: number;
  };
}

const GeneratedEstimate = ({ data, companyLogo, calculations }: GeneratedInvoiceProps) => {
  return (
    <div className="p-8 bg-white" style={{ width: '190mm', maxWidth: '190mm' }}>
      <div className="flex justify-between mb-8">
        <div className="space-y-4">
          {companyLogo && (
            <img src={companyLogo} alt="Company Logo" className="h-20 object-contain mb-2" />
          )}
          <div className="text-gray-700 leading-tight space-y-0.5">
            <p className="text-xl font-semibold">{data.companyName}</p>
            <p>{data.companyAddress}</p>
            <p>{data.companyCity}, {data.companyState} {data.companyPostalCode}</p>
            <p className="text-sm">{data.companyPhone} | {data.companyEmail}</p>
          </div>
        </div>
        <div className="text-right space-y-2">
          <h2 className="text-3xl font-bold text-green-600 mb-2">ESTIMATE</h2>
          <h2 className="text-xl font-semibold text-gray-700">#{data.invoiceNumber}</h2>
          <div className="text-gray-600 space-y-0.5">
            <p>Issued: {data.issuedDate}</p>
            <p>Due: {data.paymentDate}</p>
          </div>
        </div>
      </div>

      <div className="mb-8 border-b border-gray-200 pb-4">
        <h2 className="text-lg font-bold mb-2">BILL TO:</h2>
        <div className="text-gray-700 space-y-0.5">
          {data.clientFirstName || data.clientLastName ? (
            <p className="font-medium text-lg">{data.clientFirstName} {data.clientLastName}</p>
          ) : null}
          {data.clientAddress ? (
            <p>{data.clientAddress}</p>
          ) : null}
          {data.clientCity || data.clientState || data.clientPostalCode ? (
            <p>{[data.clientCity, data.clientState, data.clientPostalCode].filter(Boolean).join(', ')}</p>
          ) : null}
          {(data.clientPhone || data.clientEmail) ? (
            <p className="text-sm text-gray-600">{[data.clientPhone, data.clientEmail].filter(Boolean).join(' | ')}</p>
          ) : null}
        </div>
      </div>

      <h2 className="text-lg font-bold mb-4">Services Provided</h2>
      <table className="w-full mb-8">
        <thead className="bg-green-600 text-white">
          <tr>
            <th className="py-3 px-4 text-left">DESCRIPTION</th>
            <th className="py-3 px-4 text-left">PRODUCT / SERVICE</th>
            <th className="py-3 px-4 text-center">QTY</th>
            <th className="py-3 px-4 text-right">UNIT PRICE</th>
            <th className="py-3 px-4 text-right">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          {data.lineItems.map((item, index) => (
            <tr key={item.id} className={index % 2 === 0 ? 'bg-gray-50' : ''}>
              <td className="py-3 px-4 border-b text-sm">{item.description}</td>
              <td className="py-3 px-4 border-b text-sm">{item.name}</td>
              <td className="py-3 px-4 border-b text-center text-sm">{item.quantity}</td>
              <td className="py-3 px-4 border-b text-right text-sm">${item.unitCost.toFixed(2)}</td>
              <td className="py-3 px-4 border-b text-right font-medium">${(item.quantity * item.unitCost).toFixed(2)}</td>
            </tr>
          ))}
        </tbody>
      </table>

      <div className="flex justify-end">
        <div className="w-64 space-y-2">
          <div className="flex justify-between text-sm">
            <span>Subtotal:</span>
            <span>${calculations.subtotal.toFixed(2)}</span>
          </div>
          {data.discount > 0 && (
            <div className="flex justify-between text-sm text-red-600">
              <span>Discount:</span>
              <span>-${calculations.discountAmount.toFixed(2)}</span>
            </div>
          )}
          <div className="flex justify-between text-sm">
            <span>Tax ({data.taxRate}%):</span>
            <span>${calculations.taxAmount.toFixed(2)}</span>
          </div>
          <div className="flex justify-between font-bold text-xl border-t border-gray-300 pt-2">
            <span>Total:</span>
            <span>${calculations.total.toFixed(2)}</span>
          </div>
        </div>
      </div>

      {data.clientMessage && (
        <div className="mt-8 text-gray-700">
          <p className="text-sm italic">{data.clientMessage}</p>
        </div>
      )}
    </div>
  );
};

export default function EstimateGenerator({ preFilledData }: { preFilledData?: InvoiceData['preFilledData'] }) {
  const invoiceRef = useRef<HTMLDivElement>(null);
  const formRef = useRef<HTMLFormElement>(null);
  const [errorMessage, setErrorMessage] = useState('');
  
  const generatedInvoiceRef = useRef<HTMLDivElement>(null);
  const [companyLogo, setCompanyLogo] = useState<string | null>(null);
  const [invoiceData, setInvoiceData] = useState<InvoiceData>({
    invoiceNumber: '1',
    companyName: '',
    companyEmail: '',
    companyPhone: '',
    companyAddress: '',
    companyCity: '',
    companyPostalCode: '',
    companyState: '',
    companyCountry: 'United States',
    clientFirstName: '',
    clientLastName: '',
    clientAddress: '',
    clientCity: '',
    clientState: '',
    clientPostalCode: '',
    clientEmail: '',
    clientPhone: '',
    issuedDate: new Date().toISOString().split('T')[0],
    paymentDate: new Date().toISOString().split('T')[0],
    lineItems: preFilledData ? [
      ...(preFilledData.serviceName1 ? [{
        id: '1',
        name: preFilledData.serviceName1,
        description: preFilledData.serviceDescription1,
        quantity: 1,
        unitCost: parseFloat(preFilledData.serviceCost1 || '0') || 0
      }] : []),
      ...(preFilledData.serviceName2 ? [{
        id: '2',
        name: preFilledData.serviceName2,
        description: preFilledData.serviceDescription2,
        quantity: 1,
        unitCost: parseFloat(preFilledData.serviceCost2 || '0') || 0
      }] : []),
      ...(preFilledData.serviceName3 ? [{
        id: '3',
        name: preFilledData.serviceName3,
        description: preFilledData.serviceDescription3,
        quantity: 1,
        unitCost: parseFloat(preFilledData.serviceCost3 || '0') || 0
      }] : [])
    ] : [],
    clientMessage: '',
    discount: 0,
    discountType: 'percentage',
    taxRate: 13,
  });

  const handleLogoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setCompanyLogo(reader.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleRemoveLogo = () => {
    setCompanyLogo(null);
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setInvoiceData(prev => ({ ...prev, [name]: value }));
  };

  const handleDateChange = (name: string, value: string) => {
    setInvoiceData(prev => ({ ...prev, [name]: value }));
  };

  const handleLineItemChange = (id: string, field: keyof LineItem, value: string | number) => {
    setInvoiceData(prev => ({
      ...prev,
      lineItems: prev.lineItems.map(item => 
        item.id === id ? { ...item, [field]: value } : item
      )
    }));
  };

  const addLineItem = () => {
    setInvoiceData(prev => ({
      ...prev,
      lineItems: [
        ...prev.lineItems,
        {
          id: Math.random().toString(36).substr(2, 9),
          name: '',
          description: '',
          quantity: 1,
          unitCost: 0
        }
      ]
    }));
  };

  const removeLineItem = (id: string) => {
    setInvoiceData(prev => ({
      ...prev,
      lineItems: prev.lineItems.filter(item => item.id !== id)
    }));
  };

  const handleDiscountTypeChange = (value: 'percentage' | 'amount') => {
    setInvoiceData(prev => ({ ...prev, discountType: value }));
  };

  const calculations = {
    subtotal: invoiceData.lineItems.reduce((sum, item) => sum + (item.quantity * item.unitCost), 0),
    get discountAmount() {
      const subtotal = this.subtotal;
      if (invoiceData.discount === 0) return 0;
      return invoiceData.discountType === 'percentage' 
        ? (Number(invoiceData.discount) / 100) * subtotal
        : Number(invoiceData.discount);
    },
    get taxAmount() {
      const subtotal = this.subtotal;
      const discountAmount = this.discountAmount;
      return (Number(invoiceData.taxRate) / 100) * (subtotal - discountAmount);
    },
    get total() {
      const subtotal = this.subtotal;
      const discountAmount = this.discountAmount;
      const taxAmount = this.taxAmount;
      return subtotal - discountAmount + taxAmount;
    }
  };

  function inlineStyles(element: HTMLElement): HTMLElement {
    const clone = element.cloneNode(true) as HTMLElement;
  
    const copyStyles = (source: HTMLElement, target: HTMLElement) => {
      const computed = getComputedStyle(source);
      for (let i = 0; i < computed.length; i++) {
        const key = computed[i];
        target.style.setProperty(key, computed.getPropertyValue(key));
      }
  
      Array.from(source.children).forEach((srcChild, i) => {
        copyStyles(srcChild as HTMLElement, target.children[i] as HTMLElement);
      });
    };
  
    copyStyles(element, clone);
    return clone;
  }

  const resetForm = () => {
    setInvoiceData({
      invoiceNumber: '',
      companyName: '',
      companyEmail: '',
      companyPhone: '',
      companyAddress: '',
      companyCity: '',
      companyPostalCode: '',
      companyState: '',
      companyCountry: 'United States',
      clientFirstName: '',
      clientLastName: '',
      clientAddress: '',
      clientCity: '',
      clientState: '',
      clientPostalCode: '',
      clientEmail: '',
      clientPhone: '',
      issuedDate: new Date().toISOString().split('T')[0],
      paymentDate: new Date().toISOString().split('T')[0],
      lineItems: [],
      discount: 0,
      discountType: 'percentage',
      taxRate: 13,
      clientMessage: ''
    });
    setCompanyLogo(null);
    setErrorMessage('');
  };

  const generateInvoice = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!generatedInvoiceRef.current) return;
    setErrorMessage('');
    if (!validateRequiredFields()) {
      return;
    }

    try {
      const element = generatedInvoiceRef.current;
      const styledElement = inlineStyles(element);
      const opt = {
        margin: 10,
        filename: `estimate-${invoiceData.invoiceNumber}.pdf`,
        image: { type: 'jpeg', quality: 1 },
        html2canvas: { 
          scale: 2,
          useCORS: true,
          logging: false,
          backgroundColor: '#ffffff'
        },
        jsPDF: { 
          unit: 'mm', 
          format: 'a4', 
          orientation: 'portrait'
        }
      } as const;

      const html2pdf = (await import('html2pdf.js')).default;
      await html2pdf().set(opt).from(styledElement).save();
      resetForm(); // Reset form after successful PDF generation
    } catch (error) {
      console.error('Error generating PDF:', error);
      setErrorMessage('There was an error generating the PDF. Please try again.');
    }
  };

  function validateRequiredFields() {
    if (!formRef.current) return true;
    const requiredFields = formRef.current.querySelectorAll('[required]');
    let allValid = true;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Check for at least one line item
    if (invoiceData.lineItems.length === 0) {
      setErrorMessage('Please add at least one line item.');
      return false;
    }

    // Check for empty line items
    const emptyLineItems = invoiceData.lineItems.filter(item => 
      !item.name.trim() || !item.description.trim() || item.quantity <= 0 || item.unitCost <= 0
    );
    if (emptyLineItems.length > 0) {
      setErrorMessage('Please fill in all line item details.');
      return false;
    }

    requiredFields.forEach((field) => {
      // Remove previous error border
      field.className = field.className.replace('border-red-500', '');
      
      if (
        (field instanceof HTMLInputElement ||
          field instanceof HTMLTextAreaElement ||
          field instanceof HTMLSelectElement) &&
        !field.value.trim()
      ) {
        field.className += ' border-red-500';
        allValid = false;
      }

      // Validate email format for email inputs
      if (
        (field instanceof HTMLInputElement) &&
        field.type === 'email' &&
        field.value.trim() &&
        !emailRegex.test(field.value)
      ) {
        field.className += ' border-red-500';
        allValid = false;
      }
    });

    // Optionally focus the first invalid field
    if (!allValid) {
      const firstInvalid = Array.from(requiredFields).find(field => {
        if (
          (field instanceof HTMLInputElement ||
            field instanceof HTMLTextAreaElement ||
            field instanceof HTMLSelectElement)
        ) {
          // Check for empty fields
          if (!field.value.trim()) return true;
          
          // Check for invalid email format
          if (
            field instanceof HTMLInputElement &&
            field.type === 'email' &&
            !emailRegex.test(field.value)
          ) {
            return true;
          }
        }
        return false;
      });
      
      if (firstInvalid) (firstInvalid as HTMLElement).focus();
    }
    return allValid;
  }
  return (
    <>
      <div id="templateform" ref={invoiceRef} className="p-4 max-w-7xl mx-auto bg-[#F8F8F8] my-8" style={{
        scrollMarginTop: '80px' // Adjust this value based on your header height
      }}>
        <form ref={formRef} onSubmit={generateInvoice} noValidate>
        <div className="border-b-2 border-black pb-2 mb-8 bg-white rounded-lg p-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <div 
                className="w-32 h-24 border border-gray-300 rounded-md flex items-center justify-center overflow-hidden relative"
                style={{ cursor: 'pointer' }}
              >
                {companyLogo ? (
                  <div className="relative w-full h-full">
                    <img 
                      src={companyLogo} 
                      alt="Company logo" 
                      className="w-full h-full object-contain"
                    />
                    <div className="absolute inset-0 bg-black bg-opacity-50 opacity-0 hover:opacity-100 flex items-center justify-center transition-opacity">
                      <div className="flex flex-col items-center gap-2">
                        <label htmlFor="logo-upload" className="cursor-pointer p-2 bg-white rounded-full">
                          <Pencil size={18} className="text-gray-700" />
                        </label>
                        <button 
                          onClick={handleRemoveLogo}
                          className="cursor-pointer p-2 bg-white rounded-full"
                        >
                          <X size={18} className="text-red-500" />
                        </button>
                      </div>
                    </div>
                  </div>
                ) : (
                  <label htmlFor="logo-upload" className="cursor-pointer text-gray-500 flex flex-col items-center">
                    <Upload size={24} />
                    <span className="text-xs mt-1">Add Logo</span>
                    <span className="text-xs">PNG, JPG, or SVG</span>
                  </label>
                )}
                <input 
                  id="logo-upload"
                  type="file" 
                  accept="image/*" 
                  className="hidden"
                  onChange={handleLogoUpload}
                />
              </div>
            </div>

            <div className="flex items-center">
              <div className="text-right">
                <h2 className="text-2xl font-bold">Estimate #</h2>
                <div className="mt-2">
                  <input 
                    value={invoiceData.invoiceNumber}
                    onChange={handleInputChange}
                    name="invoiceNumber"
                    className="max-w-[80px] border rounded p-2"
                    required
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div className="border rounded-lg p-6 bg-white">
            <h2 className="text-xl font-bold mb-4">Company Information</h2>
            <div className="grid gap-4">
              <div>
                <input 
                  placeholder="Company Name *" 
                  name="companyName"
                  value={invoiceData.companyName}
                  onChange={handleInputChange}
                  className="w-full border rounded p-2"
                  required
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <input 
                  placeholder="Email *" 
                  type="email"
                  name="companyEmail"
                  value={invoiceData.companyEmail}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                  required
                />
                <input 
                  placeholder="Phone *" 
                  type="tel"
                  name="companyPhone"
                  value={invoiceData.companyPhone}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                  required
                />
              </div>
              <input 
                placeholder="Company Address *" 
                name="companyAddress"
                value={invoiceData.companyAddress}
                onChange={handleInputChange}
                className="w-full border rounded p-2"
                required
              />
              <div className="grid grid-cols-2 gap-4">
                <input 
                  placeholder="City *" 
                  name="companyCity"
                  value={invoiceData.companyCity}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                  required
                />
                <input 
                  placeholder="Zip/Postal Code *" 
                  name="companyPostalCode"
                  value={invoiceData.companyPostalCode}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                  required
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <input 
                  placeholder="State/Province *" 
                  name="companyState"
                  value={invoiceData.companyState}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                  required
                />
                <select 
                  value={invoiceData.companyCountry} 
                  onChange={handleInputChange}
                  name="companyCountry"
                  className="border rounded p-2"
                >
                  <option value="">Select Country</option>
                  <option value="Afghanistan">Afghanistan</option>
                  <option value="Albania">Albania</option>
                  <option value="Algeria">Algeria</option>
                  <option value="Andorra">Andorra</option>
                  <option value="Angola">Angola</option>
                  <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                  <option value="Argentina">Argentina</option>
                  <option value="Armenia">Armenia</option>
                  <option value="Australia">Australia</option>
                  <option value="Austria">Austria</option>
                  <option value="Azerbaijan">Azerbaijan</option>
                  <option value="Bahamas">Bahamas</option>
                  <option value="Bahrain">Bahrain</option>
                  <option value="Bangladesh">Bangladesh</option>
                  <option value="Barbados">Barbados</option>
                  <option value="Belarus">Belarus</option>
                  <option value="Belgium">Belgium</option>
                  <option value="Belize">Belize</option>
                  <option value="Benin">Benin</option>
                  <option value="Bhutan">Bhutan</option>
                  <option value="Bolivia">Bolivia</option>
                  <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                  <option value="Botswana">Botswana</option>
                  <option value="Brazil">Brazil</option>
                  <option value="Brunei">Brunei</option>
                  <option value="Bulgaria">Bulgaria</option>
                  <option value="Burkina Faso">Burkina Faso</option>
                  <option value="Burundi">Burundi</option>
                  <option value="Cabo Verde">Cabo Verde</option>
                  <option value="Cambodia">Cambodia</option>
                  <option value="Cameroon">Cameroon</option>
                  <option value="Canada">Canada</option>
                  <option value="Central African Republic">Central African Republic</option>
                  <option value="Chad">Chad</option>
                  <option value="Chile">Chile</option>
                  <option value="China">China</option>
                  <option value="Colombia">Colombia</option>
                  <option value="Comoros">Comoros</option>
                  <option value="Congo">Congo</option>
                  <option value="Costa Rica">Costa Rica</option>
                  <option value="Croatia">Croatia</option>
                  <option value="Cuba">Cuba</option>
                  <option value="Cyprus">Cyprus</option>
                  <option value="Czech Republic">Czech Republic</option>
                  <option value="Denmark">Denmark</option>
                  <option value="Djibouti">Djibouti</option>
                  <option value="Dominica">Dominica</option>
                  <option value="Dominican Republic">Dominican Republic</option>
                  <option value="Ecuador">Ecuador</option>
                  <option value="Egypt">Egypt</option>
                  <option value="El Salvador">El Salvador</option>
                  <option value="Equatorial Guinea">Equatorial Guinea</option>
                  <option value="Eritrea">Eritrea</option>
                  <option value="Estonia">Estonia</option>
                  <option value="Eswatini">Eswatini</option>
                  <option value="Ethiopia">Ethiopia</option>
                  <option value="Fiji">Fiji</option>
                  <option value="Finland">Finland</option>
                  <option value="France">France</option>
                  <option value="Gabon">Gabon</option>
                  <option value="Gambia">Gambia</option>
                  <option value="Georgia">Georgia</option>
                  <option value="Germany">Germany</option>
                  <option value="Ghana">Ghana</option>
                  <option value="Greece">Greece</option>
                  <option value="Grenada">Grenada</option>
                  <option value="Guatemala">Guatemala</option>
                  <option value="Guinea">Guinea</option>
                  <option value="Guinea-Bissau">Guinea-Bissau</option>
                  <option value="Guyana">Guyana</option>
                  <option value="Haiti">Haiti</option>
                  <option value="Honduras">Honduras</option>
                  <option value="Hungary">Hungary</option>
                  <option value="Iceland">Iceland</option>
                  <option value="India">India</option>
                  <option value="Indonesia">Indonesia</option>
                  <option value="Iran">Iran</option>
                  <option value="Iraq">Iraq</option>
                  <option value="Ireland">Ireland</option>
                  <option value="Israel">Israel</option>
                  <option value="Italy">Italy</option>
                  <option value="Jamaica">Jamaica</option>
                  <option value="Japan">Japan</option>
                  <option value="Jordan">Jordan</option>
                  <option value="Kazakhstan">Kazakhstan</option>
                  <option value="Kenya">Kenya</option>
                  <option value="Kiribati">Kiribati</option>
                  <option value="Korea, North">Korea, North</option>
                  <option value="Korea, South">Korea, South</option>
                  <option value="Kosovo">Kosovo</option>
                  <option value="Kuwait">Kuwait</option>
                  <option value="Kyrgyzstan">Kyrgyzstan</option>
                  <option value="Laos">Laos</option>
                  <option value="Latvia">Latvia</option>
                  <option value="Lebanon">Lebanon</option>
                  <option value="Lesotho">Lesotho</option>
                  <option value="Liberia">Liberia</option>
                  <option value="Libya">Libya</option>
                  <option value="Liechtenstein">Liechtenstein</option>
                  <option value="Lithuania">Lithuania</option>
                  <option value="Luxembourg">Luxembourg</option>
                  <option value="Madagascar">Madagascar</option>
                  <option value="Malawi">Malawi</option>
                  <option value="Malaysia">Malaysia</option>
                  <option value="Maldives">Maldives</option>
                  <option value="Mali">Mali</option>
                  <option value="Malta">Malta</option>
                  <option value="Marshall Islands">Marshall Islands</option>
                  <option value="Mauritania">Mauritania</option>
                  <option value="Mauritius">Mauritius</option>
                  <option value="Mexico">Mexico</option>
                  <option value="Micronesia">Micronesia</option>
                  <option value="Moldova">Moldova</option>
                  <option value="Monaco">Monaco</option>
                  <option value="Mongolia">Mongolia</option>
                  <option value="Montenegro">Montenegro</option>
                  <option value="Morocco">Morocco</option>
                  <option value="Mozambique">Mozambique</option>
                  <option value="Myanmar">Myanmar</option>
                  <option value="Namibia">Namibia</option>
                  <option value="Nauru">Nauru</option>
                  <option value="Nepal">Nepal</option>
                  <option value="Netherlands">Netherlands</option>
                  <option value="New Zealand">New Zealand</option>
                  <option value="Nicaragua">Nicaragua</option>
                  <option value="Niger">Niger</option>
                  <option value="Nigeria">Nigeria</option>
                  <option value="North Macedonia">North Macedonia</option>
                  <option value="Norway">Norway</option>
                  <option value="Oman">Oman</option>
                  <option value="Pakistan">Pakistan</option>
                  <option value="Palau">Palau</option>
                  <option value="Palestine">Palestine</option>
                  <option value="Panama">Panama</option>
                  <option value="Papua New Guinea">Papua New Guinea</option>
                  <option value="Paraguay">Paraguay</option>
                  <option value="Peru">Peru</option>
                  <option value="Philippines">Philippines</option>
                  <option value="Poland">Poland</option>
                  <option value="Portugal">Portugal</option>
                  <option value="Qatar">Qatar</option>
                  <option value="Romania">Romania</option>
                  <option value="Russia">Russia</option>
                  <option value="Rwanda">Rwanda</option>
                  <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                  <option value="Saint Lucia">Saint Lucia</option>
                  <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                  <option value="Samoa">Samoa</option>
                  <option value="San Marino">San Marino</option>
                  <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                  <option value="Saudi Arabia">Saudi Arabia</option>
                  <option value="Senegal">Senegal</option>
                  <option value="Serbia">Serbia</option>
                  <option value="Seychelles">Seychelles</option>
                  <option value="Sierra Leone">Sierra Leone</option>
                  <option value="Singapore">Singapore</option>
                  <option value="Slovakia">Slovakia</option>
                  <option value="Slovenia">Slovenia</option>
                  <option value="Solomon Islands">Solomon Islands</option>
                  <option value="Somalia">Somalia</option>
                  <option value="South Africa">South Africa</option>
                  <option value="South Sudan">South Sudan</option>
                  <option value="Spain">Spain</option>
                  <option value="Sri Lanka">Sri Lanka</option>
                  <option value="Sudan">Sudan</option>
                  <option value="Suriname">Suriname</option>
                  <option value="Sweden">Sweden</option>
                  <option value="Switzerland">Switzerland</option>
                  <option value="Syria">Syria</option>
                  <option value="Taiwan">Taiwan</option>
                  <option value="Tajikistan">Tajikistan</option>
                  <option value="Tanzania">Tanzania</option>
                  <option value="Thailand">Thailand</option>
                  <option value="Timor-Leste">Timor-Leste</option>
                  <option value="Togo">Togo</option>
                  <option value="Tonga">Tonga</option>
                  <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                  <option value="Tunisia">Tunisia</option>
                  <option value="Turkey">Turkey</option>
                  <option value="Turkmenistan">Turkmenistan</option>
                  <option value="Tuvalu">Tuvalu</option>
                  <option value="Uganda">Uganda</option>
                  <option value="Ukraine">Ukraine</option>
                  <option value="United Arab Emirates">United Arab Emirates</option>
                  <option value="United Kingdom">United Kingdom</option>
                  <option value="United States">United States</option>
                  <option value="Uruguay">Uruguay</option>
                  <option value="Uzbekistan">Uzbekistan</option>
                  <option value="Vanuatu">Vanuatu</option>
                  <option value="Vatican City">Vatican City</option>
                  <option value="Venezuela">Venezuela</option>
                  <option value="Vietnam">Vietnam</option>
                  <option value="Yemen">Yemen</option>
                  <option value="Zambia">Zambia</option>
                  <option value="Zimbabwe">Zimbabwe</option>
                </select>
              </div>
            </div>
          </div>

          <div className="border rounded-lg p-6 bg-white">
            <h2 className="text-xl font-bold mb-4">Estimate Details</h2>
            <div className="grid gap-6">
              <div>
                <label className="block text-sm font-medium mb-2">Issued Date *</label>
                <input
                  type="date"
                  value={invoiceData.issuedDate}
                  onChange={(e) => handleDateChange('issuedDate', e.target.value)}
                  name="issuedDate"
                  className="w-full border rounded p-2"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">Payment Date *</label>
                <input
                  type="date"
                  value={invoiceData.paymentDate}
                  onChange={(e) => handleDateChange('paymentDate', e.target.value)}
                  name="paymentDate"
                  className="w-full border rounded p-2"
                  required
                />
              </div>
            </div>
          </div>
        </div>

        <div className="mt-8">
          <div className="border rounded-lg p-6 bg-white">
            <h2 className="text-xl font-bold mb-4">Client Information</h2>
            <div className="grid gap-4">
              <div className="grid grid-cols-4 gap-4">
                <input 
                  placeholder="Client First Name *" 
                  name="clientFirstName"
                  value={invoiceData.clientFirstName}
                  onChange={handleInputChange}
                  className="border rounded p-2 col-span-1"
                  required
                />
                <input 
                  placeholder="Client Last Name" 
                  name="clientLastName"
                  value={invoiceData.clientLastName}
                  onChange={handleInputChange}
                  className="border rounded p-2 col-span-1"
                />
                <input 
                  placeholder="Client Address" 
                  name="clientAddress"
                  value={invoiceData.clientAddress}
                  onChange={handleInputChange}
                  className="border rounded p-2 col-span-2"
                />
              </div>
              <div className="grid grid-cols-4 gap-4">
                <input 
                  placeholder="City" 
                  name="clientCity"
                  value={invoiceData.clientCity}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                />
                <input 
                  placeholder="State/Province" 
                  name="clientState"
                  value={invoiceData.clientState}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                />
                <input 
                  placeholder="Zip/Postal Code" 
                  name="clientPostalCode"
                  value={invoiceData.clientPostalCode}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <input 
                  placeholder="Client Email Address" 
                  type="email"
                  name="clientEmail"
                  value={invoiceData.clientEmail}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                />
                <input 
                  placeholder="Client Phone Number" 
                  type="tel"
                  name="clientPhone"
                  value={invoiceData.clientPhone}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                />
              </div>
            </div>
          </div>
        </div>

        <div className="mt-8 bg-white rounded-lg border p-6">
          <div className="grid grid-cols-12 gap-4 py-2 font-semibold">
            <div className="col-span-4">Product/Service</div>
            <div className="col-span-2 text-center">Quantity</div>
            <div className="col-span-2 text-center">Unit Cost ($)</div>
            <div className="col-span-2 text-right">Total ($)</div>
            <div className="col-span-1"></div>
          </div>

          {invoiceData.lineItems.map((item) => (
            <div key={item.id} className="grid grid-cols-12 gap-4 items-center py-2">
              <div className="col-span-4">
                <div className="space-y-2">
                  <input 
                    type="text"
                    value={item.name}
                    onChange={(e) => handleLineItemChange(item.id, 'name', e.target.value)}
                    className="w-full border rounded p-2"
                    placeholder="Product/Service Name"
                  />
                  <input 
                    type="text"
                    value={item.description}
                    onChange={(e) => handleLineItemChange(item.id, 'description', e.target.value)}
                    className="w-full border rounded p-2"
                    placeholder="Description"
                  />
                </div>
              </div>
              <div className="col-span-2">
                <input 
                  type="number"
                  min="0"
                  value={item.quantity}
                  onChange={(e) => handleLineItemChange(item.id, 'quantity', parseInt(e.target.value) || 0)}
                  className="w-full border rounded p-2 text-center"
                />
              </div>
              <div className="col-span-2">
                <input 
                  type="number"
                  min="0"
                  step="0.01"
                  value={item.unitCost}
                  onChange={(e) => handleLineItemChange(item.id, 'unitCost', parseFloat(e.target.value) || 0)}
                  className="w-full border rounded p-2 text-center"
                />
              </div>
              <div className="col-span-2 font-semibold flex justify-end">
                ${(item.quantity * item.unitCost).toFixed(2)}
              </div>
              <div className="col-span-1 flex justify-center">
                <button
                  onClick={() => removeLineItem(item.id)}
                  className="h-8 w-8 text-gray-500 hover:text-red-500"
                >
                  <X size={16} />
                </button>
              </div>
            </div>
          ))}

          <div className="mt-4 flex">
            <button 
              onClick={addLineItem}
              className="px-4 py-2 text-sm border border-dashed border-gray-300 hover:border-gray-400 rounded bg-gray-50 hover:bg-gray-100 text-gray-600 hover:text-gray-900 flex items-center gap-2"
            >
              <span>+</span> Add Line Item
            </button>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
          <div className="flex-1">
            <textarea 
              placeholder="Client Message" 
              name="clientMessage"
              value={invoiceData.clientMessage}
              onChange={handleInputChange}
              className="w-full h-full border rounded p-2 resize-none"
            />
          </div>

          <div className="space-y-3">
            <div className="flex justify-between">
              <span>Subtotal</span>
              <span className="font-semibold">${calculations.subtotal.toFixed(2)}</span>
            </div>
            
            <div className="flex justify-between">
              <span>Discount</span>
              <div className="flex">
                <input 
                  type="number"
                  min="0"
                  name="discount"
                  value={invoiceData.discount}
                  onChange={handleInputChange}
                  className="w-16 border rounded p-1 text-right mr-2"
                />
                <select
                  value={invoiceData.discountType}
                  onChange={(e) => handleDiscountTypeChange(e.target.value as 'percentage' | 'amount')}
                  className="border rounded p-1 min-w-[40px]"
                >
                  <option value="amount">$</option>
                  <option value="percentage">%</option>
                </select>
                {invoiceData.discount === 0 ? (
                  <button 
                    className="ml-2 text-blue-600 hover:text-blue-800"
                    onClick={() => setInvoiceData({...invoiceData, discount: invoiceData.discountType === 'percentage' ? 10 : 100})}
                  >
                    Add a discount
                  </button>
                ) : (
                  <span className="ml-8 w-24 text-right">
                    ${calculations.discountAmount.toFixed(2)}
                  </span>
                )}
              </div>
            </div>
            
            <div className="flex justify-between">
              <span>Tax %</span>
              <div className="flex">
                <input 
                  type="number"
                  min="0"
                  max="100"
                  name="taxRate"
                  value={invoiceData.taxRate}
                  onChange={handleInputChange}
                  className="w-16 border rounded p-1 text-right"
                />
                <span className="ml-8 w-24 text-right">${calculations.taxAmount.toFixed(2)}</span>
              </div>
            </div>
            
            <div className="flex justify-between pt-3 border-t border-gray-200">
              <span className="font-bold">Total</span>
              <span className="font-bold text-xl">${calculations.total.toFixed(2)}</span>
            </div>

            <div className="mt-8">
              <button 
                type="submit"
                className="w-full bg-black text-white rounded p-2"
              >
                Generate Free Estimate
              </button>
              {errorMessage && (
                <div className="text-red-500 mb-4">{errorMessage}</div>
              )}
            </div>
          </div>
        </div>
        </form>
      </div>

      <div className="hidden">
        <div ref={generatedInvoiceRef}>
          <GeneratedEstimate 
            data={invoiceData} 
            companyLogo={companyLogo} 
            calculations={calculations}
          />
        </div>
      </div>
    </>
  );
} 