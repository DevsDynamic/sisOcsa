<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\DynamicMailConfig;
use App\Services\SystemConfig;
use App\Services\TelegramAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class SystemSettingController extends Controller
{
    public function edit()
    {
        $values = collect([
            'osinergmin_environment' => SystemSetting::valueFor('osinergmin_environment', config('services.osinergmin.environment')),
            'ocsa_base_url' => SystemSetting::valueFor('ocsa_base_url', config('services.ocsa.base_url')),
            'osinergmin_base_url_development' => SystemSetting::valueFor('osinergmin_base_url_development', config('services.osinergmin.base_urls.development')),
            'osinergmin_base_url_production' => SystemSetting::valueFor('osinergmin_base_url_production', config('services.osinergmin.base_urls.production')),
            'osinergmin_token_development' => SystemSetting::valueFor('osinergmin_token_development', config('services.osinergmin.tokens.development')),
            'osinergmin_token_production' => SystemSetting::valueFor('osinergmin_token_production', config('services.osinergmin.tokens.production')),
        ])->merge(DynamicMailConfig::values())->merge(TelegramAlertService::values());

        $values['mail_password_configured'] = filled($values['mail_password']);
        $values['telegram_bot_token_configured'] = filled($values['telegram_bot_token']);
        $values->forget(['mail_password', 'telegram_bot_token']);

        return view('system-settings.edit', compact('values'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'osinergmin_environment' => ['required', 'in:development,production'],
            'ocsa_base_url' => ['required', 'url', 'starts_with:https://'],
            'osinergmin_base_url_development' => ['required', 'url', 'starts_with:https://'],
            'osinergmin_base_url_production' => ['required', 'url', 'starts_with:https://'],
            'osinergmin_token_development' => ['nullable', 'string', 'max:1000'],
            'osinergmin_token_production' => ['nullable', 'string', 'max:1000'],
            // Compatibilidad con formularios/versiones anteriores que enviaban
            // retransmisiones y SMTP en una sola solicitud.
            ...$this->mailRules(false),
        ]);
        $this->storeSettings($validated);

        return redirect()->route('system-settings.edit', ['section' => 'integrations'])
            ->with('status', 'Configuración de retransmisiones actualizada.');
    }

    public function updateMail(Request $request)
    {
        $validated = $request->validate($this->mailRules(true));
        if (blank($validated['mail_password'] ?? null)) {
            unset($validated['mail_password']);
        }
        $this->storeSettings($validated);

        return redirect()->route('system-settings.edit', ['section' => 'notifications'])
            ->with('mail_status', 'Configuración de correo guardada.');
    }

    public function testMail(Request $request)
    {
        $validated = $request->validate([...$this->mailRules(true), 'mail_test_recipient' => ['required', 'email:rfc', 'max:255']]);
        if (blank($validated['mail_password'] ?? null)) {
            $validated['mail_password'] = DynamicMailConfig::values()['mail_password'];
        }

        try {
            $values = DynamicMailConfig::apply($validated);
            Mail::mailer('smtp')->raw('El envío de correo de '.config('app.name').' quedó configurado correctamente.', function ($message) use ($validated, $values) {
                $message->to($validated['mail_test_recipient'])->from($values['mail_from_address'], $values['mail_from_name'])->subject('Prueba de correo - '.config('app.name'));
            });
            $recipient = $validated['mail_test_recipient'];
            unset($validated['mail_test_recipient']);
            $this->storeSettings($validated);

            return redirect()->route('system-settings.edit')
                ->with('active_settings_section', 'notifications')
                ->with('mail_status', "Correo de prueba enviado a {$recipient} y configuración SMTP guardada.");
        } catch (Throwable $exception) {
            Log::warning('Falló la prueba de configuración SMTP.', ['host' => $validated['mail_host'], 'port' => $validated['mail_port'], 'exception' => $exception->getMessage()]);

            return back()->withInput($request->except('mail_password'))
                ->withErrors(['mail_test' => 'No se pudo enviar. Revisa servidor, puerto, cifrado, usuario y contraseña. Detalle: '.mb_substr($exception->getMessage(), 0, 350)]);
        }
    }

    public function updateTelegram(Request $request)
    {
        $validated = $request->validate($this->telegramRules());
        $validated['telegram_enabled'] = $request->boolean('telegram_enabled') ? '1' : '0';
        if (blank($validated['telegram_bot_token'] ?? null)) {
            unset($validated['telegram_bot_token']);
        }
        $this->storeSettings($validated);

        return redirect()->route('system-settings.edit', ['section' => 'notifications'])
            ->with('telegram_status', 'Configuración de Telegram guardada.');
    }

    public function testTelegram(Request $request)
    {
        $validated = $request->validate($this->telegramRules());
        $token = $validated['telegram_bot_token'] ?: TelegramAlertService::values()['telegram_bot_token'];

        try {
            TelegramAlertService::send(
                '✅ <b>'.e(config('app.name')).'</b>'."\n".'Telegram quedó configurado correctamente.'."\n".'Ambiente: <b>'.e(strtoupper(SystemConfig::environment())).'</b>',
                $token,
                TelegramAlertService::chatIds($validated['telegram_chat_ids'])
            );
            $validated['telegram_enabled'] = $request->boolean('telegram_enabled') ? '1' : '0';
            if (blank($validated['telegram_bot_token'])) {
                unset($validated['telegram_bot_token']);
            }
            $this->storeSettings($validated);

            return redirect()->route('system-settings.edit', ['section' => 'notifications'])
                ->with('telegram_status', 'Mensaje de prueba enviado y configuración de Telegram guardada.');
        } catch (Throwable $exception) {
            Log::warning('Falló la prueba de Telegram.', ['exception' => $exception->getMessage()]);

            return back()->withInput($request->except('telegram_bot_token'))
                ->withErrors(['telegram_test' => 'No se pudo enviar por Telegram. '.$exception->getMessage()]);
        }
    }

    private function storeSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            if ((str_contains($key, '_token_') || in_array($key, ['mail_password', 'telegram_bot_token'], true)) && blank($value)) {
                continue;
            }
            $storedValue = str_contains($key, 'base_url') ? rtrim((string) $value, '/') : (string) $value;
            if (str_starts_with($key, 'osinergmin_base_url_')) {
                $storedValue = preg_replace('#/api-gps-ingesta(?:-batch)?(?:/api/v1/(?:trama|trama-batch))?/?$#i', '', $storedValue);
            }
            SystemSetting::updateOrCreate(['key' => $key], ['value' => $storedValue]);
        }
    }

    private function mailRules(bool $required): array
    {
        $presence = $required ? 'required' : 'sometimes';

        return [
            'mail_host' => [$presence, 'string', 'max:255'],
            'mail_port' => [$presence, 'integer', 'between:1,65535'],
            'mail_username' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mail_password' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'mail_encryption' => ['sometimes', 'nullable', Rule::in(['tls', 'ssl'])],
            'mail_from_address' => [$presence, 'email:rfc', 'max:255'],
            'mail_from_name' => [$presence, 'string', 'max:255'],
            'mail_alert_recipients' => ['sometimes', 'nullable', function (string $attribute, mixed $value, $fail) {
                foreach (array_filter(array_map('trim', preg_split('/[,;\r\n]+/', (string) $value))) as $email) {
                    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $fail("El destinatario {$email} no es un correo válido.");
                    }
                }
            }],
        ];
    }

    private function telegramRules(): array
    {
        return [
            'telegram_enabled' => ['sometimes', 'boolean'],
            'telegram_bot_token' => ['nullable', 'string', 'max:255'],
            'telegram_chat_ids' => ['required', 'string', 'max:2000', function (string $attribute, mixed $value, $fail) {
                foreach (TelegramAlertService::chatIds((string) $value) as $chatId) {
                    if (! preg_match('/^-?\d+$/', $chatId)) {
                        $fail("El Chat ID {$chatId} no tiene un formato válido.");
                    }
                }
            }],
        ];
    }
}
