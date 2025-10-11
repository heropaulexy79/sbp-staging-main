export function generateId(length = 10) {
    // Define the character pool for the ID
    const characters =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    // Create an empty string to store the ID
    let result = "";

    // Loop for the desired length of the ID
    for (let i = 0; i < length; i++) {
        // Generate a random index within the character pool
        const randomIndex = Math.floor(Math.random() * characters.length);

        // Extract the character at the random index and append it to the ID
        result += characters.charAt(randomIndex);
    }

    // Return the generated ID
    return result;
}
