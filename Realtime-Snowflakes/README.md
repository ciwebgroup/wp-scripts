Here's the **fancy README** for your plugin **"Realtime Snowflakes"**! 🎉

---

# Realtime Snowflakes 🎄❄️

**Plugin Name:** Realtime Snowflakes  
**Description:** Bring the magic of winter to your WordPress website with dynamically falling snowflakes that respond to **live weather conditions**!  
**Version:** 1.0  
**Author:** Chris Heney
**Development Time:** Supercharged with the power of **OpenAI** in record time (but still sprinkled with love and care)!  

---

## ❄️ **What is Realtime Snowflakes?**
"Realtime Snowflakes" is a smart and visually delightful plugin that dynamically adds falling snowflakes to your website's footer based on **real-world weather data**. 

- **Configurable & Efficient**: Displays snowflakes *only* if the current temperature in your chosen city is below a defined threshold.  
- **Dynamic Snow Density**: The colder it gets, the denser the snowflakes fall. Stay cozy as your website adjusts in real-time!  
- **Global Awareness**: Automatically determines your website's city, or even fetches the location of your visitors using their IP address.  
- **Weather-Cached**: Optimized to cache weather data for 24 hours to minimize API usage, ensuring scalability across 1,000+ sites!

---

## 🌟 **Features**
1. **Live Weather Integration**:  
   Fetches real-time temperature using the [OpenWeatherMap API](https://openweathermap.org/api).

2. **Conditional Snowflake Display**:  
   Snowflakes appear **only** when the temperature falls below your defined threshold.  
   - Default: **40°F**.  

3. **Dynamic Snowflake Density**:  
   Snowflake count increases dynamically as temperatures drop.  
   - **Base Snowflakes:** 12 at 40°F.  
   - **Density Increase:** +1 snowflake for every 2°F colder.

4. **Automatic City Detection**:  
   - Default to **Chicago, US**.  
   - Pulls the site's location if set via WordPress options.  
   - Determines visitor location via IP for a personalized experience.

5. **API Caching**:  
   - Weather data is cached for **24 hours** in `wp_options` to optimize performance and minimize API usage.

6. **Highly Configurable**:  
   All core settings are defined at the top of the script:
   - `WEATHER_API_KEY` → Your OpenWeatherMap API key.  
   - `WEATHER_API_ENDPOINT` → API URL.  
   - `TEMPERATURE_MINIMUM` → Temperature threshold for snowflakes.  
   - `SNOWFLAKE_BASE_COUNT` → Base density of snowflakes.  
   - `SNOWFLAKE_DENSITY_MODIFIER` → Density scaling factor.  
   - `WEATHER_CACHE_DURATION` → Cache duration (24 hours).  
   - `DEFAULT_CITY` → Fallback city.


---


## ⚙️ **How It Works**
1. **Live Weather Fetch**:  
   Realtime Snowflakes queries the OpenWeatherMap API for the current temperature in your city.

2. **Intelligent Caching**:  
   - The API response is cached for 24 hours under the key `current_weather`.  
   - Avoids excessive API calls and ensures efficiency across all websites.

3. **Temperature Check**:  
   - If the temperature is **below the threshold** → Snowflakes appear!  
   - Snowflake count scales dynamically based on how cold it is.

4. **Automatic Location**:  
   - Checks your site's city from `site_city` in `wp_options`.  
   - If unavailable, it tries to determine the visitor's city via IP.

---

## 📦 **Installation**
1. Copy the plugin file `realtime-snowflakes.php` into your `wp-content/mu-plugins` directory.
2. Set your **OpenWeatherMap API key** at the top of the file:
   ```php
   define( 'WEATHER_API_KEY', 'YOUR_API_KEY_HERE' );
   ```
3. Optionally set a **default city** for your WordPress site:
   ```sql
   INSERT INTO wp_options (option_name, option_value) VALUES ('site_city', 'New York,US');
   ```
4. Enjoy the winter magic as snowflakes gently fall on your site when it gets chilly! ❄️

---

## 🛠️ **Configuration Options**
All the core settings can be found and customized at the top of the script:

| Setting                   | Description                               | Default Value          |
|---------------------------|-------------------------------------------|------------------------|
| `WEATHER_API_KEY`         | Your OpenWeatherMap API Key               | `YOUR_API_KEY_HERE`    |
| `WEATHER_API_ENDPOINT`    | API endpoint for fetching weather data    | OpenWeatherMap URL     |
| `TEMPERATURE_MINIMUM`     | Minimum temp for snowflakes to appear     | `40`                  |
| `SNOWFLAKE_BASE_COUNT`    | Base number of snowflakes                 | `12`                  |
| `SNOWFLAKE_DENSITY_MODIFIER` | Degrees per extra snowflake            | `2`                   |
| `WEATHER_CACHE_DURATION`  | Duration for caching weather data         | `86400` (24 hours)    |
| `DEFAULT_CITY`            | Fallback city                             | `Chicago,US`          |

---

## 🌍 **Why Realtime Snowflakes?**
- **Highly Scalable**: Perfect for multi-site WordPress networks with thousands of sites.  
- **API Efficiency**: Caches results to keep API usage low and costs under control.  
- **User Delight**: Adds a seasonal, dynamic visual effect that adapts to real-world conditions.

---

## 💡 **Fun Fact**
This plugin was carefully crafted with the help of **OpenAI** in record time!  
The development process was powered by creativity, innovation, and an extra sprinkle of ❄️ magic.

---

## 🎉 **Credits**
- Built with ❤️ by **Chris Heney**.  
- Weather data provided by **[OpenWeatherMap](https://openweathermap.org/api)**.  
- Powered by **OpenAI** for making development a breeze!  

---

Enjoy your **dynamic snowfall**! Stay warm and happy coding. ⛄❄️  

---

##  TODO 

Add "city detection" by scanning the nav menu using OpenAI's API if the city doesn't exist (just like we do for schema).

---