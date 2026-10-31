<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'rashfa:make-admin {email? : البريد الإلكتروني} {--name= : الاسم الكامل} {--role=super_admin : super_admin أو editor أو viewer}';
    protected $description = 'إنشاء أو تحديث مسؤول رشفة بكلمة مرور مشفّرة تفاعلياً.';

    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('البريد الإلكتروني');
        $name = $this->option('name') ?: $this->ask('الاسم الكامل');
        $roles = ['super_admin', 'editor', 'viewer'];
        $requestedRole = (string) $this->option('role');
        $role = in_array($requestedRole, $roles, true)
            ? $requestedRole
            : $this->choice('الدور', $roles, 0);
        $password = $this->secret('كلمة المرور (8 أحرف على الأقل)');
        $confirmation = $this->secret('أعد كتابة كلمة المرور');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || blank($name) || strlen((string) $password) < 8 || $password !== $confirmation) {
            $this->error('تحقق من البريد والاسم وتطابق كلمة مرور بطول 8 أحرف على الأقل.');
            return self::FAILURE;
        }

        $admin = Admin::firstOrNew(['email' => strtolower($email)]);
        $admin->forceFill([
            'full_name' => $name,
            'password_hash' => Hash::make($password),
            'role' => $role,
            'is_active' => true,
            'failed_attempts' => 0,
            'locked_until' => null,
        ])->save();

        $this->info('تم حفظ المسؤول بكلمة مرور مشفّرة.');
        return self::SUCCESS;
    }
}
