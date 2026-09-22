<?php

namespace App\Services;


class DailyHealthTipService
{
    // General eating guidance: https://www.who.int/initiatives/behealthy/healthy-diet
    public const TIPS = [
        'Add a variety of vegetables and fruit to your meals throughout the day.',
        'Try whole grains, such as oats or brown rice, as part of a balanced diet.',
        'Include beans or lentils in your meals for more variety.',
        'Choose whole fruit more often than sugary snacks.',
        'Use less salt when cooking and explore herbs and spices for flavour.',
        'Choose water instead of sugary drinks; follow any fluid advice from your clinician.',
        'Enjoy a variety of foods rather than relying on the same few meals.',
        'Add a serving of vegetables to your usual lunch or dinner.',
        'Try a different seasonal fruit to bring variety to your day.',
        'Choose wholegrain bread when it is available.',
        'Make room for pulses, such as chickpeas, in your weekly meals.',
        'Compare food labels and choose options with less added sugar.',
        'Taste your food before adding extra salt at the table.',
        'Try fresh fruit as a simple dessert.',
        'Choose a variety of colourful vegetables when planning your meals.',
        'Swap a sugary drink for an unsweetened option.',
        'Look for lower-salt options when buying packaged foods.',
        'Include whole grains, vegetables and pulses in a varied diet.',
        'Enjoy sugary treats less often as part of a balanced eating pattern.',
        'Plan your next meal around a mix of vegetables, whole grains and a protein source.',
    ];

    public function current(): string
    {
        $previous = session('health_tip.previous');
        $tips = array_values(array_filter(self::TIPS, fn ($tip) => $tip !== $previous));
        $tip = $tips[random_int(0, count($tips) - 1)];
        session(['health_tip.previous' => $tip]);

        return $tip;
    }
}
