<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_can_request_otp_via_email(): void
    {
        Mail::fake();

        $response = $this->post('/forgot', [
            'username' => 'guru1',
            'channel' => 'email',
        ]);

        $response->assertRedirect('/verify-otp');
        $response->assertSessionHas('reset_email', 'guru1@rapi-sekolah.local');
        $response->assertSessionHas('dev_otp');

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'guru1@rapi-sekolah.local',
        ]);
    }

    public function test_otp_is_strictly_sent_to_registered_database_email(): void
    {
        Mail::fake();

        $response = $this->post('/forgot', [
            'username' => 'guru1',
            'channel' => 'email',
            'custom_email' => 'userpribadi@gmail.com',
        ]);

        $response->assertRedirect('/verify-otp');
        $response->assertSessionHas('reset_target_email', 'guru1@rapi-sekolah.local');
        Mail::assertSent(ResetPasswordOtpMail::class, function ($mail) {
            return $mail->hasTo('guru1@rapi-sekolah.local');
        });
    }

    public function test_user_can_request_otp_via_whatsapp_registered_phone(): void
    {
        $response = $this->post('/forgot', [
            'username' => 'guru1',
            'channel' => 'whatsapp',
            'custom_phone' => '08987654321', // Diabaikan karena sistem wajib menggunakan nomor terdaftar di database
        ]);

        $response->assertRedirect('/verify-otp');
        $response->assertSessionHas('reset_target_phone', '62895611029000');
        $response->assertSessionHas('wa_url');
        $this->assertStringContainsString('62895611029000', session('wa_url'));
    }

    public function test_user_can_verify_otp_and_reset_password(): void
    {
        $user = User::where('username', 'guru1')->firstOrFail();
        $otp = '654321';

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', $otp),
            'created_at' => now(),
        ]);

        $verifyResponse = $this->withSession(['reset_email' => $user->email])
            ->post('/verify-otp', ['otp' => $otp]);

        $verifyResponse->assertRedirect();
        $token = session('reset_verified_token');
        $this->assertNotEmpty($token);

        // Reset password
        $resetResponse = $this->post('/reset', [
            'token' => $token,
            'password' => 'passwordbaru123',
            'password2' => 'passwordbaru123',
        ]);

        $resetResponse->assertRedirect('/login');

        // Pastikan password baru bisa dipakai
        $user->refresh();
        $this->assertTrue(Hash::check('passwordbaru123', $user->password));
    }
}
