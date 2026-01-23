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

interface ReceiptData {
  receiptNumber: string;
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
  receiptDate: string;
  paymentMethod: string;
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

interface GeneratedReceiptProps {
  data: ReceiptData;
  companyLogo: string | null;
  calculations: {
    subtotal: number;
    discountAmount: number;
    taxAmount: number;
    total: number;
  };
}

const GeneratedReceipt = ({ data, companyLogo, calculations }: GeneratedReceiptProps) => {
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
          <h2 className="text-3xl font-bold text-blue-600 mb-2">RECEIPT</h2>
          <h2 className="text-xl font-semibold text-gray-700">#{data.receiptNumber}</h2>
          <div className="text-gray-600 space-y-0.5">
            <p>Date: {data.receiptDate}</p>
            <p>Payment Method: {data.paymentMethod}</p>
          </div>
        </div>
      </div>

      <div className="mb-8 border-b border-gray-200 pb-4">
        <h2 className="text-lg font-bold mb-2">CUSTOMER DETAILS:</h2>
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

      <h2 className="text-lg font-bold mb-4">Receipt Details</h2>
      <table className="w-full mb-8">
        <thead className="bg-blue-600 text-white">
          <tr>
            <th className="py-3 px-4 text-left">PRODUCT/SERVICE</th>
            <th className="py-3 px-4 text-left">DESCRIPTION</th>
            <th className="py-3 px-4 text-center">QTY</th>
            <th className="py-3 px-4 text-right">UNIT PRICE</th>
            <th className="py-3 px-4 text-right">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          {data.lineItems.map((item, index) => (
            <tr key={item.id} className={index % 2 === 0 ? 'bg-gray-50' : ''}>
              <td className="py-3 px-4 border-b text-sm">{item.name}</td>
              <td className="py-3 px-4 border-b text-sm">{item.description}</td>
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
            <span>Total Paid:</span>
            <span>${calculations.total.toFixed(2)}</span>
          </div>
        </div>
      </div>

      {data.clientMessage && (
        <div className="mt-8 text-gray-700">
          <p className="text-sm italic">{data.clientMessage}</p>
        </div>
      )}

      <div className="mt-10 pt-4 border-t border-gray-200">
        <p className="text-center text-gray-600">Thank you for your business!</p>
      </div>
    </div>
  );
};

export default function ReceiptGenerator({ preFilledData }: { preFilledData?: ReceiptData['preFilledData'] }) {
  const receiptRef = useRef<HTMLDivElement>(null);
  const formRef = useRef<HTMLFormElement>(null);
  const [errorMessage, setErrorMessage] = useState('');
  
  const generatedReceiptRef = useRef<HTMLDivElement>(null);
  const [companyLogo, setCompanyLogo] = useState<string | null>(null);
  const [receiptData, setReceiptData] = useState<ReceiptData>({
    receiptNumber: '1',
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
    receiptDate: new Date().toISOString().split('T')[0],
    paymentMethod: 'Credit Card',
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
    taxRate: 0,
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
    setReceiptData(prev => ({ ...prev, [name]: value }));
  };

  const handleDateChange = (name: string, value: string) => {
    setReceiptData(prev => ({ ...prev, [name]: value }));
  };

  const handleLineItemChange = (id: string, field: keyof LineItem, value: string | number) => {
    setReceiptData(prev => ({
      ...prev,
      lineItems: prev.lineItems.map(item => 
        item.id === id ? { ...item, [field]: value } : item
      )
    }));
  };

  const addLineItem = () => {
    setReceiptData(prev => ({
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
    setReceiptData(prev => ({
      ...prev,
      lineItems: prev.lineItems.filter(item => item.id !== id)
    }));
  };

  const handleDiscountTypeChange = (value: 'percentage' | 'amount') => {
    setReceiptData(prev => ({ ...prev, discountType: value }));
  };

  const calculations = {
    subtotal: receiptData.lineItems.reduce((sum, item) => sum + (item.quantity * item.unitCost), 0),
    get discountAmount() {
      const subtotal = this.subtotal;
      if (receiptData.discount === 0) return 0;
      return receiptData.discountType === 'percentage' 
        ? (Number(receiptData.discount) / 100) * subtotal
        : Number(receiptData.discount);
    },
    get taxAmount() {
      const subtotal = this.subtotal;
      const discountAmount = this.discountAmount;
      return (Number(receiptData.taxRate) / 100) * (subtotal - discountAmount);
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
    setReceiptData({
      receiptNumber: '',
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
      receiptDate: new Date().toISOString().split('T')[0],
      paymentMethod: 'Credit Card',
      lineItems: [],
      discount: 0,
      discountType: 'percentage',
      taxRate: 0,
      clientMessage: ''
    });
    setCompanyLogo(null);
    setErrorMessage('');
  };

  const generateReceipt = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!generatedReceiptRef.current) return;
    setErrorMessage('');
    if (!validateRequiredFields()) {
      return;
    }

    try {
      const element = generatedReceiptRef.current;
      const styledElement = inlineStyles(element);
      const opt = {
        margin: 10,
        filename: `receipt-${receiptData.receiptNumber}.pdf`,
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
    if (receiptData.lineItems.length === 0) {
      setErrorMessage('Please add at least one line item.');
      return false;
    }

    // Check for empty line items
    const emptyLineItems = receiptData.lineItems.filter(item => 
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
      <div id="templateform" ref={receiptRef} className="p-4 max-w-7xl mx-auto bg-[#F8F8F8] my-8" style={{
        scrollMarginTop: '80px' // Adjust this value based on your header height
      }}>
        <form ref={formRef} onSubmit={generateReceipt} noValidate>
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
                <h2 className="text-2xl font-bold">Receipt #</h2>
                <div className="mt-2">
                  <input 
                    value={receiptData.receiptNumber}
                    onChange={handleInputChange}
                    name="receiptNumber"
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
                  value={receiptData.companyName}
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
                  value={receiptData.companyEmail}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                  required
                />
                <input 
                  placeholder="Phone *" 
                  type="tel"
                  name="companyPhone"
                  value={receiptData.companyPhone}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                  required
                />
              </div>
              <input 
                placeholder="Company Address *" 
                name="companyAddress"
                value={receiptData.companyAddress}
                onChange={handleInputChange}
                className="w-full border rounded p-2"
                required
              />
              <div className="grid grid-cols-2 gap-4">
                <input 
                  placeholder="City *" 
                  name="companyCity"
                  value={receiptData.companyCity}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                  required
                />
                <input 
                  placeholder="Zip/Postal Code *" 
                  name="companyPostalCode"
                  value={receiptData.companyPostalCode}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                  required
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <input 
                  placeholder="State/Province *" 
                  name="companyState"
                  value={receiptData.companyState}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                  required
                />
                <select 
                  value={receiptData.companyCountry} 
                  onChange={handleInputChange}
                  name="companyCountry"
                  className="border rounded p-2"
                >
                  <option value="United States">United States</option>
                  <option value="Canada">Canada</option>
                  <option value="United Kingdom">United Kingdom</option>
                  <option value="Australia">Australia</option>
                  <option value="India">India</option>
                  {/* Add more countries as needed */}
                </select>
              </div>
            </div>
          </div>

          <div className="border rounded-lg p-6 bg-white">
            <h2 className="text-xl font-bold mb-4">Receipt Details</h2>
            <div className="grid gap-6">
              <div>
                <label className="block text-sm font-medium mb-2">Receipt Date *</label>
                <input
                  type="date"
                  value={receiptData.receiptDate}
                  onChange={(e) => handleDateChange('receiptDate', e.target.value)}
                  name="receiptDate"
                  className="w-full border rounded p-2"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">Payment Method *</label>
                <select
                  value={receiptData.paymentMethod}
                  onChange={handleInputChange}
                  name="paymentMethod"
                  className="w-full border rounded p-2"
                  required
                >
                  <option value="Credit Card">Credit Card</option>
                  <option value="Cash">Cash</option>
                  <option value="Debit Card">Debit Card</option>
                  <option value="Bank Transfer">Bank Transfer</option>
                  <option value="Check">Check</option>
                  <option value="PayPal">PayPal</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div className="mt-8">
          <div className="border rounded-lg p-6 bg-white">
            <h2 className="text-xl font-bold mb-4">Customer Information</h2>
            <div className="grid gap-4">
              <div className="grid grid-cols-2 gap-4">
                <input 
                  placeholder="Customer First Name *" 
                  name="clientFirstName"
                  value={receiptData.clientFirstName}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                  required
                />
                <input 
                  placeholder="Customer Last Name" 
                  name="clientLastName"
                  value={receiptData.clientLastName}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                />
              </div>
              <input 
                placeholder="Customer Address" 
                name="clientAddress"
                value={receiptData.clientAddress}
                onChange={handleInputChange}
                className="w-full border rounded p-2"
              />
              <div className="grid grid-cols-3 gap-4">
                <input 
                  placeholder="City" 
                  name="clientCity"
                  value={receiptData.clientCity}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                />
                <input 
                  placeholder="State/Province" 
                  name="clientState"
                  value={receiptData.clientState}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                />
                <input 
                  placeholder="Zip/Postal Code" 
                  name="clientPostalCode"
                  value={receiptData.clientPostalCode}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <input 
                  placeholder="Customer Email Address" 
                  type="email"
                  name="clientEmail"
                  value={receiptData.clientEmail}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                />
                <input 
                  placeholder="Customer Phone Number" 
                  type="tel"
                  name="clientPhone"
                  value={receiptData.clientPhone}
                  onChange={handleInputChange}
                  className="border rounded p-2"
                />
              </div>
            </div>
          </div>
        </div>

        <div className="mt-8 bg-white rounded-lg border p-6">
          <div className="grid grid-cols-12 gap-4 py-2 font-semibold">
            <div className="col-span-3">Product/Service</div>
            <div className="col-span-3">Description</div>
            <div className="col-span-2 text-center">Quantity</div>
            <div className="col-span-2 text-center">Unit Cost ($)</div>
            <div className="col-span-1 text-right">Total ($)</div>
            <div className="col-span-1"></div>
          </div>

          {receiptData.lineItems.map((item) => (
            <div key={item.id} className="grid grid-cols-12 gap-4 items-center py-2">
              <div className="col-span-3">
                <input 
                  type="text"
                  value={item.name}
                  onChange={(e) => handleLineItemChange(item.id, 'name', e.target.value)}
                  className="w-full border rounded p-2"
                  placeholder="Product/Service Name"
                />
              </div>
              <div className="col-span-3">
                <input 
                  type="text"
                  value={item.description}
                  onChange={(e) => handleLineItemChange(item.id, 'description', e.target.value)}
                  className="w-full border rounded p-2"
                  placeholder="Description"
                />
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
              <div className="col-span-1 font-semibold flex justify-end">
                ${(item.quantity * item.unitCost).toFixed(2)}
              </div>
              <div className="col-span-1 flex justify-center">
                <button
                  type="button"
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
              type="button"
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
              placeholder="Receipt Message (Optional)" 
              name="clientMessage"
              value={receiptData.clientMessage}
              onChange={handleInputChange}
              className="w-full h-full border rounded p-2 resize-none"
              rows={4}
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
                  value={receiptData.discount}
                  onChange={handleInputChange}
                  className="w-16 border rounded p-1 text-right mr-2"
                />
                <select
                  value={receiptData.discountType}
                  onChange={(e) => handleDiscountTypeChange(e.target.value as 'percentage' | 'amount')}
                  className="border rounded p-1 min-w-[40px]"
                >
                  <option value="amount">$</option>
                  <option value="percentage">%</option>
                </select>
                {receiptData.discount === 0 ? (
                  <button 
                    type="button"
                    className="ml-2 text-blue-600 hover:text-blue-800"
                    onClick={() => setReceiptData({...receiptData, discount: receiptData.discountType === 'percentage' ? 10 : 100})}
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
                  value={receiptData.taxRate}
                  onChange={handleInputChange}
                  className="w-16 border rounded p-1 text-right"
                />
                <span className="ml-8 w-24 text-right">${calculations.taxAmount.toFixed(2)}</span>
              </div>
            </div>
            
            <div className="flex justify-between pt-3 border-t border-gray-200">
              <span className="font-bold">Total Paid</span>
              <span className="font-bold text-xl">${calculations.total.toFixed(2)}</span>
            </div>

            <div className="mt-8">
              <button 
                type="submit"
                className="w-full bg-blue-600 hover:bg-blue-700 text-white rounded p-2"
              >
                Generate Free Receipt
              </button>
              {errorMessage && (
                <div className="text-red-500 mt-2">{errorMessage}</div>
              )}
            </div>
          </div>
        </div>
        </form>
      </div>

      <div className="hidden">
        <div ref={generatedReceiptRef}>
          <GeneratedReceipt 
            data={receiptData} 
            companyLogo={companyLogo} 
            calculations={calculations}
          />
        </div>
      </div>
    </>
  );
}