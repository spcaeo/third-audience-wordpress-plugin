import Link from "next/link";

export default async function Custom404() {
    return (<>
        <section className="flex items-center h-screen bg-gray-100">
            <div className="container mx-auto p-4">
                <div className="flex flex-col items-center">
                    <h1 className="text-6xl font-bold text-black">404</h1>
                    <p className="text-2xl font-medium text-gray-600">Page not found</p>
                    <Link href="/">
                        <p className="mt-6 bg-black hover:bg-black-700  text-white font-bold py-2 px-4 rounded-full">Back to Home</p>
                    </Link>
                </div>
            </div>
        </section>
    </>);
};

export async function generateMetadata() {
    const origin = process.env.NEXT_PUBLIC_BASE_URL;
    const ENV_TYPE = process.env.ENV_TYPE;

    return {
        title: "404 Page not found",
        description: "404 Page not found",
        robots: "noindex, nofollow",
    };
}
