import { NextRequest, NextResponse } from "next/server";
import { getUserBySlug } from "@/lib/api";
export const dynamic = 'force-dynamic';

export async function GET(request: NextRequest) {
  try {

    const url = new URL(request.url);
    const endCursor = url.searchParams.get('endCursor');
    const userslug = url.searchParams.get('user');
    const items = url.searchParams.get('items');
    const data = await getUserBySlug(userslug, endCursor, items ? parseInt(items) : 9);

    return NextResponse.json({
      status: 200,
      data: data,
    })
  } catch (error) {
    return NextResponse.json({
      status: 404,
      error: error,
      message: "There was some issue with form, Please try after some time."
    })
  }
}
