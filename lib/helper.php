<?php

/**
 * Function to print star rating using Bootstrap.
 *
 * @param int $rating The rating number (min = 0, max = 5).
 * @return string HTML content for the star rating.
 */
function print_star_rating($rating) {
    $rating = max(0, min(5, $rating)); // Ensure rating is between 0 and 5
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5 ? 1 : 0;
    $empty_stars = 5 - $full_stars - $half_star;

    $output = '<div class="star-rating">';
    
    for ($i = 0; $i < 5; $i++) {
        if ($i < $full_stars) {
            $output .= '<i class="fa fa-star"></i>';
        } elseif ($i == $full_stars && $half_star) {
            $output .= '<i class="fa fa-star-half-o"></i>';
        } else {
            $output .= '<i class="fa fa-star empty"></i>';
        }
    }
    
    $output .= '</div>';

    return $output;
}


/**
 * Function to convert a string into a URL-friendly slug.
 *
 * This function takes a string and converts it to a lowercase slug by:
 * - Converting the string to lowercase
 * - Removing special characters
 * - Replacing spaces and other non-alphanumeric characters with hyphens
 * - Removing duplicate hyphens and trimming hyphens from start and end
 *
 * @param string $text The text to convert to a slug.
 * @return string The slugified version of the input text.
 */
function vmbt_slugify($text) {
    // Convert to lowercase
    $text = strtolower($text);
    
    // Remove accents/diacritics
    $text = remove_accents($text);
    
    // Replace non-alphanumeric characters with hyphens
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    
    // Remove duplicate hyphens
    $text = preg_replace('/-+/', '-', $text);
    
    // Trim hyphens from beginning and end
    $text = trim($text, '-');
    
    return $text;
}
