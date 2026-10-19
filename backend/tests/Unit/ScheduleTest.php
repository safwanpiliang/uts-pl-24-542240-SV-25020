<?php

namespace Tests\Unit;

use App\Models\Schedule;
use PHPUnit\Framework\TestCase;

class ScheduleTest extends TestCase
{
    public function test_status_list_contains_expected_values(): void
    {
        $this->assertSame(['planned', 'done', 'cancelled'], Schedule::STATUSES);
    }
}
