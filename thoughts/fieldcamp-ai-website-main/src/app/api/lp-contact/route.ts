import { NextResponse } from 'next/server';

export async function POST(request: Request) {
  try {
    const formData = await request.formData();
    
    // Get WordPress site URL from environment variables
    const wpSiteUrl = process.env.WORDPRESS_BASE_URL;
    if (!wpSiteUrl) {
      throw new Error('WordPress site URL is not configured');
    }

    // Forward the request to WordPress using the same field names as the existing contact form
    // This ensures no changes are needed on the WordPress side

    // Forward the request to WordPress
    const response = await fetch(`${wpSiteUrl}/wp-json/form/v1/store-contact-form-data/`, {
      method: 'POST',
      body: formData,
      // Let the browser set the content-type with boundary
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      return NextResponse.json(
        { error: errorData.message || 'Failed to submit form' },
        { status: response.status }
      );
    }

    const data = await response.json();
    return NextResponse.json(data);
  } catch (error) {
    console.error('Error in LP contact form submission:', error);
    return NextResponse.json(
      { error: 'Internal server error' },
      { status: 500 }
    );
  }
}

// Add OPTIONS method for CORS preflight
// This is necessary if your frontend and backend are on different domains
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