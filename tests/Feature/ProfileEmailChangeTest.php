<?php

namespace Tests\Feature;

use App\Mail\EmailChangeCode;
use App\Models\EmailChangeVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProfileEmailChangeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_profile_name_fields_are_limited_to_60_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('profile.update'), [
                'name' => str_repeat('И', 61),
                'last_name' => 'Петров',
                'gender' => 'male',
                'birth_date' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        $this->assertSame($user->name, $user->fresh()->name);

        $this->actingAs($user)
            ->postJson(route('profile.update'), [
                'name' => 'Анна',
                'last_name' => str_repeat('И', 61),
                'gender' => 'female',
                'birth_date' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['last_name']);
    }

    public function test_email_change_sends_code_without_changing_the_account_email(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $newEmail = 'new-address@example.com';

        $this->actingAs($user)
            ->from(route('profile'))
            ->post(route('profile.email.update'), ['email' => $newEmail])
            ->assertRedirect(route('profile'))
            ->assertSessionHas('email_change_step', 'code');

        $this->get(route('profile'))
            ->assertOk()
            ->assertSee('Введите код из email')
            ->assertSee('data-email-change-code-modal', false);

        $this->from(route('profile'))
            ->post(route('profile.email.update'), ['email' => $newEmail])
            ->assertRedirect(route('profile'))
            ->assertSessionHasErrors([
                'email' => 'Новый код можно запросить не чаще одного раза в 60 секунд.',
            ]);

        $this->assertSame($user->email, $user->fresh()->email);
        $this->assertDatabaseHas('email_change_verifications', [
            'user_id' => $user->id,
            'email' => $newEmail,
            'attempts' => 0,
        ]);
        Mail::assertSent(EmailChangeCode::class, function (EmailChangeCode $mail) use ($newEmail): bool {
            return $mail->hasTo($newEmail) && preg_match('/^\d{4}$/', $mail->code) === 1;
        });
        Mail::assertSent(EmailChangeCode::class, 1);
    }

    public function test_email_is_updated_and_marked_verified_only_after_correct_code(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $newEmail = 'verified-address@example.com';

        $this->actingAs($user)
            ->from(route('profile'))
            ->post(route('profile.email.update'), ['email' => $newEmail]);

        $code = null;
        Mail::assertSent(EmailChangeCode::class, function (EmailChangeCode $mail) use (&$code, $newEmail): bool {
            if (!$mail->hasTo($newEmail)) {
                return false;
            }

            $code = $mail->code;
            return true;
        });

        $this->post(route('profile.email.verify'), ['code' => $code])
            ->assertRedirect(route('profile'))
            ->assertSessionHas('email_change_success', true);

        $this->assertSame($newEmail, $user->fresh()->email);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing('email_change_verifications', ['user_id' => $user->id]);
    }

    public function test_duplicate_email_is_reported_in_the_email_modal_without_sending_code(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $existingUser = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile'))
            ->post(route('profile.email.update'), ['email' => $existingUser->email])
            ->assertRedirect(route('profile'))
            ->assertSessionHasErrors(['email' => 'Email уже занят']);

        Mail::assertNothingSent();
        $this->assertDatabaseMissing('email_change_verifications', ['user_id' => $user->id]);
    }

    public function test_code_format_is_validated_and_expired_codes_cannot_change_email(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $newEmail = 'expired-address@example.com';

        $this->actingAs($user)
            ->from(route('profile'))
            ->post(route('profile.email.update'), ['email' => $newEmail]);

        $code = null;
        Mail::assertSent(EmailChangeCode::class, function (EmailChangeCode $mail) use (&$code, $newEmail): bool {
            if (!$mail->hasTo($newEmail)) {
                return false;
            }

            $code = $mail->code;
            return true;
        });

        $this->from(route('profile'))
            ->post(route('profile.email.verify'), ['code' => '123'])
            ->assertRedirect(route('profile'))
            ->assertSessionHasErrors(['code' => 'Код должен состоять из 4 цифр.']);
        $this->assertSame(0, EmailChangeVerification::where('user_id', $user->id)->value('attempts'));

        EmailChangeVerification::where('user_id', $user->id)
            ->update(['expires_at' => now()->subSecond()]);

        $this->from(route('profile'))
            ->post(route('profile.email.verify'), ['code' => $code])
            ->assertRedirect(route('profile'))
            ->assertSessionHasErrors(['code' => 'Срок действия кода истёк. Запросите новый код.']);
        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_email_is_checked_again_when_the_code_is_verified(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $newEmail = 'claimed-after-request@example.com';

        $this->actingAs($user)
            ->from(route('profile'))
            ->post(route('profile.email.update'), ['email' => $newEmail]);

        $code = null;
        Mail::assertSent(EmailChangeCode::class, function (EmailChangeCode $mail) use (&$code, $newEmail): bool {
            if (!$mail->hasTo($newEmail)) {
                return false;
            }

            $code = $mail->code;
            return true;
        });
        User::factory()->create(['email' => $newEmail]);

        $this->from(route('profile'))
            ->post(route('profile.email.verify'), ['code' => $code])
            ->assertRedirect(route('profile'))
            ->assertSessionHasErrors(['email' => 'Email уже занят']);

        $this->assertSame($user->email, $user->fresh()->email);
        $this->assertDatabaseMissing('email_change_verifications', ['user_id' => $user->id]);
    }

    public function test_five_wrong_codes_exhaust_attempts_and_a_resend_resets_them(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $newEmail = 'retry-address@example.com';

        $this->actingAs($user)
            ->from(route('profile'))
            ->post(route('profile.email.update'), ['email' => $newEmail]);

        $code = null;
        Mail::assertSent(EmailChangeCode::class, function (EmailChangeCode $mail) use (&$code, $newEmail): bool {
            if (!$mail->hasTo($newEmail)) {
                return false;
            }

            $code = $mail->code;
            return true;
        });
        $wrongCode = $code === '0000' ? '0001' : '0000';

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = $this->from(route('profile'))
                ->post(route('profile.email.verify'), ['code' => $wrongCode]);
        }

        $response->assertRedirect(route('profile'))
            ->assertSessionHasErrors(['code' => 'Вы исчерпали лимит попыток верификации.']);
        $this->assertSame(5, EmailChangeVerification::where('user_id', $user->id)->value('attempts'));

        $this->from(route('profile'))
            ->post(route('profile.email.verify'), ['code' => $code])
            ->assertRedirect(route('profile'))
            ->assertSessionHasErrors(['code' => 'Вы исчерпали лимит попыток верификации.']);

        $verification = EmailChangeVerification::where('user_id', $user->id)->firstOrFail();
        $verification->update(['resend_available_at' => now()->subSecond()]);

        $this->from(route('profile'))
            ->post(route('profile.email.update'), ['email' => $newEmail])
            ->assertRedirect(route('profile'))
            ->assertSessionHas('email_change_step', 'code');

        $this->assertSame(0, $verification->fresh()->attempts);
        Mail::assertSent(EmailChangeCode::class, 2);
    }
}
