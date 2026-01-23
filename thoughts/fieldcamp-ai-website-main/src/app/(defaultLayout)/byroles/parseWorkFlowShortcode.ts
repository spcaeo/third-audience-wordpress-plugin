export interface FeatureItem {
    title: string;
    description: string;
}

export interface RoleItem {
    name: string;
    features: FeatureItem[];
}

export interface WorkFlowSwiperData {
    title?: string;
    subtitle?: string;
    buttonText?: string;
    buttonLink?: string;
    roles?: RoleItem[];
    hasShortcode: boolean;
}

/**
 * Parse workFlowSwiper shortcode from HTML content
 * Supports formats:
 * - [workFlowSwiper] - simple shortcode, uses default values
 * - [workFlowSwiper title="..." subtitle="..." buttonText="..." buttonLink="..."] - with attributes
 * - [workFlowSwiper title="..." logos='[{"name":"...","icon":"...","features":[...]}]'] - with JSON logos data
 */
export function parseWorkFlowSwiperShortcode(content: string): WorkFlowSwiperData {
    if (!content) {
        return { hasShortcode: false };
    }

    // Decode HTML entities that WordPress might add
    let decodedContent = content
        .replace(/&#91;/g, '[')
        .replace(/&#93;/g, ']')
        .replace(/&lbrack;/g, '[')
        .replace(/&rbrack;/g, ']')
        .replace(/&quot;/g, '"')
        .replace(/&#8220;/g, '"')
        .replace(/&#8221;/g, '"')
        .replace(/&#8217;/g, "'")
        .replace(/&amp;/g, '&');

    // Match shortcode with or without attributes
    const shortcodeRegex = /\[workFlowSwiper([^\]]*)\]/i;
    const match = decodedContent.match(shortcodeRegex);

    if (!match) {
        return { hasShortcode: false };
    }

    const attributesString = match[1]?.trim() || '';

    if (!attributesString) {
        // Simple shortcode without attributes
        return { hasShortcode: true };
    }

    // Parse attributes
    const result: WorkFlowSwiperData = { hasShortcode: true };

    // Parse simple string attributes (title, subtitle, buttonText, buttonLink)
    const titleMatch = attributesString.match(/title=["']([^"']+)["']/);
    if (titleMatch) {
        result.title = titleMatch[1];
    }

    const subtitleMatch = attributesString.match(/subtitle=["']([^"']+)["']/);
    if (subtitleMatch) {
        result.subtitle = subtitleMatch[1];
    }

    const buttonTextMatch = attributesString.match(/buttonText=["']([^"']+)["']/);
    if (buttonTextMatch) {
        result.buttonText = buttonTextMatch[1];
    }

    const buttonLinkMatch = attributesString.match(/buttonLink=["']([^"']+)["']/);
    if (buttonLinkMatch) {
        result.buttonLink = buttonLinkMatch[1];
    }

    // Parse roles JSON attribute (can be complex)
    // Look for roles='[...]' or roles="[...]"
    const rolesMatch = attributesString.match(/roles='(\[[\s\S]*?\])'/);
    const rolesMatchDouble = attributesString.match(/roles="(\[[\s\S]*?\])"/);

    const rolesJson = rolesMatch?.[1] || rolesMatchDouble?.[1];
    if (rolesJson) {
        try {
            // Decode HTML entities if present
            const decodedJson = rolesJson
                .replace(/&quot;/g, '"')
                .replace(/&#39;/g, "'")
                .replace(/&amp;/g, '&')
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>');
            result.roles = JSON.parse(decodedJson);
        } catch (e) {
            console.error('Failed to parse roles JSON:', e);
        }
    }

    return result;
}

/**
 * Check if content contains workFlowSwiper shortcode
 */
export function hasWorkFlowSwiperShortcode(content: string): boolean {
    if (!content) return false;

    // Decode HTML entities that WordPress might add
    const decodedContent = content
        .replace(/&#91;/g, '[')
        .replace(/&#93;/g, ']')
        .replace(/&lbrack;/g, '[')
        .replace(/&rbrack;/g, ']');

    return /\[workFlowSwiper[^\]]*\]/i.test(decodedContent);
}
