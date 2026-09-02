<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Globetrotters Logistics</title>

    <!-- Google Fonts: Plus Jakarta Sans & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --gt-light-bg: #f0f7ff;
            --gt-white: #ffffff;
            --gt-light-blue: #e0f2fe;
            --gt-blue-border: #bae6fd;
            --gt-primary: #0284c7;
            --gt-primary-hover: #0369a1;
            --gt-text-dark: #0f172a;
        }

        body {
            background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: var(--gt-text-dark);
        }

        .brand-heading {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 700;
        }

        .login-card {
            background: #ffffff;
            border-radius: 1.25rem;
            border: 1px solid var(--gt-blue-border);
            box-shadow: 0 20px 30px -10px rgba(2, 132, 199, 0.15);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }

        .login-header {
            background: #ffffff;
            border-bottom: 1px solid var(--gt-blue-border);
            padding: 2.5rem 1.5rem 1.5rem 1.5rem;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--gt-primary) !important;
            border-color: var(--gt-primary) !important;
            color: #ffffff !important;
            font-weight: 600;
            border-radius: 0.5rem;
            padding: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.3);
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: var(--gt-primary-hover) !important;
            border-color: var(--gt-primary-hover) !important;
            box-shadow: 0 6px 12px -1px rgba(3, 105, 161, 0.4);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('images/logo.png') }}" alt="Globetrotters Logo" class="img-fluid mb-2" style="max-height: 75px; width: auto;">
            <div class="small fw-bold text-uppercase text-secondary" style="letter-spacing: 1px; font-size: 0.75rem;">Shipment Leads CRM</div>
        </div>

        <div class="p-4 p-md-5">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="m-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label font-weight-bold text-dark">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-primary"></i></span>
                        <input type="email" name="email" class="form-control border-start-0" placeholder="admin@company.com" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold text-dark">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-primary"></i></span>
                        <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label text-muted small" for="remember">
                            Remember Me
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 font-weight-bold">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In
                </button>
            </form>

            <div class="mt-4 pt-3 border-top text-center text-muted small">
                Default Credentials: <strong>admin@company.com</strong> / <strong>password</strong>
            </div>
        </div>
    </div>

</body>
</html>
