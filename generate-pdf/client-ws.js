const axios = require('axios');

// URL to hit
const url = "http://127.0.0.1:3334/status_device";

// Interval (in milliseconds) between requests (5 seconds = 5000 ms)
const interval = 5000; // Change to your preferred interval

// Function to make the GET request
const makeRequest = async () => {
    try {
        const response = await axios.get(url);
        console.log(response);

        if (response.status === 200) {
            console.log("Successfully hit the URL.");
        } else {
            console.log(`Failed to hit the URL. Status Code: ${response.status}`);
        }
    } catch (error) {
        console.log(`Error making request: ${error.message}`);
    }
};

// Set an interval to hit the URL at the specified time
setInterval(makeRequest, interval);
