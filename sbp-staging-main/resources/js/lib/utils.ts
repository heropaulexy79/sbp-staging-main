import { type ClassValue, clsx } from "clsx";
import { twMerge } from "tailwind-merge";
import { ref, onUnmounted } from "vue";

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * @tutorial https://www.jacobparis.com/content/file-image-thumbnails#avoiding-memory-leaks-with-useobjecturls
 */
export function useObjectURLs() {
    const mapRef = ref(new Map());

    onUnmounted(() => {
        for (const [, url] of mapRef.value) {
            URL.revokeObjectURL(url);
        }
        mapRef.value.clear();
    });

    function getFileUrl(file: File): string {
        if (!mapRef.value.has(file)) {
            const url = URL.createObjectURL(file);
            mapRef.value.set(file, url);
        }

        const url = mapRef.value.get(file);
        if (!url) {
            throw new Error("File URL not found");
        }

        return url;
    }

    return getFileUrl;
}

export function slugify(input: string, randomLength = 6) {
    const baseSlug = input
        .toLowerCase()
        .replace(/\s+/g, "-") // Replace spaces with -
        .replace(/[^\w-]+/g, "") // Remove non-word characters
        .replace(/--+/g, "-") // Replace multiple - with single -
        .replace(/^-+/, "") // Trim - from start of text
        .replace(/-+$/, ""); // Trim - from end of text

    // Generate random URL fragment
    const randomString = Math.random()
        .toString(36)
        .substring(2, randomLength + 2);

    // Combine base slug and random fragment
    return `${baseSlug}-${randomString}`;
}

export function getPublicProfileImage(email: string) {
    return ` https://unavatar.io/gravatar/${email}?ttl=1d&fallback=https://avatar.vercel.sh/37t?size=400`;
}

export async function getPublicProfileImageOld(email: string) {
    if (!email) return "";

    const emailHash = await digest(email, "md5");

    return `https://www.gravatar.com/avatar/${emailHash}`;
}

async function digest(message: string, algo = "SHA-1") {
    return Array.from(
        new Uint8Array(
            await crypto.subtle.digest(algo, new TextEncoder().encode(message)),
        ),
        (byte) => byte.toString(16).padStart(2, "0"),
    ).join("");
}
