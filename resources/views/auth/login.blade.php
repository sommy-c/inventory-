<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Inventory System</title>
<style>
  * { box-sizing: border-box; margin:0; padding:0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

  body {
      display:flex;
      justify-content:center;
      align-items:center;
      min-height:100vh;
      padding:15px;
      background: url('{{ asset("images/shorpingcart.jpeg") }}') no-repeat center center fixed;
      background-size: cover;
  }

  .login-container {
      background: rgba(255,255,255,0.95); /* slightly transparent for background image to show */
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 400px;
      padding: 40px 30px;
      text-align: center;
      transition: transform 0.3s;
  }

  .login-container:hover { transform: translateY(-3px); }

  /* Logo */
  .logo { max-width: 100px; margin: 0 auto 25px; }

  h1 { font-size: 26px; margin-bottom: 25px; color: #333; }

  .alert-success {
      background:#d1fae5;
      color:#065f46;
      padding:10px;
      border-radius:5px;
      margin-bottom:15px;
      font-size:14px;
  }

  form label {
      display:block;
      margin-bottom:5px;
      font-weight:600;
      color:#555;
      font-size:14px;
      text-align: left;
  }

  form input[type="email"], form input[type="password"] {
      width:100%;
      padding:12px 40px 12px 12px; 
      border:1px solid #ccc;
      border-radius:8px;
      font-size:14px;
      margin-bottom:15px;
      transition: border-color 0.3s;
  }

  form input:focus { outline:none; border-color:#2563eb; }

  .password-wrapper { position: relative; }
  .password-wrapper .eye-icon {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #888;
      font-size: 18px;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: color 0.2s;
  }
  .password-wrapper .eye-icon:hover { color:#2563eb; }
  .password-wrapper .eye-icon svg { width:20px; height:20px; }

  .form-footer { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; font-size:13px; }
  .form-footer a { color:#2563eb; text-decoration:none; }
  .form-footer a:hover { text-decoration:underline; }

  button[type="submit"] {
      width: 100%;
      padding:12px;
      background:#2563eb;
      color:#fff;
      border:none;
      border-radius:8px;
      font-size:16px;
      cursor:pointer;
      transition: background-color 0.3s, transform 0.2s;
  }
  button[type="submit"]:hover { background:#1d4ed8; transform: translateY(-1px); }

  .error { color:#dc2626; font-size:13px; margin-top:-10px; margin-bottom:10px; }

  @media(max-width:420px){
      .login-container { padding:35px 25px; }
      form input[type="email"], form input[type="password"] { padding:10px 36px 10px 10px; }
  }
</style>
</head>
<body>

<div class="login-container">
    <!-- Company Logo -->
    <img src="{{ asset('images/logo.png') }}" alt="Company Logo" class="logo">

    <h1>Login</h1>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('login.submit') }}" method="POST">
        @csrf
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required>
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <label for="password">Password</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" required>
            <span class="eye-icon" onclick="togglePassword()">
                <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:none">
                    <path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a18.32 18.32 0 015.11-5.11m3.93-1.93A10.94 10.94 0 0112 4c7 0 11 8 11 8a18.32 18.32 0 01-1.93 3.93M1 1l22 22"/>
                </svg>
            </span>
        </div>
        @error('password') <p class="error">{{ $message }}</p> @enderror

        <div class="form-footer">
            <label><input type="checkbox" name="remember"> Remember me</label>
            <a href="#">Forgot Password?</a>
        </div>

        <button type="submit">Login</button>
    </form>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeOpen = document.getElementById('eye-open');
    const eyeClosed = document.getElementById('eye-closed');

    if(passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'inline';
    } else {
        passwordInput.type = 'password';
        eyeOpen.style.display = 'inline';
        eyeClosed.style.display = 'none';
    }
}
</script>

</body>
</html>
