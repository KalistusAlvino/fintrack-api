<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Gagal</title>
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

        .failed-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            padding: 48px 40px;
            text-align: center;
            max-width: 480px;
            width: 100%;
            position: relative;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .failed-header {
            margin-bottom: 32px;
        }

        .failed-icon {
            width: 100px;
            height: 100px;
            background: #F56565;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            position: relative;
            animation: pulseAnimation 2s infinite;
        }

        .failed-icon::after {
            content: '!';
            color: white;
            font-size: 60px;
            font-weight: bold;
        }

        @keyframes pulseAnimation {
            0% {
                box-shadow: 0 0 0 0 rgba(245, 101, 101, 0.4);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(245, 101, 101, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(245, 101, 101, 0);
            }
        }

        .failed-title {
            color: #F56565;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .failed-subtitle {
            color: #4A5568;
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .failed-message {
            color: #718096;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .error-details {
            background: #FFF5F5;
            border: 1px solid #FED7D7;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 32px;
            border-left: 4px solid #F56565;
            text-align: left;
        }

        .error-code {
            color: #E53E3E;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            font-family: monospace;
        }

        .error-reason {
            color: #2D3748;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .error-explanation {
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
        }

        .reasons-list {
            background: #F7FAFC;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
            text-align: left;
        }

        .reasons-title {
            color: #2D3748;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            text-align: center;
        }

        .reason-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            color: #4A5568;
            font-size: 15px;
        }

        .reason-item:last-child {
            margin-bottom: 0;
        }

        .reason-icon {
            width: 20px;
            height: 20px;
            background: #F56565;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .reason-icon::after {
            content: '!';
            color: white;
            font-size: 12px;
            font-weight: bold;
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
            color: #4A5568;
            border: 2px solid #CBD5E0;
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
            background: #F7FAFC;
            border-color: #A0AEC0;
            transform: translateY(-1px);
        }

        .help-section {
            border-top: 1px solid #E2E8F0;
            padding-top: 24px;
        }

        .help-title {
            color: #2D3748;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .help-options {
            display: flex;
            justify-content: center;
            gap: 16px;
        }

        .help-option {
            display: flex;
            align-items: center;
            color: #1DB584;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .help-option:hover {
            color: #17A2B8;
            text-decoration: underline;
        }

        .help-icon {
            margin-right: 6px;
            font-size: 18px;
        }

        @media (max-width: 480px) {
            .failed-container {
                padding: 32px 24px;
                margin: 16px;
                border-radius: 20px;
            }

            .failed-title {
                font-size: 28px;
            }

            .failed-subtitle {
                font-size: 16px;
            }

            .failed-message {
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

            .reasons-list {
                padding: 20px;
            }

            .help-options {
                flex-direction: column;
                gap: 12px;
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
    <div class="failed-container">
        <div class="failed-header">
            <div class="failed-icon"></div>
            <h1 class="failed-title">Verifikasi Gagal</h1>
            <h2 class="failed-subtitle">Email Tidak Dapat Diverifikasi</h2>
            <p class="failed-message">
                Maaf, ada kesalahan dalam memverifikasi email anda.
                Silahkan Kirim Ulang Email Verifikasi Melalui Aplikasi
            </p>
        </div>
    </div>
</body>
</html>
