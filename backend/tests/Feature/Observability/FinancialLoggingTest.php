<?php

namespace Tests\Feature\Observability;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FinancialLoggingTest extends TestCase
{
    use RefreshDatabase;

    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logPath = storage_path('logs/financial-'.now()->format('Y-m-d').'.log');
        File::delete($this->logPath);
    }

    protected function tearDown(): void
    {
        File::delete($this->logPath);

        parent::tearDown();
    }

    public function test_a_completed_deposit_is_recorded_in_the_financial_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/deposits', ['amount' => 5000]);

        $this->assertFileExists($this->logPath);

        $entry = json_decode(File::get($this->logPath), true);

        $this->assertSame('deposit.completed', $entry['message']);
        $this->assertSame('success', $entry['context']['result']);
        $this->assertSame($user->wallet->id, $entry['context']['wallet_id']);
        $this->assertSame(5000, $entry['context']['amount']);
        $this->assertArrayHasKey('transaction_id', $entry['context']);
    }

    public function test_a_rejected_transfer_is_recorded_in_the_financial_log(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $this->actingAs($sender)->postJson('/api/transfers', [
            'to_wallet_id' => $recipient->wallet->id,
            'amount' => 5000,
        ]);

        $this->assertFileExists($this->logPath);

        $entry = json_decode(File::get($this->logPath), true);

        $this->assertSame('transfer.rejected', $entry['message']);
        $this->assertSame('rejected', $entry['context']['result']);
        $this->assertSame('insufficient_balance', $entry['context']['reason']);
    }
}
