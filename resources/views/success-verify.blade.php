<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Berhasil Diverifikasi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #F5F5F5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            padding: 48px 40px;
            text-align: center;
            max-width: 480px;
            width: 100%;
            position: relative;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-header {
            margin-bottom: 32px;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #1DB584 0%, #17A2B8 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            position: relative;
            animation: checkmarkAnimation 0.8s ease-out 0.3s both;
        }

        .success-icon::after {
            content: '✓';
            color: white;
            font-size: 48px;
            font-weight: bold;
        }

        @keyframes checkmarkAnimation {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-title {
            color: #1DB584;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .success-subtitle {
            color: #4A5568;
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .success-message {
            color: #718096;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .user-info {
            background: #F7FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 32px;
            border-left: 4px solid #1DB584;
        }

        .user-email {
            color: #2D3748;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .verification-time {
            color: #718096;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 32px;
        }

        .primary-button {
            background: linear-gradient(135deg, #1DB584 0%, #17A2B8 100%);
            color: white;
            border: none;
            padding: 16px 32px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }

        .primary-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s;
        }

        .primary-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(29, 181, 132, 0.3);
        }

        .primary-button:hover::before {
            left: 100%;
        }

        .secondary-button {
            background: transparent;
            color: #1DB584;
            border: 2px solid #1DB584;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .secondary-button:hover {
            background: #1DB584;
            color: white;
            transform: translateY(-1px);
        }

        .features-list {
            background: #F7FAFC;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
            text-align: left;
        }

        .features-title {
            color: #2D3748;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            text-align: center;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            color: #4A5568;
            font-size: 15px;
        }

        .feature-item:last-child {
            margin-bottom: 0;
        }

        .feature-icon {
            width: 20px;
            height: 20px;
            background: #1DB584;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .feature-icon::after {
            content: '✓';
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .footer-note {
            color: #A0AEC0;
            font-size: 14px;
            line-height: 1.5;
            border-top: 1px solid #E2E8F0;
            padding-top: 24px;
        }

        @media (max-width: 480px) {
            .success-container {
                padding: 32px 24px;
                margin: 16px;
                border-radius: 20px;
            }

            .success-title {
                font-size: 28px;
            }

            .success-subtitle {
                font-size: 16px;
            }

            .success-message {
                font-size: 15px;
            }

            .action-buttons {
                gap: 12px;
            }

            .primary-button,
            .secondary-button {
                padding: 14px 28px;
                font-size: 15px;
            }

            .features-list {
                padding: 20px;
            }
        }

        /* Loading state for buttons */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-header">
            <div class="success-icon"></div>
            <h1 class="success-title">Berhasil!</h1>
            <h2 class="success-subtitle">Email Telah Diverifikasi</h2>
            <p class="success-message">
                Selamat! Akun Anda telah berhasil diaktifkan dan siap digunakan.
                Anda sekarang dapat mengakses semua fitur aplikasi.
            </p>
        </div>

        <div class="user-info">
            <div class="user-email" id="userEmail">{{$email}}</div>
            <div class="verification-time" id="verificationTime">Diverifikasi pada: {{$verification_time}}</div>
        </div>

        <div class="footer-note">
            <p>
                Jika Anda mengalami masalah atau memiliki pertanyaan,
                jangan ragu untuk menghubungi tim support kami.
            </p>
        </div>
    </div>
    </script>
</body>
</html>
