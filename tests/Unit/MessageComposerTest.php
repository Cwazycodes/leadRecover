<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Lead;
use App\Services\MessageComposer;
use Tests\TestCase;

class MessageComposerTest extends TestCase
{
    public function test_it_replaces_all_supported_tokens(): void
    {
        $business = new Business(['name' => 'Bright Smile', 'slug' => 'bright-smile', 'booking_type' => 'internal']);
        $lead = new Lead(['name' => 'Alex', 'phone' => '+447700900000']);

        $output = (new MessageComposer)->render(
            'Hi {{name}}, thanks for calling {{business}}. Book: {{booking_link}}',
            $business,
            $lead
        );

        $this->assertStringContainsString('Hi Alex', $output);
        $this->assertStringContainsString('thanks for calling Bright Smile', $output);
        $this->assertStringContainsString(route('book', 'bright-smile'), $output);
    }

    public function test_it_falls_back_to_a_friendly_name_when_lead_is_anonymous(): void
    {
        $business = new Business(['name' => 'Acme', 'slug' => 'acme', 'booking_type' => 'internal']);
        $lead = new Lead(['phone' => '+447700900000']);

        $output = (new MessageComposer)->render('Hi {{name}}!', $business, $lead);

        $this->assertSame('Hi there!', $output);
    }

    public function test_for_stage_uses_business_sms_template_for_initial_sms(): void
    {
        $business = new Business([
            'name' => 'Acme', 'slug' => 'acme', 'booking_type' => 'internal',
            'sms_template' => 'Custom {{business}} message',
        ]);
        $lead = new Lead(['phone' => '+447700900000']);

        $output = (new MessageComposer)->forStage('initial', $business, $lead, 'sms');

        $this->assertSame('Custom Acme message', $output);
    }
}
