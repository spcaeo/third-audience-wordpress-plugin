import { NextRequest, NextResponse } from "next/server";
import { redirect } from 'next/navigation'
import { revalidatePath } from "next/cache";
import { draftMode } from "next/headers";

export async function GET(request: NextRequest, response: NextResponse) {
    const queryString = new URLSearchParams(request.nextUrl.search);
    const previewId = queryString.get('preview_id');
    const preview = queryString.get('preview');
    const p = queryString.get('p');
    const path = queryString.get('path') || '/';
    
    // Ensure path starts with a slash
    const cleanPath = path.startsWith('/') ? path : `/${path}`;
    const url = new URL(process.env.NEXT_PUBLIC_FRONTEND_URL || '');
    // Note: Next.js redirect() automatically handles basePath from next.config.js
    const redirectUTL = new URL(cleanPath, url.origin);
    // console.log('mayur',redirectUTL);
    
    redirectUTL.searchParams.set('preview', preview || '');
    redirectUTL.searchParams.set('p', p || '');
    const draft = await draftMode()
    draft.enable()
    // revalidatePath(redirectUTL.pathname);
    redirect(redirectUTL.href);
}