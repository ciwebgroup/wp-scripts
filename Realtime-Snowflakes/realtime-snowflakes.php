<?php
/**
 * Plugin Name: Realtime Snowflakes
 * Description: Displays falling snowflakes in the footer if the temperature is below the defined threshold. Snowflake density increases with colder temperatures. Made with OpenAI.
 * Author: Chris Heney
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/** SETTINGS AND CONSTANTS */
define( 'WEATHER_API_KEY', '<YOUR-API-KEY>' ); // Your OpenWeatherMap API Key
define( 'WEATHER_API_ENDPOINT', 'https://api.openweathermap.org/data/2.5/weather' ); // OpenWeatherMap API URL
define( 'TEMPERATURE_MINIMUM', 40 ); // Minimum temperature to display snowflakes (in °F)
define( 'SNOWFLAKE_BASE_COUNT', 12 ); // Base number of snowflakes at the minimum temperature
define( 'SNOWFLAKE_DENSITY_MODIFIER', 2 ); // Degrees per additional snowflake
define( 'WEATHER_CACHE_KEY', 'current_weather' ); // Option key for caching weather
define( 'WEATHER_CACHE_DURATION', 86400 ); // Cache duration (24 hours in seconds)
define( 'DEFAULT_CITY', 'Chicago,US' ); // Default city if no location is detected

/** Fetch the website or user's city */
function get_website_or_user_city() {
    $city = get_option( 'site_city', DEFAULT_CITY ); // Default to predefined city

    // Try to determine location via IP
    if ( function_exists( 'geoip_detect2_get_info_from_current_ip' ) ) {
        $geo_data = geoip_detect2_get_info_from_current_ip();
        if ( ! empty( $geo_data->city->name ) && ! empty( $geo_data->country->isoCode ) ) {
            $city = $geo_data->city->name . ',' . $geo_data->country->isoCode;
        }
    } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $geo_api = "http://ip-api.com/json/{$ip_address}";
        $response = wp_remote_get( $geo_api );
        if ( ! is_wp_error( $response ) ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( isset( $data['city'] ) && isset( $data['countryCode'] ) ) {
                $city = $data['city'] . ',' . $data['countryCode'];
            }
        }
    }
    return $city;
}

/** Fetch temperature from OpenWeatherMap API with caching */
function get_cached_temperature() {
    $weather_data = get_option( WEATHER_CACHE_KEY );

    // Check if data exists and is still valid
    if ( $weather_data && isset( $weather_data['timestamp'] ) && ( time() - $weather_data['timestamp'] ) < WEATHER_CACHE_DURATION ) {
        return $weather_data['temp'];
    }

    // Fetch fresh data
    $city = get_website_or_user_city();
    $api_url = WEATHER_API_ENDPOINT . "?q={$city}&units=imperial&appid=" . WEATHER_API_KEY;

    $response = wp_remote_get( $api_url );
    if ( is_wp_error( $response ) ) {
        return TEMPERATURE_MINIMUM + 10; // Default fallback temp
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( isset( $data['main']['temp'] ) ) {
        $weather_data = [
            'temp' => (float) $data['main']['temp'],
            'location' => $city,
            'timestamp' => time(),
        ];
        update_option( WEATHER_CACHE_KEY, $weather_data );
        return $weather_data['temp'];
    }

    return TEMPERATURE_MINIMUM + 10; // Default fallback temp
}

/** Conditionally display snowflakes */
function add_snowflakes_to_footer() {
    $currentTemp = get_cached_temperature();

    // Do not display snowflakes if temperature is above the threshold
    if ( $currentTemp > TEMPERATURE_MINIMUM ) {
        return;
    }

    // Calculate snowflake density
    $densityModifier = max( 0, floor( ( TEMPERATURE_MINIMUM - $currentTemp ) / SNOWFLAKE_DENSITY_MODIFIER ) );
    $snowflakeCount = SNOWFLAKE_BASE_COUNT + $densityModifier;

    ?>
    <style> 
    /* customizable snowflake styling */
    .snowflake {
      color: #fff;
      font-size: 1em;
      font-family: Arial, sans-serif;
      text-shadow: 0 0 5px #000;
      position: fixed;
      top: -10%;
      z-index: 9999;
      animation-name: snowflakes-shake;
      animation-duration: 3s;
      animation-timing-function: ease-in-out;
      animation-iteration-count: infinite;
    }
    .snowflake .inner {
      animation-name: snowflakes-fall;
      animation-duration: 10s;
      animation-timing-function: linear;
      animation-iteration-count: infinite;
    }
    @keyframes snowflakes-fall {
      0% { transform: translateY(0); }
      100% { transform: translateY(110vh); }
    }
    @keyframes snowflakes-shake {
      0%, 100% { transform: translateX(0); }
      50% { transform: translateX(80px); }
    }
    </style>
    <div class="snowflakes" aria-hidden="true">
      <?php for ( $i = 0; $i < $snowflakeCount; $i++ ): ?>
        <div class="snowflake" style="left: <?php echo rand(1, 100); ?>%; animation-delay: <?php echo rand(0, 10); ?>s;">
          <div class="inner">❅</div>
        </div>
      <?php endfor; ?>
    </div>
    <?php
}

/** Register action only if conditions are met */
function register_snowflakes_action() {
    $currentTemp = get_cached_temperature();

    if ( $currentTemp <= TEMPERATURE_MINIMUM ) {
        add_action( 'wp_footer', 'add_snowflakes_to_footer' );
    }
}

add_action( 'init', 'register_snowflakes_action' );