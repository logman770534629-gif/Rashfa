<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دخول إدارة رشة</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #07120e 0%, #0a2a1f 100%);
            color: #fff;
            font-family: 'Cairo', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3rem;
            border-radius: 1.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            text-align: right;
        }
        .login-card h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #c6f135;
            font-weight: 800;
        }
        .login-card p {
            font-size: 0.9rem;
            color: #aaa;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: #ddd;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            color: #fff;
            outline: none;
            box-sizing: border-box;
        }
        .form-group input:focus {
            border-color: #c6f135;
        }
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: #c6f135;
            color: #07120e;
            border: none;
            border-radius: 0.5rem;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-login:hover {
            background: #d4ff4d;
        }
        .error-msg {
            color: #ff6b6b;
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <a href="/" style="text-decoration:none; color:#c6f135; font-weight:bold; display:block; margin-bottom:2rem;">← العودة للموقع</a>
        <h1>المنطقة الهادئة</h1>
        <p>هذه البوابة مخصصة لفريق رشة فقط.</p>

        @if(session('status'))
            <div style="background:rgba(198,241,53,0.1); color:#c6f135; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.attempt') }}">
            @csrf
            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')<span class="error-msg">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">دخول آمن</button>
        </form>
    </div>
</body>
</html>