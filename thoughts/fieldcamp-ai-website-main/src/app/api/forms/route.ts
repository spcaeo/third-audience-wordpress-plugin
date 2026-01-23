import { NextResponse } from 'next/server';

export async function POST(request: Request) {
  try {
    const formData = await request.formData();
    
    // Get form type from URL params or form data
    const url = new URL(request.url);
    const formType = url.searchParams.get('type') || formData.get('formType') || 'contact';
    
    // Get WordPress site URL from environment variables
    const wpSiteUrl = process.env.WORDPRESS_BASE_URL;
    if (!wpSiteUrl) {
      throw new Error('WordPress site URL is not configured');
    }

    // Create a new FormData object for WordPress submission
    const wpFormData = new FormData();
    
    // Handle different form types with specific field mappings
    switch (formType) {
      case 'demo':
        // Demo request form - simple email capture
        wpFormData.append('email', formData.get('email') as string || '');
        wpFormData.append('headache', `Demo Request - ${formData.get('message') || 'No message provided'}`);
        wpFormData.append('formType', formType);
        break;
        
      case 'lp-contact':
        // Landing page contact form
        wpFormData.append('fullName', formData.get('fullName') as string || '');
        wpFormData.append('email', formData.get('email') as string || '');
        wpFormData.append('phone', formData.get('phone') as string || '');
        
        // Combine additional fields into headache field
        const company = formData.get('companyName') as string;
        const teamSize = formData.get('teamSize') as string;
        const challenge = formData.get('biggestChallenge') as string;
        const adsByGroup = formData.get('adsByGroup') as string;
        const keyword = formData.get('keyword') as string;
        
        const lpMessage = `Company: ${company || 'N/A'} | Team Size: ${teamSize || 'N/A'} | Challenge: ${challenge || 'N/A'} | Ads Group: ${adsByGroup || 'N/A'} | Keyword: ${keyword || 'N/A'}`;
        wpFormData.append('headache', lpMessage);
        wpFormData.append('formType', formType);
        break;
        
      case 'data-drop':
        // Data drop form
        wpFormData.append('fullName', formData.get('fullName') as string || '');
        wpFormData.append('email', formData.get('email') as string || '');
        wpFormData.append('phone', formData.get('phone') as string || '');
        wpFormData.append('companySize', formData.get('companySize') as string || '');
        wpFormData.append('headache', formData.get('headache') as string || '');
        
        // Handle file upload if present
        const file = formData.get('file') as File;
        if (file) {
          wpFormData.append('file', file);
        }
        
        // Handle URL if present
        const url = formData.get('url') as string;
        if (url) {
          wpFormData.append('url', url);
        }
        wpFormData.append('formType', formType);
        break;
        
      default:
        // Default contact form - pass through all fields
        for (const [key, value] of formData.entries()) {
          if (key !== 'formType') {
            wpFormData.append(key, value);
          }
        }
        wpFormData.append('formType', formType);
        break;
    }

    // Forward the request to WordPress
    const response = await fetch(`${wpSiteUrl}/wp-json/form/v1/store-contact-form-data/`, {
      method: 'POST',
      body: wpFormData,
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      return NextResponse.json(
        { error: errorData.message || `Failed to submit ${formType} form` },
        { status: response.status }
      );
    }

    const data = await response.json();
    return NextResponse.json(data);
  } catch (error) {
    console.error('Error in form submission:', error);
    return NextResponse.json(
      { error: 'Internal server error' },
      { status: 500 }
    );
  }
}

export async function OPTIONS() {
  return new Response(null, {
    status: 204,
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'POST, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type',
    },
  });
}