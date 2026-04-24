jQuery(document).ready(function ($) {
    $('.vmb-faq-accordion .faq-question').on('click', function () {
        const $item = $(this).closest('.faq-item');
        const $answer = $item.find('.faq-answer');
        const $icon = $(this).find('.faq-toggle-icon');

        // Close all others
        $('.vmb-faq-accordion .faq-answer').not($answer).slideUp();
        $('.vmb-faq-accordion .faq-toggle-icon').not($icon).text('+');

        // Toggle current
        $answer.slideToggle(200, function () {
            $icon.text($answer.is(':visible') ? '−' : '+');
        });
    });
});