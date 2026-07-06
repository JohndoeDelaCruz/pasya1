<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Verification Code</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#16a34a,#15803d);padding:28px 32px;">
                            <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;">PASYA</p>
                            <p style="margin:4px 0 0;font-size:12px;color:#bbf7d0;">Farmer Login Verification</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 8px;font-size:15px;color:#374151;">Hello, <strong>{{ $farmerName }}</strong></p>
                            <p style="margin:0 0 24px;font-size:14px;color:#6b7280;line-height:1.6;">
                                Use the verification code below to complete your login. This code is valid for <strong>10 minutes</strong>.
                            </p>

                            <!-- OTP Box -->
                            <div style="text-align:center;margin:0 0 24px;">
                                <div style="display:inline-block;background:#f0fdf4;border:2px solid #86efac;border-radius:12px;padding:20px 40px;">
                                    <p style="margin:0;font-size:36px;font-weight:700;letter-spacing:10px;color:#15803d;font-family:monospace;">{{ $otp }}</p>
                                </div>
                            </div>

                            <p style="margin:0 0 8px;font-size:13px;color:#9ca3af;text-align:center;">
                                If you did not attempt to log in, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9fafb;padding:16px 32px;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;">
                                &copy; {{ date('Y') }} PASYA &mdash; Philippine Agricultural System for Yield Analytics
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
