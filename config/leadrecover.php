<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Free trial length (days)
    |--------------------------------------------------------------------------
    */
    'trial_days' => (int) env('LEADRECOVER_TRIAL_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Subscription plans
    |--------------------------------------------------------------------------
    | Stripe price IDs are pulled from the environment so the same codebase
    | works across test/live Stripe accounts. `lead_limit` of null means
    | unlimited recovered leads per month.
    */
    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'price' => 49,
            'currency' => 'GBP',
            'stripe_price_id' => env('STRIPE_PRICE_STARTER'),
            'lead_limit' => 200,
            'features' => [
                'Missed-call SMS recovery',
                'Up to 200 recovered leads / month',
                'Lead dashboard & pipeline',
                'Automated 1h & 24h follow-ups',
                'Email support',
            ],
        ],
        'growth' => [
            'name' => 'Growth',
            'price' => 99,
            'currency' => 'GBP',
            'stripe_price_id' => env('STRIPE_PRICE_GROWTH'),
            'lead_limit' => 1000,
            'most_popular' => true,
            'features' => [
                'Everything in Starter',
                'WhatsApp recovery channel',
                'Up to 1,000 recovered leads / month',
                'Calendly & booking-link integration',
                'Revenue-recovered analytics',
                'Priority support',
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 199,
            'currency' => 'GBP',
            'stripe_price_id' => env('STRIPE_PRICE_PRO'),
            'lead_limit' => null,
            'features' => [
                'Everything in Growth',
                'Unlimited recovered leads',
                'Multiple team members',
                'Custom branding on messages',
                'Dedicated success manager',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Automated follow-up cadence
    |--------------------------------------------------------------------------
    | How long after the missed call each follow-up step fires. Values are in
    | minutes so they're easy to shorten for local testing.
    */
    'follow_ups' => [
        'first_reminder_minutes' => (int) env('LEADRECOVER_FOLLOWUP_1', 60),       // 1 hour
        'second_reminder_minutes' => (int) env('LEADRECOVER_FOLLOWUP_2', 60 * 24), // 24 hours
        'stale_after_minutes' => (int) env('LEADRECOVER_FOLLOWUP_STALE', 60 * 72), // 72 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Default messaging templates
    |--------------------------------------------------------------------------
    | Tokens: {{name}}, {{business}}, {{booking_link}}
    */
    'templates' => [
        'sms' => 'Sorry we missed your call at {{business}}! Click here to book an appointment: {{booking_link}}',
        'whatsapp' => 'Hi 👋 thanks for calling {{business}}. Sorry we missed you! How can we help you today? You can book here: {{booking_link}}',
        'first_reminder' => 'Just checking in from {{business}} — we’d still love to help. Book a time that suits you: {{booking_link}}',
        'second_reminder' => 'Last nudge from {{business}}! Tap here whenever you’re ready to book: {{booking_link}}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Average customer value per industry (£)
    |--------------------------------------------------------------------------
    | Used to estimate "revenue recovered" when a lead has no explicit value.
    */
    'industries' => [
        'dentist' => ['label' => 'Dentist', 'avg_value' => 220],
        'hair_salon' => ['label' => 'Hair Salon', 'avg_value' => 55],
        'barber' => ['label' => 'Barber', 'avg_value' => 25],
        'physiotherapist' => ['label' => 'Physiotherapist', 'avg_value' => 60],
        'aesthetic_clinic' => ['label' => 'Aesthetic Clinic', 'avg_value' => 350],
        'estate_agent' => ['label' => 'Estate Agent', 'avg_value' => 1500],
        'driving_instructor' => ['label' => 'Driving Instructor', 'avg_value' => 45],
        'other' => ['label' => 'Other', 'avg_value' => 100],
    ],

    /*
    |--------------------------------------------------------------------------
    | Lead statuses (single source of truth for UI + validation)
    |--------------------------------------------------------------------------
    */
    'statuses' => ['new', 'contacted', 'booked', 'converted', 'lost', 'stale'],
];
