<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\DynamicMailConfig;
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
        ])->merge(DynamicMailConfig::values());

        $values['mail_password_configured'] = filled($values['mail_password']);
        $values->forget('mail_password');

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
            ...$this->mailRules(false),
        ]);

        foreach ($validated as $key => $value) {
            if ((str_contains($key, '_token_') || $key === 'mail_password') && blank($value)) {
                continue;
            }
            $storedValue = str_contains($key, 'base_url') ? rtrim((string) $value, '/') : (string) $value;
            SystemSetting::updateOrCreate(['key' => $key], ['value' => $storedValue]);
        }

        return back()->with('status', 'Configuración actualizada. Los siguientes procesos usarán estos valores.');
    }

    public function testMail(Request $request)
    {
        $validated = $request->validate([
            ...$this->mailRules(true),
            'mail_test_recipient' => ['required', 'email:rfc', 'max:255'],
        ]);

        if (blank($validated['mail_password'] ?? null)) {
            $validated['mail_password'] = DynamicMailConfig::values()['mail_password'];
        }

        try {
            $values = DynamicMailConfig::apply($validated);
            Mail::mailer('smtp')->raw(
                'El envío de correo de '.config('app.name').' quedó configurado correctamente.',
                function ($message) use ($validated, $values) {
                    $message->to($validated['mail_test_recipient'])
                        ->from($values['mail_from_address'], $values['mail_from_name'])
                        ->subject('Prueba de correo - '.config('app.name'));
                }
            );

            return back()->withInput($request->except('mail_password'))
                ->with('mail_status', 'Correo de prueba enviado correctamente a '.$validated['mail_test_recipient'].'.');
        } catch (Throwable $exception) {
            Log::warning('Falló la prueba de configuración SMTP.', [
                'host' => $validated['mail_host'],
                'port' => $validated['mail_port'],
                'exception' => $exception->getMessage(),
            ]);

            return back()->withInput($request->except('mail_password'))
                ->withErrors(['mail_test' => 'No se pudo enviar. Revisa servidor, puerto, cifrado, usuario y contraseña. Detalle: '.mb_substr($exception->getMessage(), 0, 350)]);
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
                $emails = array_filter(array_map('trim', preg_split('/[,;\r\n]+/', (string) $value)));
                foreach ($emails as $email) {
                    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $fail("El destinatario {$email} no es un correo válido.");
                    }
                }
            }],
        ];
    }
}
