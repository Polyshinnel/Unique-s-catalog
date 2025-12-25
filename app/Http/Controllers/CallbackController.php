<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CallbackController extends Controller
{
    /**
     * Отправка сообщения в Telegram из формы обратного звонка
     */
    public function sendCallback(Request $request)
    {
        // Валидация телефона
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!$this->validateRussianPhone($value)) {
                    $fail('Некорректный номер телефона для РФ.');
                }
            }],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('phone')
            ], 422);
        }

        $phone = $request->input('phone');
        $token = env('TELEGRAM_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        // Проверка наличия токена и chat ID
        if (empty($token) || empty($chatId)) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка конфигурации. Обратитесь к администратору.'
            ], 500);
        }

        // Формируем сообщение
        $message = "Новое сообщение с каталога Юник С.\nТелефон - {$phone}";

        // Отправляем сообщение в Telegram
        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Сообщение успешно отправлено!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при отправке сообщения. Попробуйте позже.'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отправке сообщения. Попробуйте позже.'
            ], 500);
        }
    }

    /**
     * Валидация российского номера телефона
     * Поддерживает форматы:
     * - +7XXXXXXXXXX
     * - 8XXXXXXXXXX
     * - 7XXXXXXXXXX
     * - +7 (XXX) XXX-XX-XX
     * - 8 (XXX) XXX-XX-XX
     * - и другие варианты с пробелами, скобками, дефисами
     */
    private function validateRussianPhone($phone)
    {
        // Удаляем все символы кроме цифр и +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);

        // Проверяем, что номер начинается с +7, 7 или 8
        if (!preg_match('/^(\+?7|8)/', $cleaned)) {
            return false;
        }

        // Заменяем 8 на 7 для единообразия
        if (strpos($cleaned, '8') === 0) {
            $cleaned = '7' . substr($cleaned, 1);
        }

        // Удаляем + если есть
        $cleaned = ltrim($cleaned, '+');

        // Проверяем, что номер состоит из 11 цифр (7 + 10 цифр)
        if (strlen($cleaned) !== 11 || $cleaned[0] !== '7') {
            return false;
        }

        // Проверяем, что вторая цифра (код оператора) от 3 до 9
        $operatorCode = (int)$cleaned[1];
        if ($operatorCode < 3 || $operatorCode > 9) {
            return false;
        }

        return true;
    }
}

