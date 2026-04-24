<?php
add_shortcode('see_more', 'see_more_func');
add_shortcode('year', 'year_func');
add_shortcode('vmb_socials', 'vmb_socials_func');
add_shortcode('vmb_display_phone_number', 'vmb_display_phone_number_func');
add_shortcode('vmb_display_email', 'vmb_display_email_func');
add_shortcode('vmb_display_address', 'vmb_display_address_func');

// See More
function see_more_func($atts) {
	
	// Attributes
	$atts = shortcode_atts(
		array(
			'target_id' => '',
			'initial_text' => 'See More',
			'toggle_text' => 'See Less',
			'target_id' => '',  
		),
		$atts,
		'see_more'
	);
	
	return '<a class="see-more-btn" data-target_id="'.$atts['target_id'].'" data-initial_text="'.$atts['initial_text'].'" data-toggle_text="'.$atts['toggle_text'].'">See More <i class="fa-solid fa-chevron-down"></i></a>';
	
}


function year_func() {
    return date('Y');
}

function vmb_display_phone_number_func($atts) {
	$atts = shortcode_atts(
		array(
			'class' => '',
			'option' => 'phone_number',
		),
		$atts,
		'vmb_display_phone_number'
	);

	$phone_number = vmb_get_field($atts['option'], 'option');

	if($phone_number) {
		return '<a href="tel:'.$phone_number.'" class="dynamic-phone-number vmb-phone-number '.$atts['class'].'">'.$phone_number.'</a>';
	} else {
		return 'Phone number option not found';
	}
}

function vmb_display_email_func($atts) {
	$atts = shortcode_atts(
		array(
			'class' => '',
			'option' => 'email_address',
		),
		$atts,
		'vmb_display_email'
	);

	$email = vmb_get_field($atts['option'], 'option');

	if($email) {
		return '<a href="mailto:'.$email.'" class="vmb-email '.$atts['class'].'">'.$email.'</a>';
	} else {
		return 'Email option not found';
	}
}

function vmb_display_address_func($atts) {
	$atts = shortcode_atts(
		array(
			'class' => '',
			'option' => 'address',
		),
		$atts,
		'vmb_display_address'
	);

	$address = vmb_get_field($atts['option'], 'option');

	if($address) {
		return '<span class="vmb-address '.$atts['class'].'">'.wp_kses_post($address).'</span>';
	} else {
		return 'Address option not found';
	}
}

function vmb_socials_func($atts) {

	$atts = shortcode_atts(
		array(
			'class' => '',
		),
		$atts,
		'vmb_socials'
	);
	
	$socials = vmb_get_field('social_media', 'option');
	$output = '';

	if(!empty($socials)) {
		foreach($socials as $social) {
			$output .= '<a href="'.$social['url'].'" target="_blank"><i class="fa-brands '.$social['icon'].'"></i></a>';
		}
	}

	return '<div class="vmb-socials '.$atts['class'].'">'.$output.'</div>';
}
