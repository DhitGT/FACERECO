const apiUrl = `https://wttr.in/bantargebang?format=%C|%t|%w|%h|%P`;

function translateCondition(condition) {
    const translations = {
        'clear': 'Cerah',
        'cloudy': 'Berawan',
        'partly cloudy': 'Sebagian Berawan',
        'rain': 'Hujan',
        'light rain': 'Hujan Ringan',
        'moderate rain': 'Hujan Sedang',
        'heavy rain': 'Hujan Lebat',
        'thunderstorm': 'Badai Petir',
        'snow': 'Salju',
        'fog': 'Kabut',
        'mist': 'Kabut Ringan',
        'drizzle': 'Gerimis',
        'patchy rain': 'Hujan Gerimis',
        'patchy light rain': 'Hujan Ringan Gerimis',
    };

    // Return the translated condition, defaulting to the original if not found
    return translations[condition.toLowerCase()] || condition;
}

// Function to get the appropriate weather icon based on the condition
function getWeatherIcon(condition) {
    const conditionLower = condition.toLowerCase();
    if (conditionLower.includes('rain')) {
        return '/images/lightrain.svg'; // Rainy icon
    } else if (conditionLower.includes('heavy rain')) {
        return '/images/heavyrain.svg'; // Clear icon
    } else if (conditionLower.includes('clear')) {
        return '/images/sun.svg'; // Clear icon
    } else if (conditionLower.includes('cloud')) {
        return '/images/cloud.svg'; // Cloudy icon
    } else if (conditionLower.includes('storm')) {
        return '/images/thunder.svg'; // Stormy icon
    } else {
        return '/images/sun.svg'; // Default icon
    }
}

async function fetchWeather() {
    try {
        const response = await fetch(apiUrl);
        const data = await response.text();
        const [condition, temperature, wind, humidity, probability] = data.split('|');

        const translatedCondition = translateCondition(condition.trim());


        // Update the DOM with the fetched data
        document.getElementById('condition').textContent = translatedCondition;
        document.getElementById('temperature').textContent = temperature.trim();
        document.getElementById('wind').textContent = wind.trim();
        document.getElementById('humidity').textContent = humidity.trim();
        document.getElementById('probability').textContent = probability.trim();

        // Set the weather icon based on the condition
        const iconUrl = getWeatherIcon(condition.trim());
        document.getElementById('weather-icon').src = iconUrl;

    } catch (error) {
        console.error('Error fetching weather data:', error);
        document.getElementById('weather').innerHTML = '<p class="text-red-500">Error fetching weather data.</p>';
    }
}

// Call the function to fetch weather data
fetchWeather();