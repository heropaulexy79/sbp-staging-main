export function errorBagToString(error: { [key: string]: string }) {
    return Object.values(error).join(", ");
}
