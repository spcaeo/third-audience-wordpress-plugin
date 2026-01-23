import { NextRequest, NextResponse } from "next/server";
import { revalidatePath, revalidateTag } from "next/cache";



function withBasePath(path: string): string {
  const basePath = (process.env.BASE_PATH || '').replace(/\/+$/, '');
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  return basePath ? `${basePath}${cleanPath}` : cleanPath;
}

export async function POST(request: NextRequest) {
  const responseData = await request.json();
  
  const slug = responseData.post_name;
  const posttype = responseData.post_type;
  const poststatus = responseData.post_status;
   
  if (posttype == 'page' && poststatus == 'publish') {
    revalidatePath(withBasePath(`/${slug}/`));
  } else if (posttype == 'post' && poststatus == 'publish') {
    revalidatePath(withBasePath(`/blog/${slug}/`));
    revalidatePath(withBasePath('/blog/'));
    revalidatePath(withBasePath('/author/*'));
    revalidatePath(withBasePath('/category/*'));
  } else {
    revalidateTag("wpchange");
  }

  revalidatePath(withBasePath('/sitemap*'));
  
  return NextResponse.json({ revalidated: true, now: Date.now() });
} 