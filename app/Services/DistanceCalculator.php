<?php

namespace App\Services;

class DistanceCalculator
{
    /**
     * Calculate the distance between two points on the Earth's surface using the Haversine formula.
     *
     * @param float $lat1 Latitude of point 1
     * @param float $lon1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lon2 Longitude of point 2
     * @param string $unit (optional) Unit of measurement (default is 'km')
     * @return float Distance between the two points
     */
    public static function haversineDistance($lat1, $lon1, $lat2, $lon2, $unit = 'km')
    {
        $earthRadius = [
            'km' => 6371.0, // in kilometers
            'mi' => 3958.8, // in miles
            'nmi' => 3440.0, // in nautical miles
        ];

        // Convert latitude and longitude from degrees to radians
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        // Calculate differences
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        // Haversine formula
        $angle = 2 * asin(sqrt(
                pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) *
                pow(sin($lonDelta / 2), 2)
            ));

        // Calculate the distance
        $distance = $angle * $earthRadius[$unit];

        return $distance;
    }
}
