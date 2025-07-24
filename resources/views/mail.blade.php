<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Verifikasi Email</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;">

    <!-- Email Container -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f5f5f5;">
        <tr>
            <td style="padding: 40px 20px;">

                <!-- Main Content Table -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">

                    <!-- Header Section -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1DB584 0%, #17A2B8 100%); border-radius: 16px 16px 0 0; padding: 40px 30px; text-align: center;">

                            <!-- Header Text -->
                            <h1 style="margin: 0 0 8px 0; color: #ffffff; font-size: 28px; font-weight: 600; line-height: 1.2;">
                                Verifikasi Email Anda
                            </h1>
                            <p style="margin: 0; color: rgba(255,255,255,0.9); font-size: 16px; line-height: 1.4;">
                                Aktivasi akun diperlukan untuk melanjutkan
                            </p>
                        </td>
                    </tr>

                    <!-- Content Section -->
                    <tr>
                        <td style="padding: 40px 30px;">

                            <!-- Welcome Message -->
                            <p style="margin: 0 0 30px 0; color: #2d3748; font-size: 16px; line-height: 1.6; text-align: center;">
                                Halo! Terima kasih telah mendaftar. Untuk mengaktifkan akun Anda dan mengakses semua fitur aplikasi, silakan klik tombol verifikasi di bawah ini.
                            </p>

                            <!-- Verification Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto 30px;">
                                <tr>
                                    <td style="border-radius: 50px; background: linear-gradient(135deg, #1DB584 0%, #17A2B8 100%); text-align: center;">
                                        <a href="{{ url('/api/verify/' . $verification_token) }}" style="display: inline-block; padding: 16px 40px; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 50px;">
                                            Verifikasi Email Sekarang
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Alternative Link -->
                            <p style="margin: 0 0 30px 0; color: #718096; font-size: 14px; line-height: 1.5; text-align: center;">
                                Jika tombol di atas tidak berfungsi, salin dan tempel link berikut ke browser Anda:<br>
                                <a href="{{ url('/api/verify/' . $verification_token) }}" style="color: #1DB584; text-decoration: none; word-break: break-all;">
                                    {{ url('/api/verify/' . $verification_token) }}
                                </a>
                            </p>

                        </td>
                    </tr>

                    <!-- Security Info Section -->
                    <tr>
                        <td style="padding: 0 30px 40px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f7fafc; border-radius: 12px; border-left: 4px solid #1DB584;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td style="width: 30px; vertical-align: top; padding-right: 12px;">
                                                    <span style="display: inline-block; width: 24px; height: 24px; background-color: #1DB584; border-radius: 50%; text-align: center; line-height: 24px; font-size: 12px;">🔒</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <p style="margin: 0; color: #4a5568; font-size: 14px; line-height: 1.5;">
                                                        <strong>Keamanan Terjamin:</strong> Link verifikasi ini hanya berlaku selama 24 jam dan hanya dapat digunakan sekali untuk menjaga keamanan akun Anda.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer Section -->
                    <tr>
                        <td style="padding: 30px; background-color: #f8f9fa; border-radius: 0 0 16px 16px; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 15px 0; color: #718096; font-size: 13px; line-height: 1.4; text-align: center;">
                                Email ini dikirim karena Anda mendaftar akun baru. Jika Anda tidak melakukan pendaftaran, abaikan email ini.
                            </p>
                            <p style="margin: 0; color: #a0aec0; font-size: 12px; text-align: center;">
                                © 2024 YourApp. Semua hak dilindungi undang-undang.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

    <!-- Mobile Responsive Styles -->
    <style>
        @media only screen and (max-width: 600px) {
            .mobile-padding {
                padding: 20px !important;
            }
            .mobile-text {
                font-size: 14px !important;
            }
            .mobile-button {
                padding: 14px 30px !important;
                font-size: 15px !important;
            }
        }
    </style>

</body>
</html>
